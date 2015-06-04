<?php

class Index {
    private $RecordID = null;
    private $Limit = 50;
    private $Page = 1;
    
    public function Index() {
        $this->RecordID = CInput::Get('RecordID');
        $nPage = CInput::Get('Page');
        if($nPage <= 0) {
            $nPage = 1;
        }
        $this->Page = $nPage;
    }
    
    public function Render() {
        $sContent = '';
        
        $nRecordID = $this->RecordID;
        $nLimit = $this->Limit;
        $nStartRecord = ($this->Page - 1) * $nLimit;

		$sCacheKey = 'Index_'.$nRecordID.'_'.$nStartRecord.'_'.$nLimit;
		if(($arData = CCache::Get($sCacheKey)) !== false) {
			Application::$Title = $arData['Title'];
            return $arData['Content'];
        }
		
        $rs = new CRecordset('
            SELECT
	            `'.Application::$CMSDB.'`.`Topic`.`TopicID` AS TopicID,
	            `'.Application::$CMSDB.'`.`Topic`.`Title` AS Title,
	            `'.Application::$CMSDB.'`.`Topic`.`Date` AS Date,
	            `'.Application::$CMSDB.'`.`Topic`.`Content` AS Content,
	            `'.Application::$CMSDB.'`.`User`.`UserID` AS UserID,
	            `'.Application::$CMSDB.'`.`User`.`Name` AS UserName
            FROM
	            `'.Application::$CMSDB.'`.`Topic`
            JOIN `User` ON `'.Application::$CMSDB.'`.`User`.`UserID` = `'.Application::$CMSDB.'`.`Topic`.`UserID`
            WHERE 
                '.(!empty($nRecordID) ? '`'.Application::$CMSDB.'`.`Topic`.`TopicID` = '.$nRecordID : 'TRUE').'
            LIMIT '.$nStartRecord.', '.$nLimit);
        
        while(!$rs->EOF) {
            if(!empty($nRecordID)) {
                Application::$Title = $rs->Title;
            }
			
            $arData = array(
				'BoxContent' => CParser::Parse('Topic', 'Index', $rs),
				'BoxTitle' => '<a href="#/Index/'.$rs->TopicID.'">'.$rs->Title.'</a>'
			);
			$sContent .= CParser::Parse('_RightBox', Application::$TemplateDir, $arData);
            $rs->MoveNext();
        }
		
		$arData = array(
			'Content' => $sContent,
			'Title' => Application::$Title
		);
		
        CCache::Save($sCacheKey, $arData, 60);

        return $sContent;
    }
}
