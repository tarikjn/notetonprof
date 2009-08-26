<?php

// used statically
class Web
{
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
	
	/* *****************
	 * authentication
	 */
	
	static function isEmail($string)
	{
		return eregi('^[\._a-z0-9-]+@[a-z0-9-]+(\.[a-z0-9-]+)*$', $string);
	}
	
	static function genPass($length = 8)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_.";
		$pass = "";
		
		for($i = 0; $i < $length; $i++)
			$pass .= $chars{mt_rand(0, strlen($chars) - 1)};
		
		return $pass;
	}
}
