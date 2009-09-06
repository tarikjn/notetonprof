<?php

// require Swift Mailer
require_once 'third-party/swiftmailer/swift_required.php';

class Mail
{
	const SMTP_HOST = "smtp.notetonprof.com";
	const SMTP_PORT = 5190;
	const SMTP_USER = "frportal";
	const SMTP_PASS = "PehuF5Ac";
	
	static $FROM = array("root@notetonprof.com" => "NoteTonProf.com");
	static $REPLY_TO = array("info@notetonprof.com" => "NoteTonProf.com");
	
	private static $mailer = null;
	
	// source: http://codingforums.com/showthread.php?t=93453
	static function isValidEmail($string)
	{
		$dot_atom_re = '[-!#\$%&\'\*\+\/=\?\^_`{}\|~0-9A-Z]+(?:\.[-!#\$%&\'\*\+\/=\?\^_`{}\|~0-9A-Z]+)*';
    	$implemented_domain_re = '[-0-9A-Z]+(?:\.[-0-9A-Z]+)*';
    	$full_pattern = '/^'.$dot_atom_re.'(?:@'.$implemented_domain_re.')?$/iD';
    	if (preg_match($full_pattern, $string)) return true;
    	else return false;
    }
    
    
    
    static function sendMail($recipient, $subject, $tpl_name, $tpl_vars = null)
    {
    	if (!self::$mailer)
    		self::setMailer();
		
		//Create a message
		$message = Swift_Message::newInstance($subject)
		  ->setFrom(self::$FROM)
		  ->setReplyTo(self::$REPLY_TO)
		  ->setTo($recipient)
		  ->setBody(self::loadTemplate($tpl_name, $tpl_vars))
		  ;
  		
		//Send the message
		$result = self::$mailer->send($message);
    }
    
    private static function setMailer()
    {
    	//Create the Transport
    	if (!Settings::USE_SENDMAIL)
			$transport = Swift_SmtpTransport::newInstance(self::SMTP_HOST, self::SMTP_PORT)
			  ->setUsername(self::SMTP_USER)
			  ->setPassword(self::SMTP_PASS)
			  ;
		else
			$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
		
		//Create the Mailer using your created Transport
		self::$mailer = Swift_Mailer::newInstance($transport);
    }
    
    private static function loadTemplate($template_name, $var)
    {
    	$var['base_url'] = Settings::WEB_ROOT;
    	$var = (object) $var;
    	
    	ob_start();
		require("tpl/emails/$template_name.text.php");
		$content = ob_get_clean();
		
		return $content;
    }
}
