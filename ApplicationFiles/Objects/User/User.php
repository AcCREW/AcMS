<?php
/**
 * Summary of OUser
 * @property int $UserID Primary key
 * @property string $Email Email
 * @property string $Username Username
 * @property string $SHAPassHash SHA1 Generated Password
 * @property string $Expansion Expansion (1, 2, 3, ...)
 * @property string $JoinDate Join Date
 * @property string $LastIP Last Login IP
 * @property string $LastLogin Last Login Date
 * @property string $Avatar Avatar
 * @property int $Gender Gender (0 - Male, 1 - Female)
 * @property string $Location Location
 * @property int $DonatePoints Donate Points
 * @property int $VotePoints Vote Points
 * @property int $Posts Posts
 * @property int $Rank Rank
 * @property int $Reputation Reputation
 * @property string $SecurityAnswer Security Answer
 * @property string $SecurityQuestion Security Question
 * @property string $Name Name
 * @property int $GMLevel In-game GMLevel
 * @property array $Characters Array of Characters information
 * @property bool $IsOwner If the current session owns this user
 *
 */
class OUser extends AcObject {
    public function OUser($nRecordID = null) {
        parent::__construct($nRecordID);
    }
	
    public function Update() {
        $bNeedUpdate = false;
        $arPropertiesNeedsUpdate = array();
        $nUserID = $this->RecordID;

        foreach($this->_arPendingData as $sPendingProperty => $vPendingValue) {
            if($this->arData[$sPendingProperty] != $vPendingValue && !in_array($sPendingProperty, array('CharactersInfo', 'UserID'))) {
                $bNeedUpdate = true;
                $arPropertiesNeedsUpdate[] = $sPendingProperty;
                $this->arData[$sPendingProperty] = $vPendingValue;
            }
        }

        if($bNeedUpdate && sizeof($arPropertiesNeedsUpdate) > 0) {
            $arUpdateString = array();
            $sUpdateString = '';

            #region Construct Update Query
            foreach($arPropertiesNeedsUpdate as $sPropertyName) {
                switch($sPropertyName) {
                    case 'Email':
                        $arUpdateString[] = 'AUser.`email` = "'.$this->Email.'"';
                        break;
                    case 'Username':
                        $arUpdateString[] = 'AUser.`username` = "'.$this->Username.'"';
                        break;
                    case 'SHAPassHash':
                        $arUpdateString[] = 'AUser.`sha_pass_hash` = "'.$this->SHAPassHash.'"';
                        break;
                    case 'Expansion':
                        $arUpdateString[] = 'AUser.`expansion` = "'.$this->Expansion.'"';
                        break;
                    case 'JoinDate':
                        $arUpdateString[] = 'AUser.`joindate` = "'.$this->JoinDate.'"';
                        break;
                    case 'LastIP':
                        $arUpdateString[] = 'AUser.`last_ip` = "'.$this->LastIP.'"';
                        break;
                    case 'LastLogin':
                        $arUpdateString[] = 'AUser.`last_login` = "'.$this->LastLogin.'"';
                        break;
                    case 'Avatar':
                        $arUpdateString[] = 'CUser.`Avatar` = "'.$this->Avatar.'"';
                        break;
                    case 'Gender':
                        $arUpdateString[] = 'CUser.`Gender` = "'.$this->Gender.'"';
                        break;
                    case 'Location':
                        $arUpdateString[] = 'CUser.`Location` = "'.$this->Location.'"';
                        break;
                    case 'DonatePoints':
                        $arUpdateString[] = 'CUser.`DonatePoints` = "'.$this->DonatePoints.'"';
                        break;
                    case 'VotePoints':
                        $arUpdateString[] = 'CUser.`VotePoints` = "'.$this->VotePoints.'"';
                        break;
                    case 'Posts':
                        $arUpdateString[] = 'CUser.`Posts` = "'.$this->Posts.'"';
                        break;
                    case 'Rank':
                        $arUpdateString[] = 'CUser.`Rank` = "'.$this->Rank.'"';
                        break;
                    case 'Reputation':
                        $arUpdateString[] = 'CUser.`Reputation` = "'.$this->Reputation.'"';
                        break;
                    case 'SecurityAnswer':
                        $arUpdateString[] = 'CUser.`SecurityAnswer` = "'.$this->SecurityAnswer.'"';
                        break;
                    case 'SecurityQuestionID':
                        $arUpdateString[] = 'CUser.`SecurityQuestionID` = "'.$this->SecurityQuestionID.'"';
                        break;
                    case 'Name':
                        $arUpdateString[] = 'CUser.`Name` = "'.$this->Name.'"';
                        break;
                }
            }

            $sUpdateString = implode(', ', $arUpdateString);

            CRecordset::Execute('
                UPDATE
	                `'.Application::$AuthDB.'`.`account` AS AUser
                LEFT JOIN
	                `'.Application::$CMSDB.'`.`User` AS CUser
                ON
	                CUser.UserID = AUser.id
                SET '.$sUpdateString.' 
                WHERE AUser.id = '.$nUserID);
            
            #endregion

            CCache::Save('AcUser_'.$nUserID, $this->DataSource, 60);
        }
    }
    
    public function GenerateCUserData() {
        $nUserID = $this->RecordID;
		$sNickname = $this->Username;
		$this->arData['Name'] = $sNickname;
        CRecordset::Execute("INSERT INTO `".Application::$CMSDB."`.`User` (`UserID`, `Name`) VALUES ('".$nUserID."', '".$sNickname."')");
    }
	
    protected function Load() {
        $nUserID = $this->RecordID;
        if(($DataSource = CCache::Get('AcUser_'.$nUserID)) !== false) {
            $this->arData = $DataSource;
			
            return true;
        }
        
		$arRealmlistCollection = ORealmlist::Collection();
        $sCharacterInfo = "NULL";
        $nRealmlistCount = sizeof($arRealmlistCollection);
		if($nRealmlistCount > 0) {
		    $sCharacterInfo = "
		        (SELECT GROUP_CONCAT(CharactersTmp.`CharacterInfo` SEPARATOR '|')
		            FROM (";

		    foreach($arRealmlistCollection as $nCounter => $Realmlist) {
		        $nRealmID = $Realmlist->id;
		        $sCharacterInfo .= "SELECT CONCAT(CHARDB".$nRealmID.".guid, ',', CHARDB".$nRealmID.".`name`, ',', CHARDB".$nRealmID.".`race`, ',', CHARDB".$nRealmID.".`class`, ',', CHARDB".$nRealmID.".`gender`, ',".$nRealmID."') AS `CharacterInfo` FROM `".$Realmlist->char_db."`.`characters` AS CHARDB".$nRealmID." WHERE CHARDB".$nRealmID.".guid IS NOT NULL AND CHARDB".$nRealmID.".`name` IS NOT NULL AND CHARDB".$nRealmID.".account = ".$nUserID;
		        if($nCounter + 1 < $nRealmlistCount) {
		            $sCharacterInfo .= "UNION ALL";
		        }
		    }

		    $sCharacterInfo .= "
		            ) AS CharactersTmp)";
		}

        $sQuery = '
            SELECT
	            AUser.`id` as UserID,
	            CUser.`UserID` as CUserID,
                MAX(GMLevel.`gmlevel`) as GMLevel,
	            AUser.`email` as Email,
	            AUser.`username` as Username,
	            AUser.`sha_pass_hash` as SHAPassHash,
	            AUser.`expansion` as Expansion,
	            AUser.`joindate` as JoinDate,
	            AUser.`last_ip` as LastIP,
	            AUser.`last_login` as LastLogin,
	            CUser.`Avatar` as Avatar,
	            CUser.`Gender` as Gender,
	            CUser.`Location` as Location,
	            CUser.`DonatePoints` as DonatePoints,
	            CUser.`VotePoints` as VotePoints,
	            CUser.`Posts` as Posts,
	            CUser.`Rank` as Rank,
	            CUser.`Reputation` as Reputation,
	            CUser.`SecurityAnswer` as SecurityAnswer,
	            CUser.`SecurityQuestionID` as SecurityQuestionID,
	            CUser.`Name` as Name,
	            '.$sCharacterInfo.' as CharactersInfo
            FROM
	            `'.Application::$AuthDB.'`.`account` AS AUser
            LEFT JOIN
	             `'.Application::$CMSDB.'`.`User` AS CUser
            ON
	            CUser.`UserID` = AUser.`id`
            LEFT JOIN
                `'.Application::$AuthDB.'`.`account_access` AS GMLevel
            ON
                GMLevel.`id` = AUser.`id`
            WHERE
	            AUser.`id` = '.$nUserID;

        $rs = new CRecordset($sQuery);
        if(empty($rs->UserID)) {
            return new Error($this->ObjectName.'('.$nUserID.') not exists.');
        }
        
        $DataSource = $rs->RowData;

		if($nRealmlistCount > 0) {
		    $sCharactersInfo = $DataSource['CharactersInfo'];
		    $DataSource['Characters'] = array();
		    foreach($arRealmlistCollection as $nCounter => $Realmlist) {
		        $DataSource['Characters'][$Realmlist->id] = array(
		            'RealmName' => $Realmlist->name,
		            'RealmID' => $Realmlist->id,
		            'RealmCharacters' => array()
		        );
		    }
		    if(!empty($sCharactersInfo)) {
		        foreach(explode('|', $sCharactersInfo) as $sCharacterInfo) {
		            $arCharacterInfO = explode(',', $sCharacterInfo);
		            $nCharacterID = $arCharacterInfO[0];
		            $nCharacterName = $arCharacterInfO[1];
		            $nCharacterRace = $arCharacterInfO[2];
		            $nCharacterClass = $arCharacterInfO[3];
		            $nCharacterGender = $arCharacterInfO[4];
		            $nCharacterRealmID = $arCharacterInfO[5];
		            $DataSource['Characters'][$nCharacterRealmID]['RealmCharacters'][] =  array(
		                'CharacterID' => $nCharacterID,
		                'CharacterName' => $nCharacterName,
		                'CharacterRace' => $nCharacterRace,
		                'CharacterClass' => $nCharacterClass,
		                'CharacterGender' => $nCharacterGender,
		                'CharacterRealmID' => $nCharacterRealmID
		            );
		        }
		    }
		}

        unset($DataSource['CharactersInfo']);
        
        $this->arData = $DataSource;
        if(empty($rs->CUserID)) {
            $this->GenerateCUserData();
        }
        
        CCache::Save('AcUser_'.$nUserID, $this->arData, 60);

        return true;
    }
}
