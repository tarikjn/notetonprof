<?php

// load portal settings
require_once("conf/Settings.php");

// locale needs to be installed on the system
setlocale(LC_ALL, Settings::LOCALE);

// mini web framework static
require_once("lib/Web.php");

// database library static
require_once("lib/DBPal.php");

// portal application static
require_once("lib/App.php");

// load geographical settings
require_once("conf/Geo.php");

// portal helper static
require_once("lib/Helper.php");

// start session for all pages
// used both for auth and passing variables between pages
session_start();

// save page position for login/logout redirects
if ( !in_array(Web::action(), array('login', 'logout')) )
	$_SESSION['auth.previous_page'] = $_SERVER['REQUEST_URI'];

// uer authentification singleton
require_once("lib/UserAuth.php");

// load moderation settings
require_once("conf/Admin.php");
