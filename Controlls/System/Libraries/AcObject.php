<?php

class AcObject extends AcControl {
    public $RecordID = null;
	public $OD = null;
    
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
		$sClassName = $this->OD->Name;
		$bUseCache = $this->OD->UseCache;
		
        $nRecordID = $this->RecordID;
		$bIsAdd = empty($nRecordID);
        $bNeedUpdate = false;
        $arPropertiesNeedsUpdate = array();
		
        foreach($this->arPendingData as $sPendingProperty => $vPendingValue) {
			if($sPendingProperty == $sPrimaryKey || !in_array($sPendingProperty, $this->OD->Properties)) {
				continue;
			}
            if(!isset($this->arData[$sPendingProperty]) || $this->arData[$sPendingProperty] != $vPendingValue) {
                $bNeedUpdate = true;
                $arPropertiesNeedsUpdate[$sPendingProperty] = $vPendingValue;
                $this->arData[$sPendingProperty] = $vPendingValue;
            }
        }
		$this->arPendingData = array();

        if($bNeedUpdate && sizeof($arPropertiesNeedsUpdate) > 0) {
			if($bIsAdd) {
				CRecordset::Execute('
					INSERT INTO	`SecurityQuestion` 
						(`'.implode('`, `', array_keys($arPropertiesNeedsUpdate)).'`) 
					VALUES 
						("'.implode('", "', $arPropertiesNeedsUpdate).'")');
				$nRecordID = CRecordset::LastInsertedID();
				
				$this->RecordID = $nRecordID;
				$this->SetPropertyValue($sPrimaryKey, $nRecordID);
			} else {
				$arUpdateString = array();
				foreach($arPropertiesNeedsUpdate as $sPropertyName => $vValue) {
					$arUpdateString[] = $sObjectTableName.'.'.$sPropertyName.' = "'.$vValue.'"';
				}

				$sUpdateString = implode(', ', $arUpdateString);

				CRecordset::Execute('
					UPDATE
						'.$sObjectTableName.'
					SET '.$sUpdateString.' 
					WHERE '.$sObjectTableName.'.'.$sPrimaryKey.' = '.$nRecordID);
			}
			if($bUseCache) {
				foreach (glob(CCache::$CACHE_PATH.$sClassName."*") as $sFileName) {
					@unlink($sFileName);
				}
			}
			
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
        
		self::_Load($sObjectTableName.'.'.$sPrimaryKey.' = '.$nRecordID, null, null, null, false, $this);
		
		if($bUseCache) {
            CCache::Save($this->ObjectName.'_'.$nRecordID, $this->arData, 60);
        }
		
		return true;
    }
    
    public function Reload() {
        $this->Load();
    }
	
    protected static function _Load($sCriteria = null, $sOrderBy = null, $nPage = null, $nLimit = null, $bIsCollectionTotalCount = false, &$Instance = null) {
		$OD = self::LoadOD();
		
        $sObjectTableName = $OD->ObjectTableName;
		
		$bIsNotCollection = !is_null($Instance);
		
		if($bIsCollectionTotalCount && !$bIsNotCollection) {
			$nPage = null;
			$nLimit = null;
		} elseif($bIsNotCollection) {
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
	           '.($bIsCollectionTotalCount ? 'COUNT(*) AS Count' : '*').'
            FROM
	            '.$sObjectTableName.'
	        '.(!empty($sCriteria) ? 'WHERE '.$sCriteria : '').'
            '.(!empty($sOrderBy) ? 'ORDER BY '.$sOrderBy : '').'
            '.(!empty($nLimit) && $nLimit != -1 ? 'LIMIT '.($nPage - 1).', '.$nLimit : '');

		$arDataCollection = array();
		
		$rs = new CRecordset($sQuery);
		if($bIsCollectionTotalCount && !$bIsNotCollection) {
			return $rs->Count;
		}
		
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
				$arDataCollection[$Object->{$OD->PrimaryKey}] = $Object;
				unset($Object);
			}
			$rs->MoveNext();
		}
		
		return $arDataCollection;
    }
	
	public static function CollectionGetTotalCount($sCriteria = null) {
		return self::_Load($sCriteria, null, null, null, true);
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