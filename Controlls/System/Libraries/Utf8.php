<?php
/**
 * Utf8 Class
 *
 * Provides support for UTF-8 environments
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	UTF-8
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/utf8.html
 */
class CUtf8 {

	/**
	 * Constructor
	 * Determines if UTF-8 support is to be enabled
	 */
	public static function _Initialize() {
		log_message('debug', "Utf8 Class Initialized");

		if (
			preg_match('/./u', 'é') === 1					// PCRE must support UTF-8
			&& function_exists('iconv')					// iconv must be installed
			&& ini_get('mbstring.func_overload') != 1		// Multibyte string function overloading cannot be enabled
			&& Application::GetConfig('CHARSET') == 'UTF-8'			// Application charset must be UTF-8
			) {
			log_message('debug', "UTF-8 Support Enabled");

			define('UTF8_ENABLED', true);

			// set internal encoding for multibyte string functions if necessary
			// and set a flag so we don't have to repeatedly use extension_loaded()
			// or function_exists()
			if (extension_loaded('mbstring')) {
				define('MB_ENABLED', true);
				mb_internal_encoding('UTF-8');
			} else {
				define('MB_ENABLED', false);
			}
		} else {
			log_message('debug', "UTF-8 Support Disabled");
			define('UTF8_ENABLED', false);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Clean UTF-8 strings
	 *
	 * Ensures strings are UTF-8
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function CleanString($sStr) {
		if (self::_IsAscii($sStr) === false) 	{
			$sStr = @iconv('UTF-8', 'UTF-8//IGNORE', $sStr);
		}

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Remove ASCII control characters
	 *
	 * Removes all ASCII control characters except horizontal tabs,
	 * line feeds, and carriage returns, as all others can cause
	 * problems in XML
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function SafeAsciiForXML($sStr) {
		return remove_invisible_characters($sStr, false);
	}

	// --------------------------------------------------------------------

	/**
	 * Convert to UTF-8
	 *
	 * Attempts to convert a string to UTF-8
	 *
	 * @access	public
	 * @param	string
	 * @param	string	- input encoding
	 * @return	string
	 */
	public static function ConvertToUTF8($sStr, $sEncoding) {
		if (function_exists('iconv')) {
			$sStr = @iconv($sEncoding, 'UTF-8', $sStr);
		} elseif (function_exists('mb_convert_encoding')) {
			$sStr = @mb_convert_encoding($sStr, 'UTF-8', $sEncoding);
		} else {
			return false;
		}

		return $sStr;
	}

	// --------------------------------------------------------------------

	/**
	 * Is ASCII?
	 *
	 * Tests if a string is standard 7-bit ASCII or not
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public static function _IsAscii($sStr) {
		return (preg_match('/[^\x00-\x7F]/S', $sStr) == 0);
	}

	// --------------------------------------------------------------------

}
// End Utf8 Class

/* End of file Utf8.php */