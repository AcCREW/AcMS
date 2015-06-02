<?php
/**
 * Parser Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Parser
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/parser.html
 */
class CParser {

	public static $L_DELIM = '{';
	public static $R_DELIM = '}';

	/**
	 *  Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template view,
	 * replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	array|AcObject|CRecordset
	 * @param	bool
	 * @return	string|Error
	 */
	public static function Parse($sTemplateName, $sModuleName, $arData = array(), $bAngularParse = false) {
		$sTemplate = Application::GetConfig('TEMPLATE');
		if(strpos($sModuleName, TEMPLATES) === false && file_exists(APPPATH.TEMPLATES.'/'.$sTemplate.'/'.VIEWS.'/'.$sModuleName.'/'.$sTemplateName.EXT)) {
			$sFile = APPPATH.TEMPLATES.'/'.$sTemplate.'/'.VIEWS.'/'.$sModuleName.'/'.$sTemplateName.EXT;
		} else {
			$sFile = APPPATH.MODULES.'/'.$sModuleName.'/'.VIEWS.'/'.$sTemplateName.EXT;
		}
		$sTemplate = Loader::LoadTemplate($sFile);
		if($sTemplate instanceof Error) {
			return $sTemplate;
		}
		//$sCurrentLDelim = null;
		//$sCurrentRDelim = null;
		
		//if($bAngularParse) {
		//    $sCurrentLDelim = self::$L_DELIM;
		//    $sCurrentRDelim = self::$R_DELIM;
		//    self::$L_DELIM = '{{_this.';
		//    self::$R_DELIM = '}}';
		//}

		$sReturnValue = self::_Parse($sTemplate, $arData);
		
		//if($bAngularParse) {
		//    self::$L_DELIM = $sCurrentLDelim;
		//    self::$R_DELIM = $sCurrentRDelim;
		//}
		
		return $sReturnValue;
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse a String
	 *
	 * Parses pseudo-variables contained in the specified string,
	 * replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	public static function ParseString($sTemplateName, $arData) {
		return self::_Parse($sTemplateName, $arData);
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template,
	 * replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	string
	 */
	public static function _Parse($sTemplate, $arData) {
		if ($sTemplate == '') {
			return false;
		}
        
        if($arData instanceof CRecordset){
            $arData = $arData->RowData;
        } elseif($arData instanceof AcObject) {
			$arData = $arData->arData;
		}
		
        $arData['UserIP'] = $_SERVER['REMOTE_ADDR'];
        $arData['BaseURL'] = ACPATH;
        $arData['CSRF_TOKEN_NAME'] = CSecurity::GetCSRFTokenName();
        $arData['CSRF_TOKEN_VALUE'] = CSecurity::GetCSRFHash();
		foreach ($arData as $sKey => $vVal) {
			if($vVal instanceof CRecordset){
				$vVal = $vVal->RowData;
			} elseif($vVal instanceof AcObject) {
				$vVal = $vVal->arData;
			}
			if (is_array($vVal)) {
				$sTemplate = self::_ParsePair($sKey, $vVal, $sTemplate);
			} else {
				$sTemplate = self::_ParseSingle($sKey, (string)$vVal, $sTemplate);
			}
		}

		return $sTemplate;
	}

	// --------------------------------------------------------------------

	/**
	 *  Set the left/right variable delimiters
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	public static function SetDelimiters($sL = '{', $sR = '}') {
		self::$L_DELIM = $sL;
		self::$R_DELIM = $sR;
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse a single key/value
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function _ParseSingle($sKey, $vVal, $sString) {
		return str_replace(self::$L_DELIM.$sKey.self::$R_DELIM, $vVal, $sString);
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse a tag pair
	 *
	 * Parses tag pairs:  {some_tag} string... {/some_tag}
	 *
	 * @access	private
	 * @param	string
	 * @param	array
	 * @param	string
	 * @return	string
	 */
	public static function _ParsePair($vVariable, $arData, $sString) {
		if (false === ($arMatch = self::_MatchPair($sString, $vVariable))) {
			return $sString;
		}

		$sStr = '';
		foreach ($arData as $sRow) {
			$sTemp = $arMatch['1'];
			foreach ($sRow as $sKey => $vVal) {
				if (!is_array($vVal)) {
					$sTemp = self::_ParseSingle($sKey, $vVal, $sTemp);
				} else {
					$sTemp = self::_ParsePair($sKey, $vVal, $sTemp);
				}
			}

			$sStr .= $sTemp;
		}

		return str_replace($arMatch['0'], $sStr, $sString);
	}

	// --------------------------------------------------------------------

	/**
	 *  Matches a variable pair
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	mixed
	 */
	public static function _MatchPair($sString, $vVariable) 	{
		if (!preg_match("|" . preg_quote(self::$L_DELIM) . $vVariable . preg_quote(self::$R_DELIM) . "(.+?)". preg_quote(self::$L_DELIM) . '/' . $vVariable . preg_quote(self::$R_DELIM) . "|s", $sString, $arMatch)) {
			return false;
		}

		return $arMatch;
	}

}
// END Parser Class

/* End of file Parser.php */
/* Location: ./system/libraries/Parser.php */
