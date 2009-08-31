#!/usr/local/php5/bin/php -q
<?php
require("_ini.php");

$time = time();

if ($argc < 1)
{
	fwrite(STDERR, "frequency argument missing\n");
	exit();
}
else
{
	if (in_array(@$argv[1], array('daily', 'weekly')))
	{
		fprintf(STDOUT, "Mailing %s assignments...\n", $argv[1]);
		
		// TODO: move code to a class method
		// starts here
		$f = $argv[1];
		
		$admins = DBPal::query(
		      "SELECT * FROM delegues"
		    . " WHERE status = 'ok' AND locked = 'no' AND mail_assignments = '$f'"
		    . " ORDER by level"
		  );
		while ($admin = $admins->fetch_object())
		{
			// count assignments
			$count = array(
				'admin' => DBPal::getOne(
				    "SELECT COUNT(*) FROM assignments"
				  . " WHERE assignee_id = {$admin->id} AND object_type = 'user'"),
				'data' => DBPal::getOne(
				    "SELECT COUNT(*) FROM assignments"
				  . " WHERE assignee_id = {$admin->id} AND object_type IN ('school', 'prof')"),
				'comment' => DBPal::getOne(
				    "SELECT COUNT(*) FROM assignments"
				  . " WHERE assignee_id = {$admin->id} AND object_type = 'comment'")
			);
			
			if (array_sum($count))
			{
				// setup email template vars
				$email_vars = array(
					'f' => $f,
				    'count' => $count,
				    'prenom' => $admin->prenom
				  );
				
				// send activation email
				Mail::sendMail($admin->email,
				               "Données à modérer",
				               'assignments',
				               $email_vars);
			}
		}
		// ends here
	}
	else
	{
		fwrite(STDERR, "unknown frequency\n");
		exit();
	}
}

// close db connection
DBPal::finish();

// TODO: move to ruby daemon
fwrite(STDOUT, "took " . (time() - $time) . " seconds\n");
