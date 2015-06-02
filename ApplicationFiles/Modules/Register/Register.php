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
		$this->Username = $this->Username == 'wtf' ? 'TEST' : 'wtf';
		$this->Password = 'wtf';
		$this->PasswordConfirm = 'wtf';
		$this->EMail = 'wtf';
		$this->SecurityAnswer = 'wtf';
		$this->RegisterAlert = new Alert("Ддз");
		#$this->SecurityQuestionID = 5;
		#$this->SecurityQuestions = CSecurityQuestion::Collection();
	}

	#endregion
}
