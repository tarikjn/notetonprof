<?php

// load reCAPTCHA for forms
require_once('third-party/recaptchalib.php');

// used statically
class Web
{
	static function getReCaptcha($user = null, $ly = 'dl')
	{
		return sprintf(Helper::$RC_LAYOUTS[$ly], recaptcha_get_html(Settings::RC_PUBLIC_K));
		
		// TODO: display mode for logged in user
	}
	
	static function checkReCaptcha($user, &$notice)
	{
		$rc_resp = recaptcha_check_answer(Settings::RC_PRIVATE_K,
	                               $_SERVER["REMOTE_ADDR"],
	                               $_POST["recaptcha_challenge_field"],
	                               $_POST["recaptcha_response_field"]);

		if (!$rc_resp->is_valid)
			$notice["recaptcha"] = "le reCAPTCHA entré est incorrect.";
		
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
	
	static function emailEncode($str)
	{
		$encoded = bin2hex($str); 
		$encoded = chunk_split($encoded, 2, '%'); 
		$encoded = '%'.substr($encoded, 0, strlen($encoded) - 1); 
		return $encoded; 
	}
}

// short function wrapper
function h($s)
{
	return htmlspecialchars($s);
}
