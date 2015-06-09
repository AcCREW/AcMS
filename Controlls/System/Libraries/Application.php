<?php

require_once("./Controlls/Shared/Definitions.php");
require_once("./Controlls/Shared/Common.php");
require_once("./Controlls/System/Libraries/Loader.php");

/**
 * @property Application $_this instance
 * @property string $Title Module title
 * @property array $Class Stores all loaded classes
 * @property array $JSON Stores all loaded JSONs
 * @property array $Config Stores all configs
 * @property string $DumpContent Stores all dumps and print them in content
 */
class Application {
    const _ACTION_INITIALIZE_ACDB = 0;
    const _ACTION_LOAD_MODULE = 1000;
    const _ACTION_SUBMIT = 1001;
    const _ACTION_LOGOUT = -1;
    
    static $DB_INITALIZED = false;
    
    static $IsLogged = false;
    static $User = null;
    static $AuthDB = null;
    static $CMSDB = null;
    
    static $_this = null;
    static $Title = null;
    static $Template = null;
    static $TemplateDir = null;
	
    static $DumpContent = '';
    
    static $Config = array();
    
    public $Action = self::_ACTION_INITIALIZE_ACDB;
    
    private $PreloadedJSs = null;
    private $PreloadedJSSchemes = null;
    private $PreloadedCSSs = array();

    public function Application() {
        self::$_this = &$this;
        $this->Initialize();
    }

	public static function GetModuleContent($sModule, $sBox = '_RightBox', &$arProperties = array()) {
		if(($Error = Loader::LoadModule($sModule)) instanceof Error) {
			show_error($Error->Message);
		}
		$Module = new $sModule();
		Loader::LoadJSON($sModule, MODULES, $Module);
		$sContent = call_user_func_array(array(&$Module, DEFAULT_FUNCTION), array());
		$sTitle = $sModule;
		if(property_exists($Module, '_Title')) {
			$sTitle = $Module->_Title;
		}		
		if(method_exists($Module, 'GetProperties')) {
			$arProperties = (sizeof($arProperties) > 0) ? array_merge($arProperties, $Module->GetProperties()) : $Module->GetProperties();
		}
		$arData = array(
			'BoxContent' => $sContent,
			'BoxTitle' => $sTitle
		);
		return CParser::Parse($sBox, self::$TemplateDir, $arData);
	}
	
    public function Start() {
        switch ($this->Action) {
			case self::_ACTION_LOGOUT:
				CSession::SessDestroy();
				header("Location: ./");
				break;
            case self::_ACTION_SUBMIT:
                $sModule = CInput::Post('Module');
                $Module = new $sModule();
                $sFunction = 'OnSubmit';
                if(!method_exists($Module, $sFunction)) {
                    show_error("Function '".$sFunction."' doesn't exists in class '".$sModule."'.");
                }
                call_user_func_array(array(&$Module, $sFunction), array());
                $SubmitCallbackArguments = new SubmitCallbackArguments();
                if(method_exists($Module, 'GetProperties')) {
					$SubmitCallbackArguments->UpdateJS = $Module->GetProperties();
				}
                if(property_exists($Module, 'Location') && !empty($Module->Location) && $Module->Location != 'null') {
					$SubmitCallbackArguments->Location = $Module->Location;
				}
				if(!empty(self::$DumpContent)) {
					$SubmitCallbackArguments->Dump = self::$DumpContent;
				}
				//$SubmitCallbackArguments->_Type = 'SubmitCallbackArguments';
				//if(!is_array($vReturn)) {
				//    $SubmitCallbackArguments->Message = $vReturn->Message;
				//} else {
				//    foreach($vReturn as $sKey => $vValue) {
				//        $SubmitCallbackArguments->$sKey = $vValue;
				//    }
				//}
                header('Content-Type: application/json');
                exit (json_encode($SubmitCallbackArguments));
            case self::_ACTION_INITIALIZE_ACDB:
                #region - Load Template - 
				Loader::LoadJSON(self::$Template, TEMPLATES);
				
				$arProperties = array();
                
                $sLeftContent = $this->GetModuleContent('Membership', '_LeftBox', $arProperties);
                $sLeftContent .= $this->GetModuleContent('RealmStatus', '_LeftBox', $arProperties);
				
                $arData = array();
                $arData['Core'] = 'Ac0';
                $arData['ModuleTitle'] = Application::$Title;
                $arData['SiteTitle'] = Application::GetConfig('SITE_TITLE');
                $arData['RightContent'] = '';
                $arData['LeftContent'] = $sLeftContent;
                $arData['IsLogged'] = Application::$IsLogged ? array(array()) : array();
                $arData['IsNotLogged'] = Application::$IsLogged ? array() : array(array());
                $arData['PreloadedJS'] = json_encode($this->PreloadedJSs);
                $arData['PreloadedJSScheme'] = json_encode($this->PreloadedJSSchemes);
                $arData['PreloadedCSS'] = $this->PreloadedCSSs;
                //$arData['UpdateJS'] = $arProperties;
                echo CParser::Parse('_Main', self::$TemplateDir, $arData);
                #endregion
                break;
            case self::_ACTION_LOAD_MODULE:
                header('Content-Type: application/json');
                #region - Load Module - 
				$sModule = CInput::Get('Module');
				$sModule = $sModule !== false && !empty($sModule) ? $sModule : DEFAULT_CONTROLLER;
				$sFunction =  CInput::Get('Function');
				$sFunction = $sFunction !== false && !empty($sFunction) ? $sFunction : DEFAULT_FUNCTION;
                if(($Error = Loader::LoadModule($sModule)) instanceof Error) {
                    exit(json_encode($Error));
                }
                $Module = new $sModule();
                if(!method_exists($Module, $sFunction)) {
                    exit(json_encode(new Error("Function '".$sFunction."' doesn't exists in class '".$sModule."'.")));
                }
                $JSONObject = Loader::LoadJSON($sModule, MODULES);
                $sContent = call_user_func_array(array(&$Module, $sFunction), array());
				$sTitle = Application::$Title;
				if(empty($sTitle) && property_exists($JSONObject, 'Title')) {
					$sTitle = $JSONObject->Title;
				}
				if($sModule != 'Index') {
					$arData = array(
						'BoxContent' => $sContent,
						'BoxTitle' => $sTitle
					);
					$sContent = CParser::Parse('_RightBox', self::$TemplateDir, $arData);
                }
                $arData = array();

                $arData['ModuleTitle'] = $sTitle;
                $arData['SiteTitle'] = Application::GetConfig('SITE_TITLE');
                $arData['Module'] = $sModule;
                $arData['Function'] = $sFunction;
                $arData['RequireAngularJS'] = $JSONObject->RequireAngularJS;
				if(method_exists($Module, 'GetProperties')) {
					$arData['UpdateJS'] = $Module->GetProperties();
				}
				if(property_exists($Module, 'Location') && !empty($Module->Location) && $Module->Location != 'null') {
					$arData['Location'] = $Module->Location;
				}
				//Dump(CCheck::CompareTimes(APP_START));
				//Dump(CCheck::CompareMemories());
                $arData['Content'] = self::$DumpContent.$sContent;
                exit (json_encode($arData));
            #endregion
        }
    }
    
    private function Initialize() {
		self::$AuthDB = self::GetConfig('MYSQL_AUTH_DB');
        self::$CMSDB = self::GetConfig('MYSQL_DEFAULT_DB');
		
        $this->PreloadedJSs = new stdClass();
        $this->PreloadedJSSchemes = new stdClass();
        foreach(self::GetConfig('AUTOLOAD_LIBRARIES') as $sLibrarieName) {
            Loader::LoadLibrary($sLibrarieName, true);
        }        
        foreach(self::GetConfig('AUTOLOAD_HELPERS') as $sHelperName) {
            Loader::LoadHelper($sHelperName);
        }
		if(($nAction = CInput::PostGet('Action')) !== false) {
            $this->Action = $nAction;    
        }
		if($nAction == self::_ACTION_INITIALIZE_ACDB) {
			Loader::LoadJSON('', DEFAULT_CONTENT);
		}
		if(self::$IsLogged) {
			$nUserID = CSession::Get('UserID');
			if(empty($nUserID)){
				CSession::Set('IsLogged', false);
			} else {
				self::$User = new OUser($nUserID);
			}
		}
		
		self::$Template = self::GetConfig('TEMPLATE') !== false ? self::GetConfig('TEMPLATE') : DEFAULT_TEMPLATE;
		self::$TemplateDir = '../Templates/'.self::$Template;
    }

    public static function SetConfig($sKey, $vValue) {
        self::$Config[$sKey] = $vValue;
    }

    public static function GetConfig($sKey, $bStrict = false) {
        if(!isset(self::$Config[$sKey])) {
            if($bStrict) {
                show_error("Can't find config with key '".$sKey."'.");
            }
            return false;
        }
        return self::$Config[$sKey];
    }
    
    public function LoadJS($sKey, $sLink) {
        if(empty($sLink) || empty($sKey)) {
            return false;
        }
        
        $this->PreloadedJSs->$sKey = $sLink;
        return true;
    }
    
    public function LoadJSScheme($sKey, $sLink) {
        if(empty($sKey)) {
            return false;
        }
        
        $this->PreloadedJSSchemes->$sKey = $sLink;
        return true;
    }
    
    public function LoadCSS($vLink = null) {
        if(empty($vLink)) {
            return false;
        }
        
        if(!is_array($vLink)) {
            $vLink = array('Link' => $vLink);
        }
        
        $this->PreloadedCSSs = array_merge($this->PreloadedCSSs, array($vLink));
        return true;
    }
}

require_once(APPPATH."Config/Config.php");

function AcAutoLoader($sClass) {
    if(($Error = Loader::LoadObject($sClass, true)) instanceof Error) {
		if(($Error = Loader::LoadLibrary($sClass, true)) instanceof Error) {
			if(($Error = Loader::LoadModule($sClass, true)) instanceof Error) {
				show_error($Error->Message);
			}
		}
	}
}
spl_autoload_register('AcAutoLoader');
