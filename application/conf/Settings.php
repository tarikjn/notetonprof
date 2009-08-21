<?php

class Settings
{
	const DB_HOST = '127.0.0.1';
	const DB_BASE = 'frportal';
	const DB_USER = 'notetonproffr';
	const DB_PASS = '9xmw5LrH';
	
	const DB_MAX_TIME_DIFF = 15; // 15 seconds delay allowed with the SQL server time, max 60
	
	const LOCALE = 'fr_FR.UTF-8';
	// timezone set in .htaccess
	
	const WEB_ROOT = 'http://www.notetonprof.fr/'; // dev: http://localhost/~tarik/frportal/trunk/public/
	
	const COOKIE_DOMAIN = '.notetonprof.fr';
	
	const SESSION_PATH = '/var/tmp';
	
	static $objType2tabName = Array(
	    "user"    => "delegues",
	    "school"  => "etablissements",
	    "prof"    => "professeurs",
	    "comment" => "notes"
	  );
	
	const COMMENT_MAX_LEN = 255;
}

