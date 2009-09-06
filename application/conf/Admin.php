<?php

class Admin
{
	const MOD_SCHOOL = false;
	const MOD_PROF = false;
	const MOD_COMMENT = true; // not used in code, assumed always true
	
	const COMMENTS_ON = true; // not in use
	
	static $RANKS = array(
	    1 => 'Apprenti Modérateur',
	    2 => 'Modérateur',
	    3 => 'Modérateur Général',
	    4 => 'Super-Modérateur',
	    5 => 'Opérateur'
	  );
	
	static $MAX_SCHOOLS = array(
	    1 => 2,
	    2 => 5,
	    3 => null,
	    4 => null,
	    5 => null,
	  );
	
	const MIN_SCORE = 350;
	
	const ACC_SHOW_SCORE = 4;
	
	const ACC_MONITOR = 5;
	const ACC_DATA = 2;
	const ACC_ALL_DATA = 3;
	const ACC_ADMINS = 4;
	const ACC_CHANGE_POWERS = 5;
	const ACC_PRE_MODERATE = 1;
	const ACC_REAL_MODERATE = 2;
	const ACC_DATA_TICKET = 3;
	const ACC_COMMENT_TICKET = 4;
	const ACC_RAISED_OBJECT = 5;
	
	const QUOTA_PROFS_PER_ADMIN = 10;
	const QUOTA_MAX_REDONDANCY = 2;
	
	const DEF_ADMIN_INACTIVE_AFTER = 7; // days
}
