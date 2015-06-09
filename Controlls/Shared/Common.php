<?php

class Error {
    public $Message = null;
    public $Code = null;
    public $__Type = 'Error';
    
    public function Error($sMessage, $sCode = null) {
        $this->Message = $sMessage;
        $this->Code = $sCode;
    }
}

class SubmitCallbackArguments extends stdClass {
    public function SubmitCallbackArguments($arData = array()) {
    }
}

/**
 * Remove Invisible Characters
 *
 * This prevents sandwiching null characters
 * between ascii characters, like Java\0script.
 *
 * @access	public
 * @param	string
 * @return	string
 */
if (!function_exists('remove_invisible_characters')) {
	function remove_invisible_characters($str, $url_encoded = true) {
		$non_displayables = array();
		
		// every control character except newline (dec 10)
		// carriage return (dec 13), and horizontal tab (dec 09)
		
		if ($url_encoded) {
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}
		
		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		} while ($count);

		return $str;
	}
}
// ------------------------------------------------------------------------

/**
 * Determines if the current version of PHP is greater then the supplied value
 *
 * Since there are a few places where we conditionally test for PHP > 5
 * we'll set a static variable.
 *
 * @access	public
 * @param	string
 * @return	bool	TRUE if the current version is $version or higher
 */
if (!function_exists('is_php')) {
	function is_php($version = '5.0.0') {
		static $_is_php;
		$version = (string)$version;

		if ( ! isset($_is_php[$version])) {
			$_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? false : true;
		}

		return $_is_php[$version];
	}
}

if (!function_exists('log_message')) {
	function log_message($sMode, $sMessage) {
        return;
        var_dump($sMode);
        var_dump($sMessage);
    }
}

if (!function_exists('show_error')) {
	function show_error($sMessage) {
        trigger_error($sMessage);
        exit;
    }
}
if (!function_exists('Dump')) {
	function Dump($oObject, $bDump = false) {
        ob_start();
        echo '<pre id="dump" style="margin: 0;margin-top: 5px; font-size: 11px; color: #000077; font-weight: normal; text-transform: none; text-align: left;"><span style="color:#FF0000; font-weight:bold">&lt;Dump&gt;</span>'."\n";
        if($bDump){
            var_dump($oObject);
        }else{
            print_r($oObject);
        }
        echo '<br /><span style="color:#FF0000; font-weight:bold">&lt;/Dump&gt;</span></pre>';
        $sContent = ob_get_contents();
        ob_end_clean();
		
		if(Application::$_this->Action != Application::_ACTION_INITIALIZE_ACDB) {
			Application::$DumpContent .= $sContent;
		} else {
			echo $sContent;
		}
        
    }
}
if (!function_exists('ConvertBytesToString')) {
    function ConvertBytesToString($vSize) {
        $arUnits = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($vSize/pow(1024,($i=floor(log($vSize,1024)))),2).' '.$arUnits[$i];
    }
}