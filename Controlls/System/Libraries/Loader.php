<?php

class Loader {
    static $JSON = array();
    static $OD = array();
    
    public static function LoadLibrary($sName, $bInitialize) {
        return self::Load($sName, LIBRARIES, $bInitialize);
    }
    
    public static function LoadModule($sName, $bInitialize = true) {
        return self::Load($sName, MODULES, $bInitialize);
    }
	
	public static function LoadObject($sName, $bInitialize = true) {
        return self::Load($sName, OBJECTS, $bInitialize);
    }

    public static function LoadHelper($sName) {
        return self::Load($sName, HELPERS);
    }
	
	/**
	 * Summary of LoadOD
	 * @param string $sClassName 
	 * @return Error|AcObjectDescriptor
	 */
	public static function LoadOD($sClassName) {
		if(mb_substr($sClassName, 0, 1, "UTF-8") == 'O' && ($sChar = mb_substr ($sClassName, 1, 1, "UTF-8")) != mb_strtolower($sChar, "UTF-8")) {
			$sClassName = mb_substr($sClassName, 1, mb_strlen($sClassName, "UTF-8"), "UTF-8");
		}
		
		$sPath = APPPATH.OBJECTS.'/'.$sClassName.'/'.$sClassName.'.'.OBJECT_DESCRIPTOR.EXT;
		if(isset(self::$OD[$sPath])) {
			return self::$OD[$sPath];
		}
		if(($vRetVal = self::LoadFile($sPath, true)) instanceof Error) {
			return $vRetVal;
		}
		
		$sClass = OBJECT_DESCRIPTOR.$sClassName;
		$OD = new $sClass();
		
		self::$OD[$sPath] = $OD;
		
		return $OD;
	}
    
    public static function LoadJSON($sNamePath, $sType = MODULES) {
        $arName = explode('/', $sNamePath);
        $sName = ucfirst(end($arName));
		$sIndex = $sType.'/'.$sName;
     
		if(isset(self::$JSON[$sIndex])) {
            $Object = self::$JSON[$sIndex];
        } else {
			$sPath = null;
			$sDir = null;
			if($sType == MODULES) {
				$sDir = APPPATH.MODULES.'/'.$sNamePath.'/';
				$sPath = $sDir.MODULE_JSON;
			} elseif($sType == TEMPLATES) {
				$sDir = APPPATH.TEMPLATES.'/'.$sNamePath.'/';
				$sPath = $sDir.TEMPLATE_JSON;
			} elseif($sType == OBJECTS) { 
				$sDir = APPPATH.OBJECTS.'/'.$sNamePath.'/';
				$sPath = $sDir.OBJECT_JSON;
			} elseif($sType == DEFAULT_CONTENT) {
				$sDir = CONTENTPATH;
				$sPath = $sDir.DEFAULT_JSON;
			} else {
				return new Error("Invalid request for JSON.");
			}
			
			if(($vRetVal = self::LoadFile($sPath)) instanceof Error) {
				return $vRetVal;
			}
			
			$Object = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $vRetVal));
			if(is_null($Object)) {
				return new Error("Invalid JSON file '".$sPath."'.");
			}
			$Object->Dir = $sDir;
			self::$JSON[$sIndex] = $Object;
		}
		if(in_array($sType, array(TEMPLATES, MODULES, DEFAULT_CONTENT))) {
			if(property_exists($Object, 'Enabled') && !$Object->Enabled) {
				show_error("The '".$sName."' (".$sType.") is not enabled.");
			}
			if(!property_exists($Object, 'RequireAngularJS')) {
				$Object->RequireAngularJS = false;
			}
			if(property_exists($Object, 'CSS')) {
				foreach($Object->CSS as $sLink) {
					Application::$_this->LoadCSS(ACPATH.$Object->Dir.$sLink);
				}
			}
			if(property_exists($Object, 'JS')) {
				foreach($Object->JS as $sKey => $sLink) {
					Application::$_this->LoadJS($sKey, ACPATH.$Object->Dir.$sLink);
				}
			}
			if(property_exists($Object, 'JSSchemes')) {
				foreach($Object->JSSchemes as $sKey => $sLink) {
					Application::$_this->LoadJSScheme($sKey, $sLink);
				}
			}
			if(empty(Application::$Title) && property_exists($Object, 'Title')) {
                Application::$Title = $Object->Title;
            }
		}
		
        return $Object; 
    }
    
    public static function LoadTemplate($sFile) {
        return self::LoadFile($sFile);
    }
    
    public static function LoadFile($sFile, $bRequre = false) {
        if(!file_exists($sFile)) {
            return new Error("Unable to load file '".$sFile."'.");
        }
        
        if($bRequre) {
            require_once($sFile);
            return true;
        } else {
            return file_get_contents($sFile);
        }
    }
    
    public static function Load($sName, $sType = LIBRARIES, $bInitialize = false) {
        $sTMPLoadName = $sName;
        if(($sType == OBJECTS && substr($sTMPLoadName, 0, 1) == OBJECTS_PREFFIX) || ($sType == LIBRARIES && substr($sTMPLoadName, 0, 1) == LIBRARIES_PREFFIX)) {
            $sTMPLoadName = substr($sTMPLoadName, 1);
        }
		
        if(in_array($sType, array(MODULES, OBJECTS))) {
            $sFile = APPPATH.$sType.'/'.$sTMPLoadName.'/'.$sTMPLoadName.EXT;
        } else {
            $sFile = SYSDIR.$sType.'/'.$sTMPLoadName.EXT;
        }
        
        if(($Error = self::LoadFile($sFile, true)) instanceof Error) {
            return $Error;
        }
        
        if($bInitialize && method_exists($sName, '_Initialize')) {
            $sName::_Initialize();
        }
        
        return true;
    }
}