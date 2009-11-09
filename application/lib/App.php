<?php

// beanstalkd PHP client, required for the job queue
require_once("third-party/pheanstalk/pheanstalk_init.php");

// used statically
class App
{
	/* save_log
	 *
	 * $uid: 0 (system/cron action), > 0 (logged) or null (guest)
	 * $did: > 0 or null
	 */
	static function log($mmsg, $otype, $oid, $uid = null, $related_data = null, $note = null, $not_an_update = false)
	{
		// $otype and $oid are trusted
		
		$ip = DBPal::str2null(($uid !== 0)? $_SERVER["REMOTE_ADDR"] : null);
		$host = DBPal::str2null(
		    ($uid !== 0)?
		      (empty($_SERVER["REMOTE_HOST"])) ? gethostbyaddr($_SERVER["REMOTE_ADDR"]) : $_SERVER["REMOTE_HOST"]
		      : null
		  );
		$related_data = DBPal::str2null(($related_data) ? json_encode($related_data) : null);
		$uid = DBPal::int2null($uid);
		
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
		         "VALUES ($msg, '$otype', $oid, $ip, $host, $related_data, $uid, NOW(), $note, '$is_update')";
		$iid = DBPal::insert($query);
		
		return $iid;
	}
	
	/* queue
	 * send job to message queue (beanstalkd)
	 *
	 * $action: action identifier
	 * $params: array of parameters
	 * $p: priority
	 */
	static function queue($action, $params = null, $p = 0)
	{
		// format message
		$message = array(
			'job'  => $action,
			'args' => Helper::flatten($params)
		);
		$message = json_encode($message);
		
		// connect to beanstalkd
		$pheanstalk = new Pheanstalk(Settings::BEANSTALKQ);
		
		// send message
		$pheanstalk->put($message);
		
		// TODO: does Pheanstalk automatically disconnect after garbage collection?
	}
	
	static function getReports($object_type, $object_id, $uid)
	{
		$result = DBPal::query(
		      "SELECT reports.id AS id, description, actor_id, UNIX_TIMESTAMP(time) AS time,"
		    . "  (SELECT COUNT(*) FROM defered_reports WHERE admin_id = $uid AND report_id = reports.id) AS defered"
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
	
	static function deleteSchool($id, $uid, $notes = null)
	{
		// set profs and comments orphans + clear assignments
		// TODO: should that be done by a separate job?
		$profs = DBPal::getList("SELECT id FROM professeurs WHERE etblt_id = $id AND status = 'ok'");
		while ($prof_id = array_shift($profs))
		{
			self::deleteProf($prof_id, $uid, null, true);
		}
		
		// delete school
		DBPal::query("UPDATE etablissements SET status = 'deleted' WHERE id = $id");		    
		
		// log
		self::log("Deleted", "school", $id, $uid, null, $notes);
		
		// clear school's assignments
		DBPal::query("DELETE FROM assignments WHERE object_type = 'school' AND object_id = $id");
	}
	
	static function deleteProf($prof_id, $uid, $notes = null, $orphan = false)
	{
		$comments = DBPal::getList("SELECT id FROM notes WHERE prof_id = $prof_id AND status = 'ok'");
		while ($comment_id = array_shift($comments))
		{
		    self::deleteComment($comment_id, $uid, true);
		}
		
		DBPal::query("UPDATE professeurs SET status = '".(($orphan)?'orphaned':'deleted')."' WHERE id = $prof_id");
		self::log(($orphan)?'Orphaned':'Deleted', "prof", $prof_id, $uid, null, $notes);
		
		DBPal::query("DELETE FROM assignments WHERE object_type = 'prof' AND object_id = $prof_id");
	}
	
	static function deleteComment($comment_id, $uid, $orphan = false)
	{
		DBPal::query("UPDATE notes SET status = '".(($orphan)?'orphaned':'deleted')."' WHERE id = $comment_id");
		self::log(($orphan)?'Orphaned':'Deleted', "comment", $comment_id, $uid);
		    
		DBPal::query("DELETE FROM assignments WHERE object_type = 'comment' AND object_id = $comment_id");
	}
	
	static function updateFirstReport($object_type, $object_id, $current_report = false)
	{
		// NOTE: should this method call refresh-assignments?
		
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
		
		return $new_report;
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
	
	static function addReport($report_data, $uid, $current_report = null)
	{
		// create and log report
		self::createObjectAndLog('report', (object) $report_data, $uid, true);
		
		// reset open_ticket on object
		self::updateFirstReport($report_data->object_type, $report_data->object_id, $current_report);
	}
	
	static function createObjectAndLog($object_type, $object_data, $uid, $create_record = false)
	{
		$object_id = DBPal::insert(
		      "INSERT INTO " . Settings::$objType2tabName[$object_type]
		    . " " . DBPal::arr2values($object_data)
		  );
		
		// log
		$create_record_id = App::log("Created", $object_type, $object_id, $uid, $object_data);
		
		if ($create_record)
		{
			DBPal::query(
			      "UPDATE " . Settings::$objType2tabName[$object_type]
			    . " SET create_record = $create_record_id"
			    . " WHERE id = $object_id"
			  );
		}
		
		return $object_id;
	}
	
	static function processReports($reports_post, $obj_arr, $uid, $update_id, $current_report = false)
	{
		$object_where = " WHERE object_type = '" . $obj_arr[0] . "' AND object_id = " . $obj_arr[1];
		
		foreach ($reports_post AS $rid => $action)
		{
		    $rid = (int) $rid;
		    
		    if (!$rid)
		    	continue;
		    
		    $related_data = array('log_update_id' => $update_id);
		    
		    switch($action)
		    {
		    	// TODO: would be nice if report fixes get individual comments ($notes)
		    	
		    	case 'close':
		    		
		    		DBPal::query("UPDATE reports SET status = 'closed'" . $object_where . " AND id = $rid");
		    		
		    		self::log("Closed", "report", $rid, $uid, $related_data);
		    		
		    		break;
		    		
		    	case 'defer':
		    		
		    		// no need to check that report is associated with object, affect only user
		    		DBPal::query("INSERT INTO defered_reports (admin_id, report_id) VALUES ($uid, $rid)");
		    		
		    		self::log("Defered", "report", $rid, $uid, $related_data);
		    		
		    		break;
		    }
		}
		
		// update first report if changed
		return self::updateFirstReport($obj_arr[0], $obj_arr[1], $current_report);
	}
}
