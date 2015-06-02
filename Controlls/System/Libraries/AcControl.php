<?php

/**
 * Controll provider
 *
 * @author Венцислав Кьоровски
 */
class AcControl extends stdClass {
	public $arData = array();
    protected $arPendingData = array();
	
	public function __get($sName) {
        return $this->GetPropertyValue($sName);
    }
    
    public function GetPropertyValue($sName) {
        return isset($this->arPendingData[$sName]) ? $this->arPendingData[$sName] : (isset($this->arData[$sName]) ? $this->arData[$sName] : NULL);
    }

    public function __set($sName, $vValue) {
        $this->SetPropertyValue($sName, $vValue);
    }
    
    public function SetPropertyValue($sName, $vValue) {
        $this->arPendingData[$sName] = $vValue;
    }
    
    public function SetData($arData = array()) {
        foreach($arData as $sName => $vValue) {
            $this->SetPropertyValue($sName, $vValue);
        }
    }
    
    public function IsPropertyChanged($sPropertyName) {
		return (array_key_exists($sPropertyName, $this->arPendingData) && array_key_exists($sPropertyName, $this->arData) && $this->arData[$sPropertyName] != $this->GetPropertyValue($sPropertyName));
	}

    public function HasChangedProperties() {
        $arChangedProperties = array();

        foreach($this->arPendingData as $vPendingKey => $vPendingValue) {
            if(!isset($this->arData[$vPendingKey]) || $this->arData[$vPendingKey] != $this->arPendingData[$vPendingKey]) {
                $arChangedProperties[] = $vPendingKey;
            }
        }
        
        return sizeof($arChangedProperties) == 0 ? false : $arChangedProperties;
    }
}
