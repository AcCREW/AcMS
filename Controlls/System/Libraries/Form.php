<?php

abstract class Form {
	public $Location = null;
	
    public function __construct() {
		foreach(get_object_vars($this) as $sKey => $vValue) {
			if(!is_null($vValue)) {
				continue;
			}
			
			if(($vValue = CInput::PostGet($sKey)) !== false) {
				$this->{$sKey} = $vValue;
			}
		}
    }
	
	public function GetProperties($bForJS = true) {
		$sObjectName = get_class($this);
		
		$arData = array();
		
		$arNotNeeded = array('_Title', '_Author', '_Enabled', '_RequireAngularJS', '_CSS', '_JS', '_JSSchemes', '_Dir');
		
		foreach(get_object_vars($this) as $sKey => $vValue) {
			if(in_array($sKey, $arNotNeeded)) {
				continue;
			}
			if(is_array($vValue)) {
				$vFirstItem = current($vValue);
				if($vFirstItem instanceof AcObject) {
					$_arTMPData = array();
					foreach($vValue as $_vTMPValue) {
						$_arTMPData[] = $_vTMPValue->arData;
					}
					$arData[$bForJS ? $sObjectName.'.'.$sKey : $sKey] = $_arTMPData;
				} else {
					$arData[$bForJS ? $sObjectName.'.'.$sKey : $sKey] = $vValue;
				}
			} else {
				$arData[$bForJS ? $sObjectName.'.'.$sKey : $sKey] = $vValue;
			}
		}
		
		return $arData;
	}
	
	public abstract function Render();
}

interface iFormAction {
	
	public function OnSubmit();
}