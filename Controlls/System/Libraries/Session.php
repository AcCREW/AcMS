<?php 
class CSession {

	public static $SESS_ENCRYPT_COOKIE      = false;
	public static $SESS_EXPIRATION			= 7200;
	public static $SESS_EXPIRE_ON_CLOSE		= false;
	public static $SESS_MATCH_IP			= false;
	public static $SESS_MATCH_USERAGENT		= true;
	public static $SESS_COOKIE_NAME			= 'AC_SESSION';
	public static $COOKIE_PREFIX			= '';
	public static $COOKIE_PATH				= '';
	public static $COOKIE_DOMAIN			= '';
	public static $COOKIE_SECURE			= false;
	public static $SESS_TIME_TO_UPDATE		= 300;
	public static $ENCRYPTION_KEY			= '';
	public static $TIME_REFERENCE			= 'time';
    
	public static $Userdata				    = array();
	public static $Now;

	/**
	 * Session Constructor
	 *
	 * The constructor runs the session routines automatically
	 * whenever the class is instantiated.
	 */
	public static function _Initialize() {
		foreach (array('SESS_ENCRYPT_COOKIE', 'SESS_EXPIRATION', 'SESS_EXPIRE_ON_CLOSE', 'SESS_MATCH_IP', 'SESS_MATCH_USERAGENT', 'SESS_COOKIE_NAME', 'COOKIE_PATH', 'COOKIE_DOMAIN', 'COOKIE_SECURE', 'SESS_TIME_TO_UPDATE', 'TIME_REFERENCE', 'COOKIE_PREFIX', 'ENCRYPTION_KEY') as $sKey) {
            $vParam = Application::GetConfig($sKey);
            if($vParam !== false) {
                self::$$sKey = $vParam;
            }
		}

		if (self::$ENCRYPTION_KEY == '') {
			show_error('In order to use the Session class you are required to set an encryption key in your config file.');
		}

		// Load the string helper so we can use the strip_slashes() function
		Loader::LoadHelper('String');


		// Set the "now" time.  Can either be GMT or server time, based on the
		// config prefs.  We use this to set the "last activity" time
		self::$Now = self::_GetTime();
        
		// Set the session length. If the session expiration is
		// set to zero we'll set the expiration two years from now.
		if (self::$SESS_EXPIRATION == 0) {
			self::$SESS_EXPIRATION = (60*60*24*365*2);
		}

		// Set the cookie name
		self::$SESS_COOKIE_NAME = self::$COOKIE_PREFIX.self::$SESS_COOKIE_NAME;
        
		// Run the Session routine. If a session doesn't exist we'll
		// create a new one.  If it does, we'll update it.
		if (!self::SessRead()) {
			self::SessCreate();
		} else {
			self::SessUpdate();
		}
        
        Application::$IsLogged = self::Get('IsLogged') === true;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the current session data if it exists
	 *
	 * @access	public
	 * @return	bool
	 */
	private static function SessRead() {
		// Fetch the cookie
		$Session = CInput::Cookie(self::$SESS_COOKIE_NAME);

		// No cookie?  Goodbye cruel world!...
		if ($Session === false) {
			log_message('debug', 'A session cookie was not found.');
			return false;
		}

		// HMAC authentication
		$nLen = strlen($Session) - 40;

		if ($nLen <= 0) {
			log_message('error', 'Session: The session cookie was not signed.');
			return false;
		}

		// Check cookie authentication
		$sHmac = substr($Session, $nLen);
		$Session = substr($Session, 0, $nLen);

		// Time-attack-safe comparison
		$sHmacCheck = hash_hmac('sha1', $Session, self::$ENCRYPTION_KEY);
		$nDiff = 0;

		for ($i = 0; $i < 40; $i++) {
			$cXOR = ord($sHmac[$i]) ^ ord($sHmacCheck[$i]);
			$nDiff |= $cXOR;
		}

		if ($nDiff !== 0) {
			log_message('error', 'Session: HMAC mismatch. The session cookie data did not match what was expected.');
			self::SessDestroy();
			return false;
		}

		// Decrypt the cookie data
		if (self::$SESS_ENCRYPT_COOKIE == TRUE) {
			$Session = CEncrypt::Decode($Session);
		}

		// Unserialize the session array
		$Session = self::_Unserialize($Session);

		// Is the session data we unserialized an array with the correct format?
		if (!is_array($Session) || !isset($Session['session_id']) || !isset($Session['ip_address']) || !isset($Session['user_agent']) || !isset($Session['last_activity'])) {
			self::SessDestroy();
			return false;
		}

		// Is the session current?
		if (($Session['last_activity'] + self::$SESS_EXPIRATION) < self::$Now) {
			self::SessDestroy();
			return false;
		}

		// Does the IP Match?
		if (self::$SESS_MATCH_IP == true && $Session['ip_address'] != CInput::IPAddress()) {
			self::SessDestroy();
			return false;
		}

		// Does the User Agent Match?
		if (self::$SESS_MATCH_USERAGENT == true && trim($Session['user_agent']) != trim(substr(CInput::UserAgent(), 0, 120))) {
			self::SessDestroy();
			return false;
		}

		// Session is valid!
		self::$Userdata = $Session;
		unset($Session);

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Write the session data
	 *
	 * @access	public
	 * @return	void
	 */
	public static function SessWrite() {
        self::_SetCookie();
	}

	// --------------------------------------------------------------------

	/**
	 * Create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	public static function SessCreate() {
		$sSessID = '';
		while (strlen($sSessID) < 32) {
			$sSessID .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		$sSessID .= CInput::IPAddress();

		self::$Userdata = array(
		    'session_id'	=> md5(uniqid($sSessID, true)),
		    'ip_address'	=> CInput::IPAddress(),
		    'user_agent'	=> substr(CInput::UserAgent(), 0, 120),
		    'last_activity'	=> self::$Now,
		    'user_data'		=> ''
		);

		self::_SetCookie();
	}

	// --------------------------------------------------------------------

	/**
	 * Update an existing session
	 *
	 * @access	public
	 * @return	void
	 */
	public static function SessUpdate() {
		// We only update the session every five minutes by default
		if ((self::$Userdata['last_activity'] + self::$SESS_TIME_TO_UPDATE) >= self::$Now) {
			return;
		}

		// Save the old session id so we know which record to
		// update in the database if we need it
		$sNewSessID = '';
		while (strlen($sNewSessID) < 32) {
			$sNewSessID .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		$sNewSessID .= CInput::IPAddress();

		// Turn it into a hash
		$sNewSessID = md5(uniqid($sNewSessID, true));

		// Update the session data in the session data array
		self::$Userdata['session_id'] = $sNewSessID;
		self::$Userdata['last_activity'] = self::$Now;

		// _SetCookie() will handle this for us if we aren't using database sessions
		// by pushing all userdata to the cookie.
		$CookieData = null;

		// Write the cookie
		self::_SetCookie($CookieData);
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	public static function SessDestroy() {
		// Kill the cookie
		setcookie(
			self::$SESS_COOKIE_NAME,
			addslashes(serialize(array())),
			(self::$Now - 31500000),
			self::$COOKIE_PATH,
			self::$COOKIE_DOMAIN,
			false
		);

		// Kill session data
		self::$Userdata= array();
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a specific item from the session array
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function Userdata($sName) {
		return (!isset(self::$Userdata[$sName])) ? false : self::$Userdata[$sName];
	}
	public static function Get($sName) {
        return self::Userdata($sName);
    }
	// --------------------------------------------------------------------

	/**
	 * Fetch all session data
	 *
	 * @access	public
	 * @return	array
	 */
	public static function AllUserdata() {
		return self::$Userdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Add or change data in the "userdata" array
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
    public static function Set($arNewdata = array(), $sNewval = '') {
        if (is_string($arNewdata)) {
			$arNewdata = array($arNewdata => $sNewval);
		}

		if (sizeof($arNewdata) > 0) {
			foreach ($arNewdata as $sKey => $vVal) {
				self::$Userdata[$sKey] = $vVal;
			}
		}

		self::SessWrite();
    }

	// --------------------------------------------------------------------

	/**
	 * Delete a session variable from the "userdata" array
	 *
	 * @access	array
	 * @return	void
	 */
    public static function Remove($arNewdata = array()) {
        if (is_string($arNewdata)) {
			$arNewdata = array($arNewdata => '');
		}

		if (count($arNewdata) > 0) {
			foreach ($arNewdata as $sKey => $vVal) {
				unset(self::$Userdata[$sKey]);
			}
		}

		self::SessWrite();
    }

	// --------------------------------------------------------------------

	/**
	 * Get the "now" time
	 *
	 * @access	private
	 * @return	string
	 */
	public static function _GetTime() {
		if (strtolower(self::$TIME_REFERENCE) == 'gmt') {
			$nNow = time();
			$sTime = mktime(gmdate("H", $nNow), gmdate("i", $nNow), gmdate("s", $nNow), gmdate("m", $nNow), gmdate("d", $nNow), gmdate("Y", $nNow));
		} else {
			$sTime = time();
		}

		return $sTime;
	}

	// --------------------------------------------------------------------

	/**
	 * Write the session cookie
	 *
	 * @access	public
	 * @return	void
	 */
	public static function _SetCookie($CookieData = null) {
		if (is_null($CookieData)) {
			$CookieData = self::$Userdata;
		}

		// Serialize the userdata for the cookie
		$CookieData = self::_Serialize($CookieData);

		if (self::$SESS_ENCRYPT_COOKIE == true) {
			$CookieData = CEncrypt::Encode($CookieData);
		}

		$CookieData .= hash_hmac('sha1', $CookieData, self::$ENCRYPTION_KEY);

		$nExpire = (self::$SESS_EXPIRE_ON_CLOSE === true) ? 0 : self::$SESS_EXPIRATION + time();
		
		// Set the cookie
		setcookie(
			self::$SESS_COOKIE_NAME,
			$CookieData,
			$nExpire,
			self::$COOKIE_PATH,
			self::$COOKIE_DOMAIN,
			self::$COOKIE_SECURE
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Serialize an array
	 *
	 * This function first converts any slashes found in the array to a temporary
	 * marker, so when it gets unserialized the slashes will be preserved
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	public static function _Serialize($arData) {
		if (is_array($arData)) {
			foreach ($arData as $sKey => $vVal) {
				if (is_string($vVal)) {
					$arData[$sKey] = str_replace('\\', '{{slash}}', $vVal);
				}
			}
		} else {
			if (is_string($arData)) {
				$arData = str_replace('\\', '{{slash}}', $arData);
			}
		}

		return serialize($arData);
	}

	// --------------------------------------------------------------------

	/**
	 * Unserialize
	 *
	 * This function unserializes a data string, then converts any
	 * temporary slash markers back to actual slashes
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	public static function _Unserialize($sData) {
		$arData = @unserialize(strip_slashes($sData));

		if (is_array($arData)) {
			foreach ($arData as $sKey => $vVal) {
				if (is_string($vVal)) {
					$arData[$sKey] = str_replace('{{slash}}', '\\', $vVal);
				}
			}

			return $arData;
		}

		return (is_string($arData)) ? str_replace('{{slash}}', '\\', $arData) : $arData;
	}

	// --------------------------------------------------------------------
}
// END Session Class

/* End of file Session.php */
/* Location: ./system/libraries/Session.php */
