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
		$bIsAdd = empty($nUserID);
		
		foreach($this->arPendingData as $sPendingProperty => $vPendingValue) {
			if($sPendingProperty == 'UserID' || !in_array($sPendingProperty, $this->OD->Properties)) {
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
            $arAUserUpdateString = array();
            $arCUserUpdateString = array();
            $sUpdateString = '';

            #region Construct Update Query
            foreach($arPropertiesNeedsUpdate as $sPropertyName => $vValue) {
                switch($sPropertyName) {
                    case 'Email':
                        $arAUserUpdateString['email'] = $vValue;
                        break;
                    case 'Username':
                        $arAUserUpdateString['username'] = $vValue;
                        break;
                    case 'SHAPassHash':
                        $arAUserUpdateString['sha_pass_hash'] = $vValue;
                        break;
                    case 'Expansion':
                        $arAUserUpdateString['expansion'] = $vValue;
                        break;
                    case 'JoinDate':
                        $arAUserUpdateString['joindate'] = $vValue;
                        break;
                    case 'LastIP':
                        $arAUserUpdateString['last_ip'] = $vValue;
                        break;
                    case 'LastLogin':
                        $arAUserUpdateString['last_login'] = $vValue;
                        break;
                    case 'Avatar':
                        $arCUserUpdateString['Avatar'] = $vValue;
                        break;
                    case 'Gender':
                        $arCUserUpdateString['Gender'] = $vValue;
                        break;
                    case 'Location':
                        $arCUserUpdateString['Location'] = $vValue;
                        break;
                    case 'DonatePoints':
                        $arCUserUpdateString['DonatePoints'] = $vValue;
                        break;
                    case 'VotePoints':
                        $arCUserUpdateString['VotePoints'] = $vValue;
                        break;
                    case 'Posts':
                        $arCUserUpdateString['Posts'] = $vValue;
                        break;
                    case 'Rank':
                        $arCUserUpdateString['Rank'] = $vValue;
                        break;
                    case 'Reputation':
                        $arCUserUpdateString['Reputation'] = $vValue;
                        break;
                    case 'SecurityAnswer':
                        $arCUserUpdateString['SecurityAnswer'] = $vValue;
                        break;
                    case 'SecurityQuestionID':
                        $arCUserUpdateString['SecurityQuestionID'] = $vValue;
                        break;
                    case 'Name':
                        $arCUserUpdateString['Name'] = $vValue;
                        break;
					case 'CUserID':
                        $arCUserUpdateString['UserID'] = $vValue;
                        break;
                }
            }
            #endregion
			
			if($bIsAdd) {
				CRecordset::Execute('
					INSERT INTO	`'.Application::$AuthDB.'`.`account`
						(`'.implode('`, `', array_keys($arAUserUpdateString)).'`) 
					VALUES 
						("'.implode('", "', $arAUserUpdateString).'")');
				
				$nUserID = CRecordset::LastInsertedID();
				$this->RecordID = $nUserID;
				$this->SetPropertyValue('UserID', $nUserID);
				$this->SetPropertyValue('CUserID', $nUserID);
				
				$arCUserUpdateString['UserID'] = $nUserID;
				
				CRecordset::Execute('
					INSERT INTO	`'.Application::$CMSDB.'`.`User`
						(`'.implode('`, `', array_keys($arCUserUpdateString)).'`) 
					VALUES 
						("'.implode('", "', $arCUserUpdateString).'")');
			} else {
				
				$arUpdateString = array();
				
				foreach($arAUserUpdateString as $sPropertyName => $vValue) {
					$arUpdateString[] = 'AUser.`'.$sPropertyName.'` = "'.$vValue.'"';
				}
				
				foreach($arCUserUpdateString as $sPropertyName => $vValue) {
					$arUpdateString[] = 'CUser.`'.$sPropertyName.'` = "'.$vValue.'"';
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
			}

            CCache::Save('AcUser_'.$nUserID, $this->arData, 60);
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
