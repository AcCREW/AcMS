<?php

class Register extends Form implements iFormAction {
	public $Username = null;
	public $Password = null;
	public $PasswordConfirm = null;
	public $EMail = null;
	public $SecurityAnswer = null;
	public $SecurityQuestions = null;
	public $SecurityQuestionID = null;
	public $RegisterAlert = null;
	
	function __construct() {
		parent::__construct();
		
		if(is_null($this->SecurityQuestions)) {
			$this->SecurityQuestions = OSecurityQuestion::Collection();
		}
	}
	
	function Render() {
		if(Application::$IsLogged) {
			$this->Location = ACPATH.'#/Index';
			return '';
		}
		
		return CParser::Parse('Register', 'Register');
	}
	
	#region iFormAction Members

	function OnSubmit() {
		$sUsername = $this->Username;
		$sPassword = $this->Password;
		$sPasswordConfirm = $this->PasswordConfirm;
		$sEMail = $this->EMail;
		$sSecurityAnswer = $this->SecurityAnswer;
		$sSecurityQuestionID = $this->SecurityQuestionID;
		
		if(empty($sUsername) || empty($sPassword) || empty($sPasswordConfirm) || empty($sEMail) || empty($sSecurityAnswer) || empty($sSecurityQuestionID)) {
			$this->RegisterAlert = new Alert("Please fill all fields.", Alert::ALERT_TYPE_WARNING);
			return;
		}
		
		if($sPassword != $sPasswordConfirm) {
			$this->RegisterAlert = new Alert("Password doesnt match.", Alert::ALERT_TYPE_WARNING);
			return;
		}
		
		if(($nUserID = OUser::Register($sUsername, $sPassword, $sEMail, $sSecurityQuestionID, $sSecurityAnswer)) instanceof Error) {
			$this->RegisterAlert = new Alert($nUserID->Message, Alert::ALERT_TYPE_ERROR);
			return;
		}
		
		if(empty($nUserID)) {
			$this->RegisterAlert = new Alert('Something went wrong. Please try again.', Alert::ALERT_TYPE_ERROR);
			return;
		}		
		
		$this->RegisterAlert = new Alert('Successful registration.', Alert::ALERT_TYPE_SUCCESS);
		
		$this->Username = null;
		$this->Password = null;
		$this->PasswordConfirm = null;
		$this->EMail = null;
		$this->SecurityAnswer = null;
		$this->SecurityQuestionID = null;
	}

	#endregion
}
