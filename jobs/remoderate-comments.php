#!/usr/local/php5/bin/php -q
<?php
require("_ini.php");

$time = time();


fprintf(STDOUT, "Marking comments for remoderation...\n", $argv[1]);

$comments = DBPal::getList(
      "SELECT DISTINCT object_id FROM `logs` WHERE `object_type` = 'comment'"
    . "  AND `related_data` LIKE '%moderated_by%'"
    . "  AND `related_data` NOT LIKE '%deleted_by%'"
    . "  AND `related_data` NOT LIKE '%moderated_by\":\"1%'"
  );
foreach ($comments as $comment)
{
    // report data
	$report = (object) array(
	    'object_type' => 'comment',
	    'object_id' => $comment,
	    'description' => 'Commentaire doit être re-modéré'
	);
	
	// add report (will also log and ticket object)
	App::addReport($report);
}
// ends here


// close db connection
DBPal::finish();

// TODO: move to ruby daemon
fwrite(STDOUT, "took " . (time() - $time) . " seconds\n");
