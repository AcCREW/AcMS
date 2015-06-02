<?php
/**
 * Input Class
 *
 * Pre-processes global input data for security
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Input
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/input.html
 */
class CInput {

	/**
	 * IP address of the current user
	 *
	 * @var string
	 */
	private static $IPAddress				= false;
	/**
	 * user agent (web browser) being used by the current user
	 *
	 * @var string
	 */
	private static $UserAgent				= false;
	/**
	 * If FALSE, then $_GET will be set to an empty array
	 *
	 * @var bool
	 */
	private static $_AllowGetArray		    = true;
	/**
	 * If TRUE, then newlines are standardized
	 *
	 * @var bool
	 */
	private static $_StandardizeNewlines    = true;
	/**
	 * Determines whether the XSS filter is always active when GET, POST or COOKIE data is encountered
	 * Set automatically based on config setting
	 *
	 * @var bool
	 */
	private static $_EnableXSS			    = false;
	/**
	 * Enables a CSRF cookie token to be set.
	 * Set automatically based on config setting
	 *
	 * @var bool
	 */
	private static $_EnableCSRF             = false;
	/**
	 * List of all HTTP request headers
	 *
	 * @var array
	 */
	protected static $Headers			    = array();

	/**
	 * Constructor
	 *
	 * Sets whether to globally enable the XSS processing
	 * and whether to allow the $_GET array
	 *
	 * @return	void
	 */
	public static function _Initialize() {
		log_message('debug', "Input Class Initialized");

		self::$_AllowGetArray	= (Application::GetConfig('ALLOW_GET_ARRAY') === true);
		self::$_EnableXSS		= (Application::GetConfig('ENABLE_XSS_FILTERING') === true);
		self::$_EnableCSRF		= (Application::GetConfig('CSRF_PROTECTION') === true);

		// Sanitize global arrays
		self::_SanitizeGlobals();
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch from array
	 *
	 * This is a helper function to retrieve values from global arrays
	 *
	 * @access	private
	 * @param	array
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public static function _FetchFromArray(&$arArray, $sIndex = '', $bIsXSSClean = false) {
		if (!isset($arArray[$sIndex])) {
			return false;
		}

		if ($bIsXSSClean === true) {
			return CSecurity::XSSClean($arArray[$sIndex]);
		}

		return $arArray[$sIndex];
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the GET array
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public static function Get($sIndex = null, $bIsXSSClean = false) {
		// Check if a field has been provided
		if ($sIndex === null && !empty($_GET)) {
			$arGet = array();

			// loop through the full _GET array
			foreach (array_keys($_GET) as $sKey) {
				$arGet[$sKey] = self::_FetchFromArray($_GET, $sKey, $bIsXSSClean);
			}
			return $arGet;
		}

		return self::_FetchFromArray($_GET, $sIndex, $bIsXSSClean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the POST array
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public static function Post($sIndex = null, $bIsXSSClean = false) {
		// Check if a field has been provided
		if ($sIndex === null && !empty($_POST)) {
			$arPost = array();

			// Loop through the full _POST array and return it
			foreach (array_keys($_POST) as $sKey) {
				$arPost[$sKey] = self::_FetchFromArray($_POST, $sKey, $bIsXSSClean);
			}
			return $arPost;
		}

		return self::_FetchFromArray($_POST, $sIndex, $bIsXSSClean);
	}


	// --------------------------------------------------------------------

	/**
	 * Fetch an item from either the GET array or the POST
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	bool	XSS cleaning
	 * @return	string
	 */
	public static function PostGet($sIndex = '', $bIsXSSClean = false) {
		if (!isset($_POST[$sIndex]) ) {
			return self::Get($sIndex, $bIsXSSClean);
		} else {
			return self::Post($sIndex, $bIsXSSClean);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public static function Cookie($sIndex = '', $bIsXSSClean = false) {
		return self::_FetchFromArray($_COOKIE, $sIndex, $bIsXSSClean);
	}

	// ------------------------------------------------------------------------

	/**
	 * Set cookie
	 *
	 * Accepts six parameter, or you can submit an associative
	 * array in the first parameter containing all the values.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string	the value of the cookie
	 * @param	string	the number of seconds until expiration
	 * @param	string	the cookie domain.  Usually:  .yourdomain.com
	 * @param	string	the cookie path
	 * @param	string	the cookie prefix
	 * @param	bool	true makes the cookie secure
	 * @return	void
	 */
	public static function SetCookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = false) {
		if (is_array($name)) {
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'name') as $item) {
				if (isset($name[$item])) 	{
					$$item = $name[$item];
				}
			}
		}

		if ($prefix == '' && Application::GetConfig('COOKIE_PREFIX') != '') {
			$prefix = Application::GetConfig('COOKIE_PREFIX');
		}
		if ($domain == '' && Application::GetConfig('COOKIE_DOMAIN') != '') {
			$domain = Application::GetConfig('COOKIE_DOMAIN');
		}
		if ($path == '/' && Application::GetConfig('COOKIE_PATH') != '/') {
			$path = Application::GetConfig('COOKIE_PATH');
		}
		if ($secure == false && Application::GetConfig('COOKIE_SECURE') != false) {
			$secure = Application::GetConfig('COOKIE_SECURE');
		}

		if ( ! is_numeric($expire)) {
			$expire = time() - 86500;
		} else {
			$expire = ($expire > 0) ? time() + $expire : 0;
		}

		setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public static function Server($sIndex = '', $bIsXSSClean = false) {
		return self::_FetchFromArray($_SERVER, $sIndex, $bIsXSSClean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the IP Address
	 *
	 * @return	string
	 */
	public static function IPAddress() {
		if (self::$IPAddress !== false) {
			return self::$IPAddress;
		}

		$sProxyIPs = Application::GetConfig('PROXY_IPS');
		if (!empty($sProxyIPs)) {
			$arProxyIPs = explode(',', str_replace(' ', '', $sProxyIPs));
            $sSpoof = false;
			foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $sHeader) {
				if (($sSpoof = self::Server($sHeader)) !== false) {
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					if (strpos($sSpoof, ',') !== false) {
						$arSpoof = explode(',', $sSpoof, 2);
						$sSpoof = $arSpoof[0];
					}

					if (!self::valid_ip($sSpoof)) {
						$sSpoof = false;
					} else {
						break;
					}
				}
			}

			self::$IPAddress = ($sSpoof !== false && in_array($_SERVER['REMOTE_ADDR'], $arProxyIPs, true)) ? $sSpoof : $_SERVER['REMOTE_ADDR'];
		} else {
			self::$IPAddress = $_SERVER['REMOTE_ADDR'];
		}

		if (!self::ValidIP(self::$IPAddress)) {
			self::$IPAddress = '0.0.0.0';
		}

		return self::$IPAddress;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate IP Address
	 *
	 * @access	public
	 * @param	string
	 * @param	string	ipv4 or ipv6
	 * @return	bool
	 */
	public static function ValidIP($sIP, $sWhich = '') {
		$sWhich = strtolower($sWhich);

		// First check if filter_var is available
		if (is_callable('filter_var')) {
			switch ($sWhich) {
				case 'ipv4':
					$sFlag = FILTER_FLAG_IPV4;
					break;
				case 'ipv6':
					$sFlag = FILTER_FLAG_IPV6;
					break;
				default:
					$sFlag = '';
					break;
			}

			return (bool) filter_var($sIP, FILTER_VALIDATE_IP, $sFlag);
		}

		if ($sWhich !== 'ipv6' && $sWhich !== 'ipv4') {
			if (strpos($sIP, ':') !== false) {
				$sWhich = 'ipv6';
			} elseif (strpos($sIP, '.') !== false) {
				$sWhich = 'ipv4';
			} else {
				return false;
			}
		}

		$sFunc = '_valid_'.$sWhich;
		return self::$sFunc($sIP);
	}

	// --------------------------------------------------------------------

	/**
	 * Validate IPv4 Address
	 *
	 * Updated version suggested by Geert De Deckere
	 *
	 * @access	protected
	 * @param	string
	 * @return	bool
	 */
	protected static function _ValidIPv4($sIP) {
		$arIPSegments = explode('.', $sIP);

		// Always 4 segments needed
		if (count($arIPSegments) !== 4) {
			return false;
		}
		// IP can not start with 0
		if ($arIPSegments[0][0] == '0') {
			return false;
		}

		// Check each segment
		foreach ($arIPSegments as $sSegment) {
			// IP segments must be digits and can not be
			// longer than 3 digits or greater then 255
			if ($sSegment == '' || preg_match("/[^0-9]/", $sSegment) || $sSegment > 255 || strlen($sSegment) > 3) {
				return false;
			}
		}

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate IPv6 Address
	 *
	 * @access	protected
	 * @param	string
	 * @return	bool
	 */
	protected static function _ValidIPv6($sStr) {
		// 8 groups, separated by :
		// 0-ffff per group
		// one set of consecutive 0 groups can be collapsed to ::

		$nGroups = 8;
		$bIsCollapsed = false;

		$arChunks = array_filter(
			preg_split('/(:{1,2})/', $sStr, 0, PREG_SPLIT_DELIM_CAPTURE)
		);

		// Rule out easy nonsense
		if (current($arChunks) == ':' || end($arChunks) == ':') {
			return false;
		}

		// PHP supports IPv4-mapped IPv6 addresses, so we'll expect those as well
		if (strpos(end($arChunks), '.') !== false) {
			$ipv4 = array_pop($arChunks);

			if (!self::_ValidIPv4($ipv4)) {
				return false;
			}

			$nGroups--;
		}

		while ($seg = array_pop($arChunks)) {
			if ($seg[0] == ':') {
				if (--$nGroups == 0) {
					return false;	// too many groups
				}

				if (strlen($seg) > 2) {
					return false;	// long separator
				}

				if ($seg == '::') {
					if ($bIsCollapsed) {
						return false;	// multiple collapsed
					}

					$bIsCollapsed = true;
				}
			} elseif (preg_match("/[^0-9a-f]/i", $seg) || strlen($seg) > 4) {
				return false; // invalid segment
			}
		}

		return $bIsCollapsed || $nGroups == 1;
	}

	// --------------------------------------------------------------------

	/**
	 * User Agent
	 *
	 * @access	public
	 * @return	string
	 */
	public static function UserAgent() {
		if (self::$UserAgent !== false) {
			return self::$UserAgent;
		}

		self::$UserAgent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? false : $_SERVER['HTTP_USER_AGENT'];

		return self::$UserAgent;
	}

	// --------------------------------------------------------------------

	/**
	 * Sanitize Globals
	 *
	 * This function does the following:
	 *
	 * Unsets $_GET data (if query strings are not enabled)
	 *
	 * Unsets all globals if register_globals is enabled
	 *
	 * Standardizes newline characters to \n
	 *
	 * @access	private
	 * @return	void
	 */
	public static function _SanitizeGlobals() {
		// It would be "wrong" to unset any of these GLOBALS.
		$arProtected = array('_SERVER', '_GET', '_POST', '_FILES', '_REQUEST',
							'_SESSION', '_ENV', 'GLOBALS', 'HTTP_RAW_POST_DATA',
							'system_folder', 'application_folder', 'BM', 'EXT',
							'CFG', 'URI', 'RTR', 'OUT', 'IN');

		// Unset globals for securiy.
		// This is effectively the same as register_globals = off
		foreach (array($_GET, $_POST, $_COOKIE) as $vGlobal) {
			if (!is_array($vGlobal)) {
				if (!in_array($vGlobal, $arProtected)) {
					global $vGlobal;
					$vGlobal = null;
				}
			} else {
				foreach ($vGlobal as $sKey => $vVal) {
					if (!in_array($sKey, $arProtected)) {
						global $$sKey;
						$$sKey = null;
					}
				}
			}
		}

		// Is $_GET data allowed? If not we'll set the $_GET to an empty array
		if (self::$_AllowGetArray== false) {
			$_GET = array();
		} else {
			if (is_array($_GET) && count($_GET) > 0) {
				foreach ($_GET as $sKey => $vVal) {
					$_GET[self::_CleanInputKeys($sKey)] = self::_CleanInputData($vVal);
				}
			}
		}

		// Clean $_POST Data
		if (is_array($_POST) && count($_POST) > 0) {
			foreach ($_POST as $sKey => $vVal) {
				$_POST[self::_CleanInputKeys($sKey)] = self::_CleanInputData($vVal);
			}
		}

		// Clean $_COOKIE Data
		if (is_array($_COOKIE) && count($_COOKIE) > 0) {
			// Also get rid of specially treated cookies that might be set by a server
			// or silly application, that are of no use to a CI application anyway
			// but that when present will trip our 'Disallowed Key Characters' alarm
			// http://www.ietf.org/rfc/rfc2109.txt
			// note that the key names below are single quoted strings, and are not PHP variables
			unset($_COOKIE['$Version']);
			unset($_COOKIE['$Path']);
			unset($_COOKIE['$Domain']);

			foreach ($_COOKIE as $sKey => $vVal) {
				$_COOKIE[self::_CleanInputKeys($sKey)] = self::_CleanInputData($vVal);
			}
		}

		// Sanitize PHP_SELF
		$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);


		// CSRF Protection check on HTTP requests
		if (self::$_EnableCSRF == true && !self::IsCliRequest()) {
            CSecurity::CSRFVerify();
		}

		log_message('debug', "Global POST and COOKIE data sanitized");
	}

	// --------------------------------------------------------------------

	/**
	 * Clean Input Data
	 *
	 * This is a helper function. It escapes data and
	 * standardizes newline characters to \n
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	public static function _CleanInputData($sStr) {
		if (is_array($sStr)) {
			$arNewArray = array();
			foreach ($sStr as $sKey => $vVal) {
				$arNewArray[self::_CleanInputKeys($sKey)] = self::_CleanInputData($vVal);
			}
			return $arNewArray;
		}

		/* We strip slashes if magic quotes is on to keep things consistent

		NOTE: In PHP 5.4 get_magic_quotes_gpc() will always return 0 and
		it will probably not exist in future versions at all.
		 */
		if (!is_php('5.4') && get_magic_quotes_gpc()) {
			$sStr = stripslashes($sStr);
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === true) {
			$sStr = CUtf8::CleanString($sStr);
		}

		// Remove control characters
		$sStr = remove_invisible_characters($sStr);

		// Should we filter the input data?
		if (self::$_EnableXSS=== true) {
			$sStr = CSecurity::XSSClean($sStr);
		}

		// Standardize newlines if needed
		if (self::$_StandardizeNewlines == true) {
			if (strpos($sStr, "\r") !== false) {
				$sStr = str_replace(array("\r\n", "\r", "\r\n\n"), PHP_EOL, $sStr);
			}
		}

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Clean Keys
	 *
	 * This is a helper function. To prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	public static function _CleanInputKeys($vValue) {
        if(empty($vValue)) {
            return $vValue;
        }

		if (!preg_match("/^[\$a-z0-9:\s._\/-]+$/i", $vValue)) {
			exit('Disallowed Key Characters.');
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === true) {
			$vValue = CUtf8::CleanString($vValue);
		}
        
		//if(function_exists('mysql_escape_string')){
		//    mysql_escape_string($sStr);
		//} else
		//if (Application::$DB_INITALIZED) {
		//    CRecordset::EscapeString($vValue);
		//}
		

		return $vValue;
	}

	// --------------------------------------------------------------------

	/**
	 * Request Headers
	 *
	 * In Apache, you can simply call apache_request_headers(), however for
	 * people running other webservers the function is undefined.
	 *
	 * @param	bool XSS cleaning
	 *
	 * @return array
	 */
	public static function RequestHeaders($bXXSClean = false) {
		// Look at Apache go!
		if (function_exists('apache_request_headers')) {
			$arHeaders = apache_request_headers();
		} else {
			$arHeaders['Content-Type'] = (isset($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');

			foreach ($_SERVER as $sKey => $vVal) {
				if (strncmp($sKey, 'HTTP_', 5) === 0) {
					$arHeaders[substr($sKey, 5)] = self::_FetchFromArray($_SERVER, $sKey, $bXXSClean);
				}
			}
		}

		// take SOME_HEADER and turn it into Some-Header
		foreach ($arHeaders as $sKey => $vVal) {
			$sKey = str_replace('_', ' ', strtolower($sKey));
			$sKey = str_replace(' ', '-', ucwords($sKey));

			self::$Headers[$sKey] = $vVal;
		}

		return self::$Headers;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 *
	 * @param 	string		array key for $this->headers
	 * @param	boolean		XSS Clean or not
	 * @return 	mixed		FALSE on failure, string on success
	 */
	public static function GetRequestHeader($nIndex, $bXXSClean = false) {
		if (empty(self::$Headers)) {
			self::RequestHeaders();
		}

		if (!isset(self::$Headers[$nIndex])) {
			return false;
		}

		if ($bXXSClean === true) {
			return CSecurity::XSSClean(self::$Headers[$nIndex]);
		}

		return self::$Headers[$nIndex];
	}

	// --------------------------------------------------------------------

	/**
	 * Is ajax Request?
	 *
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
	 *
	 * @return 	boolean
	 */
	public static function IsAJAXRequest() {
		return (self::Server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
	}

	// --------------------------------------------------------------------

	/**
	 * Is cli Request?
	 *
	 * Test to see if a request was made from the command line
	 *
	 * @return 	bool
	 */
	public static function IsCliRequest() {
		return (php_sapi_name() === 'cli' || defined('STDIN'));
	}

}

/* End of file Input.php */
/* Location: ./system/core/Input.php */