<?php

class Settings
{
	const IS_PROD = true;

	const DB_HOST = 'localhost';
	const DB_BASE = 'frportal';
	const DB_USER = 'frportal';
	const DB_PASS = '9xmw5LrH';
	
	const DB_MAX_TIME_DIFF = 15; // 15 seconds delay allowed with the SQL server time, max 60
	
	const LOCALE = 'fr_FR.UTF-8';
	// timezone set in .htaccess
	
	const WEB_ROOT = 'http://www.notetonprof.com';
	const WEB_PATH = '';
	
	const COOKIE_DOMAIN = '.notetonprof.com';
	
	const SESSION_PATH = '/var/tmp';
	
	const BEANSTALKQ = 'localhost:11300';
	
	// reCAPTCHA keys
	const RC_PUBLIC_K = '6LcfrwkAAAAAAHOPn_OoN8USYbCmwWWbHNhvd8UD';
	const RC_PRIVATE_K = '6LcfrwkAAAAAAFhMAU_ASJye3olor77BMAMrmS98';
	
	// Mail settings
	const USE_SENDMAIL = true;
	
	static $objType2tabName = Array(
	    "user"    => "delegues",
	    "school"  => "etablissements",
	    "prof"    => "professeurs",
	    "comment" => "notes",
	    "report"  => "reports"
	  );
	
	const COMMENT_MAX_LEN = 300;
}
