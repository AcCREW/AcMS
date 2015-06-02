<?php
/**
 * Summary of ORealmlist
 * @property int $id Primary key
 * @property string $name
 * @property string $address
 * @property string $localAddress
 * @property string $localSubnetMask
 * @property int $port
 * @property int $icon
 * @property int $flag
 * @property int $timezone
 * @property int $allowedSecurityLevel
 * @property int $population
 * @property int $gamebuild
 * @property int $Region
 * @property int $Battlegroup
 * @property string $char_db
 * @property string $world_db
 * @property int $p_limit
 * @property string $core trinity/oregon
 * @property int $ra
 * @property int $ra_port
 * @property int $soap
 * @property int $soap_port
 * @property int $unstuck_price
 * @property int $teleport_price
 * @property int $unstuck
 * @property int $teleport
 * @property int $changes
 * @property int $change_faction_price
 * @property int $change_race_price
 * @property int $change_appearance_price
 * @property int $3d_char_preview
 *
 */
class ORealmlist extends AcObject {
    public function CRealmlist($nRecordID = null) {
        parent::__construct($nRecordID);
    }
	
	public static function _Initialize() {
		self::$PrimaryKey = 'id';
        self::$ObjectName = 'Realmlist';
        self::$ObjectTableName = '`'.Application::$AuthDB.'`.`realmlist`';
        self::$UseCache = true;
	}
	
	/**
	* @return ORealmlist[]
	*/
	public static function Collection($sCriteria = null, $sOrderBy = null, $nPage = null, $nLimit = null) {
		$bGetCachedAllRealmlists = self::$UseCache && is_null($sCriteria) && is_null($sOrderBy) && is_null($nPage) && (is_null($nLimit) || $nLimit == -1);
		
		if($bGetCachedAllRealmlists && ($arDataCollection = CCache::Get('Realmlists')) !== false) {
			
			return $arDataCollection;
		}
		
		$arDataCollection = parent::Collection($sCriteria, $sOrderBy, $nPage, $nLimit);
		
		if($bGetCachedAllRealmlists) {
			CCache::Save('Realmlists', $arDataCollection, 600);
		}
		return $arDataCollection;
	}
}
