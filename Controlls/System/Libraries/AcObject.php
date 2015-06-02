<?php

class AcObject extends AcControl {
    protected $RecordID = null;
    
    protected static $ObjectName = null;
    protected static $PrimaryKey = null;
    protected static $ObjectTableName = null;
    protected static $UseCache = false;
    
    public function __construct($nRecordID = null) {
        if(empty(self::$ObjectName) || empty(self::$PrimaryKey) || empty(self::$ObjectTableName)) {
            trigger_error('U must fill "ObjectName", "PrimaryKey" AND "ObjectTableName" properties of your class.');
        }
        if(!empty($nRecordID)) {
            $this->RecordID = $nRecordID;
            $this->Load();
        }
    }
    
    public function Update() {
        $nRecordID = $this->RecordID;
        $bNeedUpdate = false;
        $arPropertiesNeedsUpdate = array();
        $sObjectTableName = self::$ObjectTableName;

        foreach($this->_arPendingData as $sPendingProperty => $vPendingValue) {
            if($this->arData[$sPendingProperty] != $vPendingValue) {
                $bNeedUpdate = true;
                $arPropertiesNeedsUpdate[] = $sPendingProperty;
                $this->arData[$sPendingProperty] = $vPendingValue;
            }
        }

        if($bNeedUpdate && sizeof($arPropertiesNeedsUpdate) > 0) {
            $arUpdateString = array();
            foreach($arPropertiesNeedsUpdate as $sPropertyName) {
                $arUpdateString[] = $sObjectTableName.'.'.$sPropertyName.' = "'.$this->GetPropertyValue($sPropertyName).'"';
            }

            $sUpdateString = implode(', ', $arUpdateString);

            CRecordset::Execute('
                UPDATE
	                '.$sObjectTableName.'
                SET '.$sUpdateString.' 
                WHERE '.$sObjectTableName.'.'.self::$PrimaryKey.' = '.$nRecordID);
			
        }
        
        return $nRecordID;
    }
    
    protected function Load() {        
        $nRecordID = $this->RecordID;
        
        if(self::$UseCache && ($DataSource = CCache::Get(self::$ObjectName.'_'.$nRecordID)) !== false) {
            $this->arData = $DataSource;

            return true;
        }
        
        $sObjectTableName = self::$ObjectTableName;
        
		self::_Load($sObjectTableName.'.'.self::$PrimaryKey.' = '.$nRecordID, null, null, null, $this);
		
		if(self::$UseCache) {
            CCache::Save($this->ObjectName.'_'.$nRecordID, $this->arData, 60);
        }
		
		return true;
    }
    
    public function Reload() {
        $this->Load();
    }
	
    protected static function _Load($sCriteria = null, $sOrderBy = null, $nPage = null, $nLimit = null, &$Instance = null) {
        $sObjectTableName = self::$ObjectTableName;
		
		$bIsNotCollection = !is_null($Instance);
        
		if($bIsNotCollection) {
			$nPage = 1;
			$nLimit = 1;
		} else {
			if(empty($nPage)) {
				$nPage = 1;
			}
			if(empty($nLimit)) {
				$nLimit = DEFAULT_COLLECTION_LIMIT;
			}
		}
        
        $sQuery = '
            SELECT
	           *
            FROM
	            '.$sObjectTableName.'
	        '.(!empty($sCriteria) ? 'WHERE '.$sCriteria : '').'
            '.(!empty($sOrderBy) ? 'ORDER BY '.$sOrderBy : '').'
            '.(!empty($nLimit) && $nLimit != -1 ? 'LIMIT '.($nPage - 1).', '.$nLimit : '');

		$arDataCollection = array();
		
		$rs = new CRecordset($sQuery);
		while(!$rs->EOF) {
			/**
			 * @property AcObject $Object
			 */
			if($bIsNotCollection) {
				$Instance->arData = $rs->RowData;
				return array();
			} else {
				$sObjectName = OBJECTS_PREFFIX.self::$ObjectName;
				$Object =  new $sObjectName();
				$Object->arData = $rs->RowData;
				$arDataCollection[] = $Object;
				unset($Object);
			}
			$rs->MoveNext();
		}
		
		return $arDataCollection;
    }
	
	public static function Collection($sCriteria = null, $sOrderBy = null, $nPage = null, $nLimit = null) {
		return self::_Load($sCriteria, $sOrderBy, $nPage, $nLimit);
	}
	
}