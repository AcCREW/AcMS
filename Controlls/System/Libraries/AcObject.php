<?php

class AcObject extends AcControl {
    protected $RecordID = null;
	protected $OD = null;
    
    public function __construct($nRecordID = null) {	
		$this->OD = self::LoadOD();
        if(!empty($nRecordID)) {
            $this->RecordID = $nRecordID;
            $this->Load();
        }
    }
    
    public function Update() {
		$sObjectTableName = $this->OD->ObjectTableName;
		$sPrimaryKey = $this->OD->PrimaryKey;
        $nRecordID = $this->RecordID;
        $bNeedUpdate = false;
        $arPropertiesNeedsUpdate = array();
		
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
                WHERE '.$sObjectTableName.'.'.$sPrimaryKey.' = '.$nRecordID);
			
        }
        
        return $nRecordID;
    }
    
    protected function Load() {        
		$sObjectTableName = $this->OD->ObjectTableName;
		$sPrimaryKey = $this->OD->PrimaryKey;
		$sObjectName = $this->OD->Name;
		$bUseCache = $this->OD->UseCache;
        $nRecordID = $this->RecordID;
        
        if($bUseCache && ($DataSource = CCache::Get($sObjectName.'_'.$nRecordID)) !== false) {
            $this->arData = $DataSource;

            return true;
        }
        
		self::_Load($sObjectTableName.'.'.$sPrimaryKey.' = '.$nRecordID, null, null, null, $this);
		
		if($bUseCache) {
            CCache::Save($this->ObjectName.'_'.$nRecordID, $this->arData, 60);
        }
		
		return true;
    }
    
    public function Reload() {
        $this->Load();
    }
	
    protected static function _Load($sCriteria = null, $sOrderBy = null, $nPage = null, $nLimit = null, &$Instance = null) {
		$OD = self::LoadOD();
		
        $sObjectTableName = $OD->ObjectTableName;
		
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
				$sObjectName = OBJECTS_PREFFIX.$OD->Name;
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
	
	public static function LoadOD() {
		$sClassName = get_called_class();
		if(($vRetValue = Loader::LoadOD($sClassName)) instanceof Error) {
			trigger_error($vRetValue->Message);
			return null;
		}
		
		return $vRetValue;
	}
	
}