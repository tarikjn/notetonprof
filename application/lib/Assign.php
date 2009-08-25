<?php

/*
 * This class allows to do assign open tickets to admins
 * its main role is to compute priority scores
 *
 * MUST NOT be called on concurrency, may corrupt table
 *
 * Assignements are deleted LIVE by the website when no more
 * current after an action taken on a ticketed object
 * -> action stats refresh?
 *
 * requires DBPal, Admin
 */
class Assign
{
	/*
	 * assignment settings:
	 */
	const REPORT_LEVEL_CHANGE = 'Level change';
	
	/*
	 * score settings:
	 */
	const SCORE_BASE = 900;
	const SCORE_SCHOOL_MEMBER = 90;
	const SCORE_FULLY_ACTIVE_LEVEL = 400;	// max activity score for 1 admin is 100, so this is like 4 fully active admins
	const SCORE_OPEN_EXP_BETA = .5;
	const SCORE_UPDATE_EXP_BETA = 0;
	
	static $SCORE_LEVEL_DIFF = array(
		0 => 250, // same level
		1 => 100,
		2 => 125,
		3 => 175,
		4 => 250
	);
	
	const DEFAULT_ACTIVITY_SCORE = 100; // TOOD: move to ActivityScore
	
	const EXPIRATION_DELTA = 604800; // 1 week in seconds
	
	/*
	 * called when:
	 * - a new admin is created
	 * - an admin is promoted
	 * - an admin is unlocked
	 * - table needs to be regenerated (manual)
	 */
	static function refreshAll()
	{
		$queries = array(
			'comment' => "SELECT * FROM notes WHERE ((moderated != 'yes' AND LENGTH(comment) > 0) OR open_ticket IS NOT NULL) AND status = 'ok'",
			'prof' => "SELECT * FROM professeurs WHERE (moderated != 'yes' OR open_ticket IS NOT NULL) AND status = 'ok'",
			'school' => "SELECT * FROM etablissements WHERE (moderated != 'yes' OR open_ticket IS NOT NULL) AND status = 'ok'",
			'user' => "SELECT * FROM delegues WHERE open_ticket IS NOT NULL AND status = 'ok'",
		);
		
		foreach ($queries AS $type => $query)
		{
			$res = DBPal::query($query);
			while ($object = $res->fetch_object())
			{
				self::refreshForObject($type, $object->id, $object);
			}
		}
	}
	
	/*
	 * called when an admin is:
	 * - demoted
	 * - locked
	 * - deleted (unsubscribe)
	 *
	 * will also update assignements for which priority score was too low
	 */
	static function refreshAssignedToAdmin($user_id)
	{
		$res = DBPal::query("SELECT * FROM assignments WHERE assignee_id = $user_id");
		while ($assignment = $res->fetch_object())
		{
			self::refreshForObject($assignment->object_type, $assignment->object_id);
		}
	}
	
	/*
	 * called when:
	 * - a report is done
	 * - any ticketed object gets a ticket update (report, delete, raise, dismiss, accept)
	 *
	 * $object_data is feed by refreshAll for speed optimization
	 * parameters are trusted to be valid and secure
	 */
	static function refreshForObject($object_type, $object_id, $object_data = null)
	{
		//TODO: action stats refresh message?
		
		if (!$object_data)
			$object_data = DBPal::getRow(
			  "SELECT * FROM ".Settings::$objType2tabName[$object_type]
			 ."  WHERE status = 'ok' AND id = $object_id"
			 ."    AND (open_ticket IS NOT NULL" . (($object_type != 'user')? " OR moderated != 'yes'":"") . ")"
			);
		
		// nothing to do on that object
		if (!$object_data)
			return;
		
		// delete current assignements
		DBPal::query("DELETE FROM assignments WHERE object_type = '$object_type' AND object_id = $object_id");
		
		// redo assignements
		$task = self::findAdminsForObject($object_type, $object_id, $object_data);
		$levels = array();
		$indexes = array();
		
		// pre-compute levels activity scores
		foreach ($task->admins as $admin)
		{
			if ($admin->level != @$last_level)
			{
				$last_level = $admin->level;
				$last_index = sizeof($levels);
				$indexes[$admin->level] = sizeof($levels);
				
				$levels[] = (object) array(
					'level' => $admin->level,
					'activity_score' => ($admin->activity_score)? $admin->activity_score : self::DEFAULT_ACTIVITY_SCORE
				);
			}
			else
			{
				$levels[$last_index]->activity_score += ($admin->activity_score)? $admin->activity_score : self::DEFAULT_ACTIVITY_SCORE;
			}
		}
		
		// compute score for each admin
		foreach ($task->admins as $admin)
		{
			$li = $indexes[$admin->level]; // level index
			
			// base score
			$score = (int) self::SCORE_BASE;
			
			// is the admin specifically moderating the school
			$score += (in_array($admin->id, $task->members))? self::SCORE_SCHOOL_MEMBER : 0;
			
			// * at this point score is SCORE_BASE + SCORE_SCHOOL_MEMBER
			
			// activity score of the level minus current admin
			$level_activity = $levels[$li]->activity_score - $admin->activity_score;
			
			// substract
			// number of admins at same level poundered by their activity score -> max 4 fully active admins
			$score -= round(self::getLevelActivityBeta($level_activity) * self::$SCORE_LEVEL_DIFF[0]);
			
			// * at this point score needs to be higher than MIN_SCORE no matter what
			
			// for each level under you (d = number of level under you: eg. 1 -> 3 if levels: 1, 2, 4, [5]
			for ($i = 0; $i < $li; $i++)
			{
				// TODO: can be optimized and done only once for each level
				
				$d = $i + 1;
				
				// number of admins under you poundered by their activity score
				$delta = (float) self::getLevelActivityBeta($levels[$i]->activity_score) * (float) self::$SCORE_LEVEL_DIFF[$d];
				
				// if ticket expired d times on this level (d = level number on available levels) : 50%
				$delta *= ($task->open_exp >= $d)? self::SCORE_OPEN_EXP_BETA : 1;
				
				// if last ticket update expired d times : 0%
				$delta *= ($task->update_exp >= $d)? self::SCORE_UPDATE_EXP_BETA : 1;
				
				$score -= round($delta);
			}
			
			// * at this point score is final
			
			// -> assign action (won't be actually active unless score is high enough)
			DBPal::query(
			    "INSERT INTO assignments (assignee_id, object_id, object_type, score)"
			  . "  VALUES ({$admin->id}, $object_id, '$object_type', $score)"
			);
		}
	}
	
	/* 
	 * private methods
	 */
	private static function findAdminsForObject($object_type, $object_id, $object_data)
	{
		$task_type = ($object_type != 'user')?
		                 (($object_data->moderated != 'yes')? 'moderation' : 'ticket')
		               : 'ticket';
		
		$object_where = "  WHERE (object_type = '$object_type' AND object_id = $object_id)";
		
		
		// required power
		if ($object_type == 'user')
		{
			$mention_power = DBPal::query(
			     "SELECT id FROM reports " .$object_where
			   . " AND status = 'open' AND description = '" .self::REPORT_LEVEL_CHANGE. "'"
			 );
			
			$req_power = ($mention_power->num_rows)? Admin::ACC_CHANGE_POWERS : Admin::ACC_ADMINS;
		}
		else
		{
			// if there is a ticket, req power might be higher
			if ($object_data->open_ticket)
			{
				$req_power = ($object_type == 'comment')? Admin::ACC_COMMENT_TICKET : Admin::ACC_DATA_TICKET;
			}
			else
			{
				$req_power = ($object_data->moderated == 'no')? Admin::ACC_PRE_MODERATE : Admin::ACC_REAL_MODERATE;
			}
		}
		
		
		// check school membership 
		// if $req_power >= Admin::ACC_ALL_DATA, it is only used for score affinity
		switch ($object_type)
		{
		    case 'comment':
		    	$school_id = DBPal::getOne(
		    	    "SELECT etblt_id FROM professeurs, notes"
		    	  . "  WHERE professeurs.id = notes.prof_id"
		    	  . "    AND notes.id = $object_id"
		    	  );
		    	break;
		    case 'prof':
		    	$school_id = $object_data->etblt_id;
		    	break;
		    case 'school':
		    	$school_id = $object_data->id;
		    	break;
		    case 'user':
		    	$school_w = "etblt_id IN (SELECT etblt_id FROM delegues_etblts WHERE delegue_id = $object_id)";
		    	break;
		}
		
		if (!@$school_w)
		    $school_w = "etblt_id = $school_id";
		
		$members = DBPal::getList(
		    "SELECT delegue_id FROM delegues_etblts"
		  . "  WHERE " . $school_w
		);
			
		// check if raised: might require higher access level
		if ($object_data->moderated == 'raised')
		{
		    $req_power = max($req_power, Admin::ACC_RAISED_OBJECT);
		}
			
		// dismiss admins who defered all opened tickets
		if ($task_type == 'ticket')
		{
			// required for timings
			$last_update = DBPal::getRow(
			    "SELECT id, UNIX_TIMESTAMP(time) AS time FROM logs " . $object_where
			   ."    AND is_update = 'yes'"
			   ."  ORDER BY id DESC"
			   ."  LIMIT 1"
			  );
			
			// list all open tickets
			$open_reports = DBPal::getList("SELECT id FROM reports " . $object_where . " AND status = 'open'");
			
			// admins to dismiss
			$dismiss_query = "SELECT admin_id FROM (SELECT admin_id, COUNT(report_id) AS count"
			               . " FROM defered_reports WHERE report_id IN (" . implode(',', $open_reports) . ")"
			               . " GROUP BY admin_id) AS s WHERE count = " . sizeof($open_reports);
		}
		
		
		// final query
		$admins_q = "SELECT id, activity_score, level FROM delegues "
		          . "  WHERE status = 'ok' AND locked = 'no' "
		            
		          . (($task_type == 'ticket')?
		            "    AND id NOT IN ($dismiss_query)" : "")
		            
		          . (($req_power < Admin::ACC_ALL_DATA)?
		            "    AND (" . ((sizeof($members))? "id IN (" . implode(',', $members) . ") OR" : "")
		          . "      level >= ". Admin::ACC_ALL_DATA .")" : "")
		            
		          // make sure the admin has a higher power than the user to change
		          . (($object_type == 'user')?
		            "    AND (level > {$object_data->level} OR level >= ". Admin::ACC_CHANGE_POWERS .")" : "")
		            
		          . "    AND level >= $req_power"
		          . "  ORDER BY level";
		// execute
		$admins = DBPal::getAll($admins_q);
		
		
		// determine update times
		if ($task_type == 'moderation')
		{
			// select creation date in log, which is the first created log about the object
			$open_time = DBPal::getOne("SELECT UNIX_TIMESTAMP(time) FROM logs WHERE object_type = '$object_type' AND object_id = $object_id ORDER BY id LIMIT 1");
			$update_time = $open_time;
		}
		else
		{
			$open_time = DBPal::getOne("SELECT UNIX_TIMESTAMP(time) FROM logs WHERE id = {$object_data->open_ticket}");
			$update_time = $last_update->time;
		}
		
		$task = (object) array(
			'type' => $task_type,
			'open_exp' => (int) ((time() - $open_time) / self::EXPIRATION_DELTA),
			'update_exp' => (int) ((time() - $open_time) / self::EXPIRATION_DELTA),
			'members' => (array) $members,
			'admins' => (array) $admins
		);
		
		return $task;
	}
	
	/* 
	 * private methods
	 */
	private static function computeScore(&$task)
	{
		// TODO: move code here after code optimizations
	}
	
	private static function getLevelActivityBeta($score)
	{
		// can't be over 1
		return min(1, (float) $score / self::SCORE_FULLY_ACTIVE_LEVEL);
	}
}
