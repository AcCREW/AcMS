<?php

class Status extends Form {
    private $RecordID = null;
    private $Limit = 50;
    private $Page = 1;
	
	public $RealmlistID = null;
	public $Realmlists = null;
    
    public function Status() {
        $this->RecordID = CInput::Get('RecordID');
        $nPage = CInput::Get('Page');
        if($nPage <= 0) {
            $nPage = 1;
        }
        $this->Page = $nPage;
		if(empty($this->Realmlists)) {
			$this->Realmlists = ORealmlist::Collection();
		}
	}
    
    public function Render() {
        $nRecordID = $this->RecordID;
		if(empty($nRecordID)) {
			return CParser::Parse('ChooseRealm', 'Status');
		}
		
        $nLimit = $this->Limit;
        $nStartRecord = ($this->Page - 1) * $nLimit;
		
		$sCacheKey = 'ReamlStatus_'.$nRecordID.'_'.$nStartRecord.'_'.$nLimit;
		if(($arData = CCache::Get($sCacheKey)) !== false) {
			Application::$Title = $arData['Title'];
            return $arData['Content'];
        }
		
		$Realmlist = $this->Realmlists[$nRecordID];
		$sTitle = $Realmlist->name;
		Application::$Title = $sTitle;
		
		$sDB = $Realmlist->char_db;
		
		$rs = new CRecordset('
            SELECT
	            `'.$sDB.'`.`characters`.`guid` AS CharacterID,
	            `'.$sDB.'`.`characters`.`name` AS Name,
	            `'.$sDB.'`.`characters`.`zone` AS Zone,
	            `'.$sDB.'`.`characters`.`level` AS Level,
	            `'.$sDB.'`.`characters`.`class` AS Class,
	            `'.$sDB.'`.`characters`.`race` AS Race,
	            `'.$sDB.'`.`characters`.`latency` AS Latency,
	            `'.$sDB.'`.`characters`.`gender` AS Gender
            FROM
	            `'.$sDB.'`.`characters`
            WHERE 
                `'.$sDB.'`.`characters`.`online` IS NOT NULL AND `'.$sDB.'`.`characters`.`online` IS NOT NULL = 1'); //LIMIT '.$nStartRecord.', '.$nLimit
		
		$arCharacters = array();
		
		$i = $nStartRecord;
		
		while(!$rs->EOF) {
            $arData = array(
				'Number' => ++$i,
				'RealmlistID' => $nRecordID,
				'CharacterID' => $rs->CharacterID,
				'Name' => $rs->Name,
				'Level' => $rs->Level,
				'Class' => $rs->Class,
				'Race' => $rs->Race,
				'Gender' => $rs->Gender,
				'Latency' => $rs->Latency
			);
			
			$arCharacters[] = $arData;
            $rs->MoveNext();
        }
		
		$sContent = CParser::Parse('Status', 'Status', array('Characters' => $arCharacters));
		
		$arData = array(
			'Content' => $sContent,
			'Title' => $sTitle
		);
		
        CCache::Save($sCacheKey, $arData, 60);
		
        return $sContent;
    }
}
