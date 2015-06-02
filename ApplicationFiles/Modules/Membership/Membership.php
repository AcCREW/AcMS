<?php
/**
 * Membership
 * 
 * @property string $Username
 * @property string $Password
 * @property CUser $User
 */
class Membership extends Form implements iFormAction {
	public $Username = null;
	public $Password = null;
	public $RememberMe = null;
	public $LoginAlert = null;
	public $User = null;
	public $ShowLoginAlert = null;
	
	public function __construct() {
		$this->User = Application::$User;
		parent::__construct();
	}
	
	public function Render() {
		$arData = Application::$IsLogged ? array('User' => array($this->User->arData)) : array();
		return CParser::Parse(Application::$IsLogged ? 'Membership' : 'Login', 'Membership',  $arData);
	}
	
	#region iFormAction Members

	public function OnSubmit() {
		$sUsername = $this->Username;
		$sPassword = $this->Password;
		
		if(empty($sUsername) || empty($sPassword)) {
			$this->LoginAlert = new Alert("Please fill all fields.", Alert::ALERT_TYPE_WARNING);
			return;
		}
        
		$sSha = sha1(strtolower($sUsername).':'.strtolower($sPassword));
        
		$rs = new CRecordset('
		        SELECT 
		            `'.Application::$AuthDB.'`.`account`.`id` AS UserID 
		        FROM 
		            `'.Application::$AuthDB.'`.`account` 
		        WHERE 
		            `'.Application::$AuthDB.'`.`account`.`username` = "'.$sUsername.'" 
		            AND  `'.Application::$AuthDB.'`.`account`.`sha_pass_hash` = "'.$sSha.'"
		        LIMIT 0, 1');
        
        
		$nUserID = $rs->UserID;
		if(!empty($nUserID)) {
			$bIsSessExpireOnCloseChanged = false;
			if($this->RememberMe && CSession::$SESS_EXPIRE_ON_CLOSE === true) {
				$bIsSessExpireOnCloseChanged = true;
				CSession::$SESS_EXPIRE_ON_CLOSE = false;
			}
			CSession::Set('IsLogged', true);
			CSession::Set('UserID', $nUserID);
			$this->Location = ACPATH;
			if($bIsSessExpireOnCloseChanged) {
				CSession::$SESS_EXPIRE_ON_CLOSE = true;
			}
		} else {
			CSession::Set('IsLogged', false);
			$this->LoginAlert = new Alert("Invalid Username/Password.");
		}
	}

	#endregion
}
