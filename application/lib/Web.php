<?php

// load reCAPTCHA for forms
require_once('third-party/recaptchalib.php');

// used statically
class Web
{
	static private $ROBOTS = array(
	    'Google',
	    'msnbot',
	    'Rambler',
	    'Yahoo',
	    'AbachoBOT',
	    'accoona',
	    'AcoiRobot',
	    'ASPSeek',
	    'CrocCrawler',
	    'Dumbot',
	    'FAST-WebCrawler',
	    'GeonaBot',
	    'Gigabot',
	    'Lycos',
	    'MSRBOT',
	    'Scooter',
	    'AltaVista',
	    'IDBot',
	    'eStyle',
	    'Scrubby'
	  );

	static function getReCaptcha($user = null, $ly = 'dl', $error_arr = null)
	{
		$retval = '';
		
		if (is_array($error_arr))
			$retval .= Helper::getFormError('recaptcha', $error_arr);
		
		$retval .= sprintf(Helper::$RC_LAYOUTS[$ly], recaptcha_get_html(Settings::RC_PUBLIC_K));
		
		return $retval;
		
		// TODO: display mode for logged in user
	}
	
	static function checkReCaptcha($user, &$notice)
	{
		$rc_resp = recaptcha_check_answer(Settings::RC_PRIVATE_K,
	                               $_SERVER["REMOTE_ADDR"],
	                               $_POST["recaptcha_challenge_field"],
	                               $_POST["recaptcha_response_field"]);

		if (!$rc_resp->is_valid)
			$notice["recaptcha"] = "Le reCAPTCHA entré est incorrect";
		else if (sizeof($notice) and @$notice)
			$notice["recaptcha"] = "Réentre le reCAPTCHA";
		
		// TODO: use this for action counter
	}
	
	static function redirect($path = '/', $fullpath = false)
	{
		DBPal::finish();
		header("Location: " . (($fullpath)?'':Settings::WEB_ROOT) . $path);
		//header("Location: http://" . $_SERVER["HTTP_HOST"] . $path);
		exit();
	}
	
	static function action()
	{
		return basename($_SERVER["SCRIPT_FILENAME"], ".php");
	}
	
	static function getPath()
	{
		return substr($_SERVER["SCRIPT_NAME"], 0, -4);
	}
	
	static function flash($key)
	{
		if (isset($_SESSION[$key]))
		{
			$val = $_SESSION[$key];
			unset($_SESSION[$key]);
		}
		else
			$val = FALSE;
		
		return $val;
	}
	
	// checks if the user agent is a human or a robot
	static function isRobot($ua)
	{
		foreach (self::$ROBOTS as $r)
		{
			if (stristr($ua, $r))
				return true;
		}
		
		return false;
	}
	
	static function emailEncode($str)
	{
		$encoded = bin2hex($str); 
		$encoded = chunk_split($encoded, 2, '%'); 
		$encoded = '%'.substr($encoded, 0, strlen($encoded) - 1); 
		return $encoded; 
	}
	
	static function checkBlacklisted(&$notice)
	{
		$match = DBPal::getOne("SELECT id FROM blacklist WHERE ip = " . DBPal::quote($_SERVER["REMOTE_ADDR"]) . " AND status = 'active'");

		if ($match)
		{
			DBPal::query("UPDATE blacklist SET attempts = attempts + 1 WHERE id = $match");
			$notice["blacklist"] = "Action refusée, veuillez contacter <a href=\"mailto:ops@notetonprof.com\">les opérateurs</a>.";
		}
	}
	
	static function valid_ip($ip)
	{ 
	    return preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])" . 
	            "(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip); 
	}
}

// short function wrapper
function h($s)
{
	return htmlspecialchars($s);
}
