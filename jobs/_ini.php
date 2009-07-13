<?php

// TODO: clean-up of used libraries
// TODO: no repeat with htaccess
ini_set("include_path", ".:/home/frportal/www/notetonprof.fr/application");
ini_set("date.timezone", "Europe/Paris");

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

// load moderation settings
require_once("conf/Admin.php");

// CLI classes
require_once("lib/Assign.php");
