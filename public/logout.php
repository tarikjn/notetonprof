<?php

define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");

$user->logout();

$_SESSION['auth.message'] = "Tu as été deconnecté avec succès";		

// redirect
Web::redirect($_SESSION['auth.previous_page']);
