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
	$hash = $argv[1];
	$filename = "emails/email.$hash.txt";
	
	// check file exist
	if (file_exists($filename))
	{
		fprintf(STDOUT, "Sending email %s...\n", $argv[1]);
		
		// TODO: move code to a class method
		// starts here
		
		
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
		    $numSent = Mail::batchMail($admins,
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
		fwrite(STDERR, "cannot find %s\n", $filename);
		exit();
	}
}

// close db connection
DBPal::finish();

// TODO: move to ruby daemon
fwrite(STDOUT, "took " . (time() - $time) . " seconds\n");
