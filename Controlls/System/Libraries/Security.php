<?php
/**
 * Security Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Security
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/security.html
 */
class CSecurity {

	/**
	 * Random Hash for protecting URLs
	 *
	 * @var string
	 * @access protected
	 */
	protected static $XSS_HASH			    = '';
	/**
	 * Random Hash for Cross Site Request Forgery Protection Cookie
	 *
	 * @var string
	 * @access protected
	 */
	protected static $CSRF_HASH             = '';
	/**
	 * Expiration time for Cross Site Request Forgery Protection Cookie
	 * Defaults to two hours (in seconds)
	 *
	 * @var int
	 * @access protected
	 */
	protected static $CSRF_EXPIRE           = 7200;
	/**
	 * Token name for Cross Site Request Forgery Protection Cookie
	 *
	 * @var string
	 * @access protected
	 */
	protected static $CSRF_TOKEN_NAME       = 'ac_csrf_token';
	/**
	 * Cookie name for Cross Site Request Forgery Protection Cookie
	 *
	 * @var string
	 * @access protected
	 */
	protected static $CSRF_COOKIE_NAME      = 'ac_csrf_token';
	/**
	 * List of never allowed strings
	 *
	 * @var array
	 * @access protected
	 */
	protected static $NEVER_ALLOWED_STR     = array(
		'document.cookie'	=> '[removed]',
		'document.write'	=> '[removed]',
		'.parentNode'		=> '[removed]',
		'.innerHTML'		=> '[removed]',
		'window.location'	=> '[removed]',
		'-moz-binding'		=> '[removed]',
		'<!--'				=> '&lt;!--',
		'-->'				=> '--&gt;',
		'<![CDATA['			=> '&lt;![CDATA[',
		'<comment>'			=> '&lt;comment&gt;'
	);

	/* never allowed, regex replacement */
	/**
	 * List of never allowed regex replacement
	 *
	 * @var array
	 * @access protected
	 */
	protected static $NEVER_ALLOWED_REGEX   = array(
		'javascript\s*:',
		'expression\s*(\(|&\#40;)', // CSS and IE
		'vbscript\s*:', // IE, surprise!
		'Redirect\s+302',
		"([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
	);

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public static function _Initialize() {
		// Is CSRF protection enabled?
		if (Application::GetConfig('CSRF_PROTECTION') === true) {
			// CSRF config
			foreach (array('CSRF_EXPIRE', 'CSRF_TOKEN_NAME', 'CSRF_COOKIE_NAME') as $key) {
				if (false !== ($val = Application::GetConfig($key))) {
					self::$$key = $val;
				}
			}

			// Append application specific cookie prefix
			if (($sCookiePrefix = Application::GetConfig('COOKIE_PREFIX'))) {
				self::$CSRF_COOKIE_NAME = $sCookiePrefix.self::$CSRF_COOKIE_NAME;
			}

			// Set the CSRF hash
			self::_CSRFSetHash();
		}

		log_message('debug', "Security Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Verify Cross Site Request Forgery Protection
	 *
	 * @return	void
	 */
	public static function CSRFVerify() {
		// If it's not a POST request we will set the CSRF cookie
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
			self::CSRFSetCookie();
            return;
		}
        
		// Do the tokens exist in both the _POST and _COOKIE arrays?
		if (!isset($_POST[self::$CSRF_TOKEN_NAME], $_COOKIE[self::$CSRF_COOKIE_NAME])) {
			self::CSRFShowError();
		}

		// Do the tokens match?
		if ($_POST[self::$CSRF_TOKEN_NAME] != $_COOKIE[self::$CSRF_COOKIE_NAME]) {
			self::CSRFShowError();
		}

		// We kill this since we're done and we don't want to
		// polute the _POST array
		unset($_POST[self::$CSRF_TOKEN_NAME]);

		// Nothing should last forever
		unset($_COOKIE[self::$CSRF_COOKIE_NAME]);
        self::_CSRFSetHash();
		self::CSRFSetCookie();

		log_message('debug', 'CSRF token verified');
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cross Site Request Forgery Protection Cookie
	 *
	 * @return	bool
	 */
	public static function CSRFSetCookie() {
		$nExpire = time() + self::$CSRF_EXPIRE;
		$bSecureCookie= (Application::GetConfig('COOKIE_SECURE') === true) ? 1 : 0;

		if ($bSecureCookie && (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) === 'off')) {
			return false;
		}

		setcookie(self::$CSRF_COOKIE_NAME, self::$CSRF_HASH, $nExpire, Application::GetConfig('COOKIE_PATH'), Application::GetConfig('COOKIE_DOMAIN'), (bool)$bSecureCookie);

		log_message('debug', "CRSF cookie Set");
        
        return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Show CSRF Error
	 *
	 * @return	void
	 */
	public static function CSRFShowError() {
		show_error('The action you have requested is not allowed.');
	}

	// --------------------------------------------------------------------

	/**
	 * Get CSRF Hash
	 *
	 * Getter Method
	 *
	 * @return 	string 	self::_csrf_hash
	 */
	public static function GetCSRFHash() {
		return self::$CSRF_HASH;
	}

	// --------------------------------------------------------------------

	/**
	 * Get CSRF Token Name
	 *
	 * Getter Method
	 *
	 * @return 	string 	self::csrf_token_name
	 */
	public static function GetCSRFTokenName() {
		return self::$CSRF_TOKEN_NAME;
	}

	// --------------------------------------------------------------------

	/**
	 * XSS Clean
	 *
	 * Sanitizes data so that Cross Site Scripting Hacks can be
	 * prevented.  This function does a fair amount of work but
	 * it is extremely thorough, designed to prevent even the
	 * most obscure XSS attempts.  Nothing is ever 100% foolproof,
	 * of course, but I haven't been able to get anything passed
	 * the filter.
	 *
	 * Note: This function should only be used to deal with data
	 * upon submission.  It's not something that should
	 * be used for general runtime processing.
	 *
	 * This function was based in part on some code and ideas I
	 * got from Bitflux: http://channel.bitflux.ch/wiki/XSS_Prevention
	 *
	 * To help develop this script I used this great list of
	 * vulnerabilities along with a few other hacks I've
	 * harvested from examining vulnerabilities in other programs:
	 * http://ha.ckers.org/xss.html
	 *
	 * @param	mixed	string or array
	 * @param 	bool
	 * @return	string
	 */
	public static function XSSClean($sStr, $bIsImage = false) {
		/*
		 * Is the string an array?
		 *
		 */
		if (is_array($sStr)) {
			while (list($sKey) = each($sStr)) {
				$sStr[$sKey] = self::XSSClean($sStr[$sKey]);
			}

			return $sStr;
		}

		/*
		 * Remove Invisible Characters
		 */
		$sStr = remove_invisible_characters($sStr);

		// Validate Entities in URLs
		$sStr = self::_ValidateEntities($sStr);

		/*
		 * URL Decode
		 *
		 * Just in case stuff like this is submitted:
		 *
		 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
		 *
		 * Note: Use rawurldecode() so it does not remove plus signs
		 *
		 */
		$sStr = rawurldecode($sStr);

		/*
		 * Convert character entities to ASCII
		 *
		 * This permits our tests below to work reliably.
		 * We only convert entities that are within tags since
		 * these are the ones that will pose security problems.
		 *
		 */

		$sStr = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si",'self::_ConvertAttribute', $sStr);

		$sStr = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", 'self::EntityDecode', $sStr);

		/*
		 * Remove Invisible Characters Again!
		 */
		$sStr = remove_invisible_characters($sStr);

		/*
		 * Convert all tabs to spaces
		 *
		 * This prevents strings like this: ja	vascript
		 * NOTE: we deal with spaces between characters later.
		 * NOTE: preg_replace was found to be amazingly slow here on
		 * large blocks of data, so we use str_replace.
		 */

		if (strpos($sStr, "\t") !== false) {
			$sStr = str_replace("\t", ' ', $sStr);
		}

		/*
		 * Capture converted string for later comparison
		 */
		$sConvertedString = $sStr;

		// Remove Strings that are never allowed
		$sStr = self::_DoNeverAllowed($sStr);

		/*
		 * Makes PHP tags safe
		 *
		 * Note: XML tags are inadvertently replaced too:
		 *
		 * <?xml
		 *
		 * But it doesn't seem to pose a problem.
		 */
		if ($bIsImage === true) {
			// Images have a tendency to have the PHP short opening and
			// closing tags every so often so we skip those and only
			// do the long opening tags.
			$sStr = preg_replace('/<\?(php)/i', "&lt;?\\1", $sStr);
		} else {
			$sStr = str_replace(array('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $sStr);
		}

		/*
		 * Compact any exploded words
		 *
		 * This corrects words like:  j a v a s c r i p t
		 * These words are compacted back to their correct state.
		 */
		$arWords = array(
			'javascript', 'expression', 'vbscript', 'script', 'base64',
			'applet', 'alert', 'document', 'write', 'cookie', 'window'
		);

		foreach ($arWords as $sWord) {
			$sTemp = '';

			for ($i = 0, $nWordLen = strlen($sWord); $i < $nWordLen; $i++) {
				$sTemp .= substr($sWord, $i, 1)."\s*";
			}

			// We only want to do this when it is followed by a non-word character
			// That way valid stuff like "dealer to" does not become "dealerto"
			$sStr = preg_replace_callback('#('.substr($sTemp, 0, -3).')(\W)#is', 'self::_CompactExplodedWords', $sStr);
		}

		/*
		 * Remove disallowed Javascript in links or img tags
		 * We used to do some version comparisons and use of stripos for PHP5,
		 * but it is dog slow compared to these simplified non-capturing
		 * preg_match(), especially if the pattern exists in the string
		 */
		do {
			$sOriginal = $sStr;

			if (preg_match("/<a/i", $sStr)) {
				$sStr = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", 'self::_JSLinkRemoval', $sStr);
			}

			if (preg_match("/<img/i", $sStr)) {
				$sStr = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", 'self::_JSImgRemoval', $sStr);
			}

			if (preg_match("/script/i", $sStr) || preg_match("/xss/i", $sStr)) {
				$sStr = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $sStr);
			}
		}
		while($sOriginal != $sStr);

		unset($sOriginal);

		// Remove evil attributes such as style, onclick and xmlns
		$sStr = self::_RemoveEvilAttributes($sStr, $bIsImage);

		/*
		 * Sanitize naughty HTML elements
		 *
		 * If a tag containing any of the words in the list
		 * below is found, the tag gets converted to entities.
		 *
		 * So this: <blink>
		 * Becomes: &lt;blink&gt;
		 */
		$sNaughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
		$sStr = preg_replace_callback('#<(/*\s*)('.$sNaughty.')([^><]*)([><]*)#is', 'self::_SanitizeNaughtyHTML', $sStr);

		/*
		 * Sanitize naughty scripting elements
		 *
		 * Similar to above, only instead of looking for
		 * tags it looks for PHP and JavaScript commands
		 * that are disallowed.  Rather than removing the
		 * code, it simply converts the parenthesis to entities
		 * rendering the code un-executable.
		 *
		 * For example:	eval('some code')
		 * Becomes:		eval&#40;'some code'&#41;
		 */
		$sStr = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $sStr);


		// Final clean up
		// This adds a bit of extra precaution in case
		// something got through the above filters
		$sStr = self::_DoNeverAllowed($sStr);

		/*
		 * Images are Handled in a Special Way
		 * - Essentially, we want to know that after all of the character
		 * conversion is done whether any unwanted, likely XSS, code was found.
		 * If not, we return TRUE, as the image is clean.
		 * However, if the string post-conversion does not matched the
		 * string post-removal of XSS, then it fails, as there was unwanted XSS
		 * code found and removed/changed during processing.
		 */

		if ($bIsImage === true) {
			return ($sStr == $sConvertedString) ? true : false;
		}

		log_message('debug', "XSS Filtering completed");
		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Random Hash for protecting URLs
	 *
	 * @return	string
	 */
	public static function XssHash() {
		if (self::$XSS_HASH == '') {
			mt_srand();
			self::$XSS_HASH = md5(time() + mt_rand(0, 1999999999));
		}

		return self::$XSS_HASH;
	}

	// --------------------------------------------------------------------

	/**
	 * HTML Entities Decode
	 *
	 * This function is a replacement for html_entity_decode()
	 *
	 * The reason we are not using html_entity_decode() by itself is because
	 * while it is not technically correct to leave out the semicolon
	 * at the end of an entity most browsers will still interpret the entity
	 * correctly.  html_entity_decode() does not convert entities without
	 * semicolons, so we are left with our own little solution here. Bummer.
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function EntityDecode($sStr, $sCharset='UTF-8') {
		if(is_array($sStr)) {
			$sStr = implode('', $sStr);
		}
		if (stristr($sStr, '&') === false) {
			return $sStr;
		}

		$sStr = html_entity_decode($sStr, ENT_COMPAT, $sCharset);
		$sStr = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $sStr);
		return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $sStr);
	}

	// --------------------------------------------------------------------

	/**
	 * Filename Security
	 *
	 * @param	string
	 * @param 	bool
	 * @return	string
	 */
	public static function SanitizeFilename($sStr, $sRelativePath = false) {
		$arBad = array(
			"../",
			"<!--",
			"-->",
			"<",
			">",
			"'",
			'"',
			'&',
			'$',
			'#',
			'{',
			'}',
			'[',
			']',
			'=',
			';',
			'?',
			"%20",
			"%22",
			"%3c",		// <
			"%253c",	// <
			"%3e",		// >
			"%0e",		// >
			"%28",		// (
			"%29",		// )
			"%2528",	// (
			"%26",		// &
			"%24",		// $
			"%3f",		// ?
			"%3b",		// ;
			"%3d"		// =
		);

		if (!$sRelativePath) {
			$bad[] = './';
			$bad[] = '/';
		}

		$sStr = remove_invisible_characters($sStr, false);
		return stripslashes(str_replace($arBad, '', $sStr));
	}

	// ----------------------------------------------------------------

	/**
	 * Compact Exploded Words
	 *
	 * Callback function for xss_clean() to remove whitespace from
	 * things like j a v a s c r i p t
	 *
	 * @param	array
	 * @return	string
	 */
	protected static function _CompactExplodedWords($arMatches) {
		return preg_replace('/\s+/s', '', $arMatches[1]).$arMatches[2];
	}

	// --------------------------------------------------------------------

	/*
	 * Remove Evil HTML Attributes (like evenhandlers and style)
	 *
	 * It removes the evil attribute and either:
	 * 	- Everything up until a space
	 *		For example, everything between the pipes:
	 *		<a |style=document.write('hello');alert('world');| class=link>
	 * 	- Everything inside the quotes
	 *		For example, everything between the pipes:
	 *		<a |style="document.write('hello'); alert('world');"| class="link">
	 *
	 * @param string $sStr The string to check
	 * @param boolean $is_image TRUE if this is an image
	 * @return string The string with the evil attributes removed
	 */
	protected static function _RemoveEvilAttributes($sStr, $bIsImage) {
		// All javascript event handlers (e.g. onload, onclick, onmouseover), style, and xmlns
		$arEvilAttributes = array('on\w*', 'style', 'xmlns', 'formaction');

		if ($bIsImage === true) {
			/*
			 * Adobe Photoshop puts XML metadata into JFIF images, 
			 * including namespacing, so we have to allow this for images.
			 */
			unset($arEvilAttributes[array_search('xmlns', $arEvilAttributes)]);
		}

		do {
			$nCount = 0;
			$arAttribs = array();

			// find occurrences of illegal attribute strings without quotes
			preg_match_all('/('.implode('|', $arEvilAttributes).')\s*=\s*([^\s>]*)/is', $sStr, $arMatches, PREG_SET_ORDER);

			foreach ($arMatches as $attr) {
				$arAttribs[] = preg_quote($attr[0], '/');
			}

			// find occurrences of illegal attribute strings with quotes (042 and 047 are octal quotes)
			preg_match_all("/(".implode('|', $arEvilAttributes).")\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is",  $sStr, $arMatches, PREG_SET_ORDER);

			foreach ($arMatches as $attr) {
				$arAttribs[] = preg_quote($attr[0], '/');
			}

			// replace illegal attribute strings that are inside an html tag
			if (count($arAttribs) > 0) {
				$sStr = preg_replace("/<(\/?[^><]+?)([^A-Za-z<>\-])(.*?)(".implode('|', $arAttribs).")(.*?)([\s><])([><]*)/i", '<$1 $3$5$6$7', $sStr, -1, $nCount);
			}

		} while ($nCount);

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Sanitize Naughty HTML
	 *
	 * Callback function for xss_clean() to remove naughty HTML elements
	 *
	 * @param	array
	 * @return	string
	 */
	protected static function _SanitizeNaughtyHTML($arMatches) {
		// encode opening brace
		$sStr = '&lt;'.$arMatches[1].$arMatches[2].$arMatches[3];

		// encode captured opening or closing brace to prevent recursive vectors
		$sStr .= str_replace(array('>', '<'), array('&gt;', '&lt;'),
							$arMatches[4]);

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * JS Link Removal
	 *
	 * Callback function for xss_clean() to sanitize links
	 * This limits the PCRE backtracks, making it more performance friendly
	 * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
	 * PHP 5.2+ on link-heavy strings
	 *
	 * @param	array
	 * @return	string
	 */
	protected static function _JSLinkRemoval($arMatch) {
		return str_replace(
			$arMatch[1],
			preg_replace(
				'#href=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
				'',
				self::FilterAttributes(str_replace(array('<', '>'), '', $arMatch[1]))
			),
			$arMatch[0]
		);
	}

	// --------------------------------------------------------------------

	/**
	 * JS Image Removal
	 *
	 * Callback function for xss_clean() to sanitize image tags
	 * This limits the PCRE backtracks, making it more performance friendly
	 * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
	 * PHP 5.2+ on image tag heavy strings
	 *
	 * @param	array
	 * @return	string
	 */
	protected static function _JSImgRemoval($arMatch) {
		return str_replace(
			$arMatch[1],
			preg_replace(
				'#src=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
				'',
				self::FilterAttributes(str_replace(array('<', '>'), '', $arMatch[1]))
			),
			$arMatch[0]
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Attribute Conversion
	 *
	 * Used as a callback for XSS Clean
	 *
	 * @param	array
	 * @return	string
	 */
	protected static function _ConvertAttribute($arMatch) {
		return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $arMatch[0]);
	}

	// --------------------------------------------------------------------

	/**
	 * Filter Attributes
	 *
	 * Filters tag attributes for consistency and safety
	 *
	 * @param	string
	 * @return	string
	 */
	protected static function _FilterAttributes($sStr) {
		$sOut = '';

		if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $sStr, $arMatches)) {
			foreach ($arMatches[0] as $sMatch) {
				$sOut .= preg_replace("#/\*.*?\*/#s", '', $sMatch);
			}
		}

		return $sOut;
	}

	// --------------------------------------------------------------------

	/**
	 * HTML Entity Decode Callback
	 *
	 * Used as a callback for XSS Clean
	 *
	 * @param	array
	 * @return	string
	 */
	protected static function _decode_entity($arMatch) {
		return self::EntityDecode($arMatch[0], strtoupper(Application::GetConfig('CHARSET')));
	}

	// --------------------------------------------------------------------

	/**
	 * Validate URL entities
	 *
	 * Called by xss_clean()
	 *
	 * @param 	string
	 * @return 	string
	 */
	protected static function _ValidateEntities($sStr) {
		/*
		 * Protect GET variables in URLs
		 */

		 // 901119URL5918AMP18930PROTECT8198

		$sStr = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', self::XssHash()."\\1=\\2", $sStr);

		/*
		 * Validate standard character entities
		 *
		 * Add a semicolon if missing.  We do this to enable
		 * the conversion of entities to ASCII later.
		 *
		 */
		$sStr = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $sStr);

		/*
		 * Validate UTF16 two byte encoding (x00)
		 *
		 * Just as above, adds a semicolon if missing.
		 *
		 */
		$sStr = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$sStr);

		/*
		 * Un-Protect GET variables in URLs
		 */
		$sStr = str_replace(self::XssHash(), '&', $sStr);

		return $sStr;
	}

	// ----------------------------------------------------------------------

	/**
	 * Do Never Allowed
	 *
	 * A utility function for xss_clean()
	 *
	 * @param 	string
	 * @return 	string
	 */
	protected static function _DoNeverAllowed($sStr) {
		$sStr = str_replace(array_keys(self::$NEVER_ALLOWED_STR), self::$NEVER_ALLOWED_STR, $sStr);

		foreach (self::$NEVER_ALLOWED_REGEX as $sRegex) {
			$sStr = preg_replace('#'.$sRegex.'#is', '[removed]', $sStr);
		}

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cross Site Request Forgery Protection Cookie
	 *
	 * @return	string
	 */
	protected static function _CSRFSetHash() {
		if (self::$CSRF_HASH == '') {
			// If the cookie exists we will use it's value.
			// We don't necessarily want to regenerate it with
			// each page load since a page could contain embedded
			// sub-pages causing this feature to fail
			if (isset($_COOKIE[self::$CSRF_COOKIE_NAME]) &&
				preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[self::$CSRF_COOKIE_NAME]) === 1) {
				return self::$CSRF_HASH = $_COOKIE[self::$CSRF_COOKIE_NAME];
			}

			return self::$CSRF_HASH = md5(uniqid(rand(), true));
		}

		return self::$CSRF_HASH;
	}

}

/* End of file Security.php */
/* Location: ./system/libraries/Security.php */