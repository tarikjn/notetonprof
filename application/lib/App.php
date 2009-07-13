<?php

// used statically
class App
{
	/* save_log
	 *
	 * $user: 0 (system/cron action), > 0 (logged) or null (guest)
	 * $did: > 0 or null
	 */
	static function log($mmsg, $otype, $oid, $user = null, $related_data = null, $note = null, $not_an_update = false)
	{
		// $otype and $oid are trusted
		
		$ip = DBPal::str2null(($user !== 0)? $_SERVER["REMOTE_ADDR"] : null);
		$host = DBPal::str2null(
		    ($user !== 0)?
		      (empty($_SERVER["REMOTE_HOST"])) ? gethostbyaddr($_SERVER["REMOTE_ADDR"]) : $_SERVER["REMOTE_HOST"]
		      : null
		  );
		$related_data = DBPal::str2null(($related_data) ? json_encode($related_data) : null);
		$user = DBPal::int2null($user);
		
		if (is_array($mmsg))
		{
			$i = 0; $msg = "";
			foreach($mmsg as $s)
			{
				if ($i > 0)
					$msg .= ", ";
				$msg .= $s;
				$i++;
			}
		}
		else
			$msg = $mmsg;
		
		$msg = DBPal::quote($msg);
		
		// newly added
		$note = DBPal::str2null($note); // this is the comment manually entered on an update
		$is_update = ($not_an_update)? 'no' : 'yes';
			
		$query = "INSERT INTO logs (log_msg, object_type, object_id, client_ip, client_host, related_data, actor_id, time, note, is_update) ".
		         "VALUES ($msg, '$otype', $oid, $ip, $host, $related_data, $user, NOW(), $note, '$is_update')";
		$iid = DBPal::insert($query);
		
		return $iid;
	}
	
	/* queue
	 * send action to message queue
	 *
	 * $msg: action identifier
	 * $params: array of parameters
	 * $p: priority
	 */
	static function queue($msg, $params = null, $p = 0)
	{
		// send msg to beanstalkd
	}
	
	// TODO set $user as param for all methods
	static function getReports($object_type, $object_id)
	{
		global $user;
	
		$result = DBPal::query(
		      "SELECT reports.id AS id, description, actor_id, UNIX_TIMESTAMP(time) AS time,"
		    . "  (SELECT COUNT(*) FROM defered_reports WHERE admin_id = {$user->uid} AND report_id = reports.id) AS defered"
		    . " FROM reports, logs"
		    . " WHERE reports.object_type = '$object_type' AND reports.object_id = $object_id AND status = 'open'"
		    . "  AND logs.id = reports.create_record"
		    . " ORDER BY reports.id DESC"
		  );
		
		$reports = array();
		while ($row = $result->fetch_object())
		{
			$reports[] = $row;
		}
		
		return $reports;
	}
	
	static function deleteSchool($id, $notes = null)
	{
		global $user;
		
		// set profs and comments orphans + clear assignments
		// TODO: should that be done by a separate job?
		$profs = DBPal::getList("SELECT id FROM professeurs WHERE etblt_id = $id");
		while ($prof_id = array_shift($profs))
		{
			self::deleteProf($prof_id, null, true);
		}
		
		// delete school
		DBPal::query("UPDATE etablissements SET status = 'deleted' WHERE id = $id");		    
		
		// log
		self::log("Deleted", "school", $id, $user->uid, null, $notes);
		
		// clear school's assignments
		DBPal::query("DELETE FROM assignments WHERE object_type = 'school' AND object_id = $id");
	}
	
	static function deleteProf($prof_id, $notes = null, $orphan = false)
	{
		global $user;
		
		$comments = DBPal::getList("SELECT id FROM notes WHERE prof_id = $prof_id");
		while ($comment_id = array_shift($comments))
		{
		    self::deleteComment($comment_id);
		}
		
		DBPal::query("UPDATE professeurs SET status = '".(($orphan)?'orphan':'deleted')."' WHERE id = $prof_id");
		self::log(($orphan)?'Orphaned':'Deleted', "prof", $prof_id, $user->uid, null, $notes);
		
		DBPal::query("DELETE FROM assignments WHERE object_type = 'prof' AND object_id = $prof_id");
	}
	
	static function deleteComment($comment_id, $orphan = false)
	{
		global $user;
		
		DBPal::query("UPDATE notes SET status = '".(($orphan)?'orphan':'deleted')."' WHERE id = $comment_id");
		self::log(($orphan)?'Orphaned':'Deleted', "comment", $comment_id, $user->uid);
		    
		DBPal::query("DELETE FROM assignments WHERE object_type = 'comment' AND object_id = $comment_id");
	}
	
	static function updateFirstReport($object_type, $object_id, $current_report = false)
	{
		$object_where = " WHERE object_type = '$object_type' AND object_id = $object_id";
		
		$new_report = DBPal::getOne(
		      "SELECT id FROM reports" . $object_where
		    . "  AND status = 'open'"
		    . " ORDER BY id LIMIT 1"
		  );
		
		if ($current_report === false or
		  ($current_report !== false and $current_report != $new_report))
		{
			DBPal::query(
			      "UPDATE " . Settings::$objType2tabName[$object_type]
			    . " SET open_ticket = " . DBPal::int2null($new_report)
			    . " WHERE id = $object_id"
			  );
		}
	}
	
	static function appendSecondary(&$arr, $cursus, $secondary)
	{
		if ($cursus == E_2ND)
		{
		    $arr['secondaire'] = array();
		    
		    if (@$secondary["college"])
		    	$arr['secondaire'][] = 'college';
		    if (@$secondary["lycee"])
		    	$arr['secondaire'][] = 'lycee';
		    
		    $arr['secondaire'] = implode(",", $arr['secondaire']);
		}
	}
	
	static function createObjectAndLog($object_type, $object_data)
	{
		global $user;
		
		$object_id = DBPal::insert(
		      "INSERT INTO " . Settings::$objType2tabName[$object_type]
		    . " " . DBPal::arr2values($object_data)
		  );
		
		// log
		App::log("Created", $object_type, $object_id, $user->uid, $object_data);
	}
	
	static function processReports($reports_post, $obj_arr, $user, $current_report = false)
	{
		$object_where = " WHERE object_type = '" . $obj_arr[0] . "' AND object_id = " . $obj_arr[1];
		
		foreach ($reports_post AS $rid => $action)
		{
		    $rid = (int) $rid;
		    
		    if (!$rid)
		    	continue;
		    
		    switch($action)
		    {
		    	// TODO: would be nice if report fixes get individual comments ($notes)
		    	
		    	case 'close':
		    		
		    		DBPal::query("UPDATE reports SET status = 'closed'" . $object_where . " AND id = $rid");
		    		
		    		self::log("Closed", "report", $rid, $user->uid);
		    		
		    		break;
		    		
		    	case 'defer':
		    		
		    		// no need to check that report is associated with object, affect only user
		    		DBPal::query("INSERT INTO defered_reports (admin_id, report_id) VALUES ({$user->uid}, $rid)");
		    		
		    		self::log("Defered", "report", $rid, $user->uid);
		    		
		    		break;
		    }
		}
		
		// update first report if changed
		self::updateFirstReport($obj_arr[0], $obj_arr[1], $current_report);
	}
}
