<?php

// required because php doesn't provide real session manipulation functions
require_once('lib/RealSession.php');

// $user singleton init bellow the definition
class UserAuth
{
	/*
	 * This class can log 3 types of login
	 * - returning user get logged-in by saved cookie
	 * - user log-in and choose to save a cookie
	 * - user log-in (barehands!)
	 */
	
	// TODO: feedback for passive disconnection
	
	// TODO: cases where the user try to login as a different user while already logged:
	//       - if user open login while logged -> unset previous session if success
	//       - set session_id to null on logout/unset

	public $uid = null;
	public $power = 0;
	public $username = '';
	
	const REQ_QUERY = "locked = 'no' AND checked = 1 AND status = 'ok'";
	
	// private
	const COOKIE_NAME = 'ccPortalAuth';
	const COOKIE_LENGTH = 31536000; // ~1 year
	
	private $log_msg = null; // temporary variable

	function UserAuth()
	{
		// check if the session is there
		if (@$_SESSION['auth.uid'])
		{
			$this->checkSession();
		}
		// if it isn't, check if the user saved a cookie
		else if ( isset($_COOKIE[self::COOKIE_NAME]) )
		{
			$this->checkRemembered($_COOKIE[self::COOKIE_NAME]);
		}
	}
	
	/* **************
	 * site methods
	 */
	
	function requireOrRedirect($power = 1)
	{
		// if not logged-in redirect to login page
		if (!$this->uid)
		{
			Web::redirect('/login?return=1');
		}
		
		// if logged-in and credentials are insuffisant
		else if ($this->power < $power)
		{
			$_SESSION['auth.message'] = "Désolé, tu n'a pas de droit d'accès suffisant pour accéder à cette page";
			
			// neeeds 2nd level previous_page and test to use previous_page and avoid redirect loop
			Web::redirect('/login?return=1');
		}
	}
	
	function logout()
	{
		// if the user has a cookie delete it
		if ( isset($_COOKIE[self::COOKIE_NAME]) )
			$this->unsetRememberCookie();
		
		// delete session auth data
		$this->unsetAuthSession();
		
		// redirect handled by logout script
	}
	
	/* ***************
	 * login methods
	 */
	
	function checkLogin($login, $pass, $remember)
	{
		$login = DBPal::quote($login);
		$pass = DBPal::quote(md5($pass));
		
		$sql = "SELECT * FROM delegues WHERE email = $login AND md5_pass = $pass AND " . self::REQ_QUERY;
		$result = DBPal::getRow($sql);
		
		if ( is_object($result) )
		{
			$this->log_msg = ($remember)? 'Login and saved cookie' : 'Login';
			$this->setSession($result, true, $remember);
		}
	}
	
	/*
	 * This function invalidate the session data of the CURRENT
	 * USER, it is useful if the user update his account data
	 * it will have the session reload the data it holds
	 *
	 * If the user has a cookie, it won't get updated with the
	 * new password hash (could be done)
	 *
	 * See Web:setRandomSessionVar for invalidating the session
	 * of a random user
	 */
	 // TODO: this method must be called when the user change his accound data
	function invalidateSessionData()
	{
		// this flag will require the checkSession method to requery the db
		$_SESSION['auth.revalidate'] = true;
		
		// force immediate session revalidation
		$this->checkSession();
	}
	
	/* ******************************
	 * session manipulation methods
	 */
	// TODO: that function must be called instead of
	// setRandomAuthSessionVar if the password
	// is changed for a user
	static function sessionUnsetAuth($sess)
	{
		unset($sess['auth.uid'], $sess['auth.power'], $sess['auth.username']);
		return $sess;
	}
	
	/*
	 * This is used as security feature, when used to set the
	 * invalidate flag of a random user session e.g. this will
	 * disconnect a user immediately if an administrator lock
	 * his account
	 */
	// TODO: this method must be called when changing the accound data of another user
	static function sessionSetRevalidate($sess)
	{
		$sess['auth.revalidate'] = true;
		return $sess;
	}
	
	/* *****************
	 * private methods
	 */
	
	private function setSession(&$values, $init = false, $remember = false, $revalidate = false)
	{
		$this->uid = (int) $values->id;
		$this->power = (int) $values->level;
		$this->username = $values->prenom;
		
		// will be triggered on actual connection or session revalidation
		if ($init or $revalidate)
		{
			// save the session
			$_SESSION['auth.uid'] = $this->uid;
			$_SESSION['auth.power'] = $this->power;
			$_SESSION['auth.username'] = $this->username;
		}
		
		if ($remember)
		{
			$this->updateCookie($values->md5_pass);
			// TODO: reverify mechanism on http://www.mtdev.com/2002/07/creating-a-secure-php-login-script
		}
		
		// will be triggered on actual connection
		if ($init)
		{
			// delete any previous session (because it won't be tracked anymore)
			if ($values->session_id)
				RealSession::replace($values->session_id, array('UserAuth', 'sessionUnsetAuth'));
		
			// update the connection info
			$session_id = DBPal::quote(session_id());
			$sql = "UPDATE delegues SET last_conn = NOW(), session_id = {$session_id} WHERE id = {$this->uid}";
			DBPal::query($sql);
			
			// log the connection
			App::log($this->log_msg, "user", $this->uid, $this->uid, null, null, true);
		}
	}
	
	private function updateCookie($cookie_hash)
	{
		$cookie = serialize(array($this->uid, $cookie_hash));
		setcookie('ccPortalAuth', $cookie, time() + self::COOKIE_LENGTH, '/', Settings::COOKIE_DOMAIN);
	}
	
	private function checkRemembered($cookie)
	{
		list($uid, $enc_pass) = @unserialize($cookie);
		if (!$uid or !$enc_pass) return;
		
		$uid = (int) $uid;
		$enc_pass = DBPaL::quote($enc_pass);
		
		$sql = "SELECT * FROM delegues WHERE id = $uid AND md5_pass = $enc_pass AND " . self::REQ_QUERY;
		$result = DBPal::getRow($sql);
		
		if ( is_object($result) )
		{
			$this->log_msg = 'Login from saved cookie';
			$this->setSession($result, true);
		}
		else
		{
			// unset the cookie, or it is going to query the db for each page load
			$this->unsetRememberCookie();
		}
	}
	
	private function checkSession()
	{
		if (@$_SESSION['auth.revalidate'])
		{
			$sql = "SELECT * FROM delegues WHERE id = {$_SESSION['auth.uid']} "
			     . "AND session_id = " . DBPal::quote(session_id()) . " AND " . self::REQ_QUERY;
			$result = DBPal::getRow($sql);
		}
		else
		{
			$result = (object) array(
				'id' => $_SESSION['auth.uid'],
				'level' => $_SESSION['auth.power'],
				'prenom' => $_SESSION['auth.username']
			);
		}
		
		if ( is_object($result) )
		{
			$this->setSession($result);
		}
		else
		{
			// delete current user auth session data
			$this->unsetAuthSession();
		}
	}
	
	private function unsetAuthSession()
	{
		unset($_SESSION['auth.uid'], $_SESSION['auth.power'], $_SESSION['auth.username']);
	}
	
	private function unsetRememberCookie()
	{
		setcookie(self::COOKIE_NAME, '', time() - 24 * 3600, '/', Settings::COOKIE_DOMAIN);
	}
}

// instance init (booh PHP! no real singleton support)
$user = new UserAuth();
