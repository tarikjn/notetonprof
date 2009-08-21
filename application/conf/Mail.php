<?php

class Mail
{
	const SMTP_HOST = "smtp.notetonprof.fr";
	const SMTP_AUTH = true;
	const SMTP_USER = "frportal";
	const SMTP_PASS = "PehuF5Ac";
	
	const BOT_MAIL = "robot@notetonprof.fr";
	const BOT_NAME = "[NoteTonProf.fr]";
	const ROOT_MAIL = "infos@notetonprof.fr";
	const ROOT_NAME = "[NoteTonProf.fr]";
}

require("third-party/class.phpmailer.php");

$smtp = "smtp.notetonprof.fr";		// hoster's smtp
$smtpA = true;
$smtpUser = "frportal";
$smtpPass = "PehuF5Ac";

$botMail = "robot@notetonprof.fr";	// robot@notetonprof.fr
$botName = "[NoteTonProf.fr]";
$rootMail = "infos@notetonprof.fr";
$rootName = "[NoteTonProf.fr]";

$gotoURL = "http://".$_SERVER["HTTP_HOST"];
