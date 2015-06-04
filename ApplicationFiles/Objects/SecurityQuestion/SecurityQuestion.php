<?php
/**
 * Summary of OSecurityQuestion
 * @property int $SecurityQuestionID Primary key
 * @property string $Name
 *
 */
class OSecurityQuestion extends AcObject {
    public function OSecurityQuestion($nRecordID = null) {
        parent::__construct($nRecordID);
    }
	
	/**
	 * @return OSecurityQuestion[]
	*/
	public static function Collection($sCriteria = null, $sOrderBy = null, $nPage = null, $nLimit = null) {
		$OD = self::LoadOD();
		
		$bGetCachedAllSecurityQuestions = $OD->UseCache && is_null($sCriteria) && is_null($sOrderBy) && is_null($nPage) && (is_null($nLimit) || $nLimit == -1);
		
		if($bGetCachedAllSecurityQuestions && ($arDataCollection = CCache::Get('SecurityQuestions')) !== false) {
			
			return $arDataCollection;
		}
		
		$arDataCollection = parent::Collection($sCriteria, $sOrderBy, $nPage, $nLimit);
		
		if($bGetCachedAllSecurityQuestions) {
			CCache::Save('SecurityQuestions', $arDataCollection, 600);
		}
		return $arDataCollection;
	}
}
