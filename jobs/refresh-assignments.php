#!/usr/local/php5/bin/php -q
<?php
require("_ini.php");

static $object_types = array(
	"user",
	"school",
	"prof",
	"comment"
);
$time = time();

if ($argc < 2)
{
	fwrite(STDERR, "action argument missing\n");
	exit();
}
else
{
	switch($argv[1])
	{
		case 'for-object':
			if ($argc != 4)
			{
				fwrite(STDERR, "incorrect number of arguments\n");
				exit();
			}
			else
			{
				$id = (int) $argv[3];
				
				if (!in_array($argv[2], $object_types))
				{
					fwrite(STDERR, "unknown object type\n");
					exit();
				}
				else if (!$id)
				{
					fwrite(STDERR, "incorrect object id\n");
					exit();
				}
				else
				{
					fwrite(STDOUT, "Refreshing assignments for object...\n");
					Assign::refreshForObject($argv[2], $id);
				}
			}
			break;
		
		case 'all':
			fwrite(STDOUT, "Refreshing all assignments...\n");
			Assign::refreshAll();
			break;
		
		case 'of-admin':
		case 'for-admin':
		case 'for-school':
			if ($argc != 3)
			{
				fwrite(STDERR, "incorrect number of arguments\n");
				exit();
			}
			else
			{
				$id = (int) $argv[2];
				
				if (!$id)
				{
					fwrite(STDERR, "incorrect object id\n");
					exit();
				}
				else
				{
					if ($argv[1] == 'of-admin')
					{
						fwrite(STDOUT, "Refreshing existing assignments of admin...\n");
						Assign::refreshAssignedToAdmin($id);
					}
					else if ($argv[1] == 'for-admin')
					{
						fwrite(STDOUT, "Refreshing assignments for admin...\n");
						Assign::refreshForAdmin($id);
					}
					else
					{
						fwrite(STDOUT, "Refreshing assignments for school...\n");
						Assign::refreshForSchool($id);
					}
				}
			}
			break;
		
		default:
			fwrite(STDERR, "unknown action\n");
			exit();
	}
}

// close db connection
DBPal::finish();

// TODO: move to ruby daemon
fwrite(STDOUT, "took " . (time() - $time) . " seconds\n");
