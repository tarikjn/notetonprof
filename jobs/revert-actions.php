#!/usr/local/php5/bin/php -q
<?php
require("../jobs/_ini.php");

$time = time();

if ($argc < 2)
{
	fwrite(STDERR, "IP address argument missing\n");
	exit();
}
else
{
	if (!Web::valid_ip($argv[1]))
	{
		fwrite(STDERR, "incorrect IP address\n");
		exit();
	}
	else
	{
		fprintf(STDOUT, "Reverting actions done by user...\n");

		$uid = 0;
		$notes = "Script reverted action done by user";

		$actions = DBPal::query(
		      "SELECT object_type, object_id FROM logs"
		    . "  WHERE client_ip = '" . $argv[1] . "' AND (log_msg = 'Created' OR log_msg = 'Submitted Review')"
		  );

		while ($action = $actions->fetch_object())
		{
			switch ($action->object_type)
			{
				case 'prof':

					// delete prof
					App::deleteProf($action->object_id, $uid, $notes);

					break;

				case 'school':

					// delete school
					App::deleteSchool($action->object_id, $uid, $notes);

					break;

				case 'comment':

					// delete school
					App::deleteComment($action->object_id, $uid);

					break;
			}
		}
		// ends here


		// close db connection
		DBPal::finish();	
	}
}

// TODO: move to ruby daemon
fwrite(STDOUT, "took " . (time() - $time) . " seconds\n");
