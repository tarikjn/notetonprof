#!/usr/local/php5/bin/php -q
<?php
require("_ini.php");

$time = time();

if ($argc < 1)
{
	fwrite(STDERR, "hash argument missing\n");
	exit();
}
else
{
	if (strlen(@$argv[1]) == 8)
	{
		fprintf(STDOUT, "Sending email %s...\n", $argv[1]);
		
		// TODO: move code to a class method
		// starts here
		$hash = $argv[1];
		$filename = "email/email.$hash.txt";
		
		// TODO: add locked and level params
		$admins = DBPal::getList(
		      "SELECT email FROM delegues"
		    . " WHERE status = 'ok' AND mail_admins = 'on'"
		  );
		$admins = array('tarikjn@gmail.com', 'tarik@tajn.net', 'tarik@campuscitizens.com');
		
		if (sizeof($admins) > 0)
		{
		    // TODO disabled PHP execution from file
		    $message = file_get_contents($filename);
		    
		    // send activation email
		    $numSent = Mail::sendMail($admins,
		                              "Ouverture de NoteTonProf.com",
		                              $message);
		    
		    // TODO: find a way to log any issues
		    fwrite(STDOUT, "Sent %d messages\n", $numSent);
		    
		    // delete file
		    unlink($filename);
		}
		// ends here
	}
	else
	{
		fwrite(STDERR, "incorrect email hash\n");
		exit();
	}
}

// close db connection
DBPal::finish();

// TODO: move to ruby daemon
fwrite(STDOUT, "took " . (time() - $time) . " seconds\n");
