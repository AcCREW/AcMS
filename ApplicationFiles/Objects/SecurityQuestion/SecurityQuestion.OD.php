<?php

class ODSecurityQuestion extends AcObjectDescriptor {
	
	public function __construct() {
		$this->Name = 'SecurityQuestion';
		$this->Text = 'SecurityQuestion';
		$this->ObjectTableName = Application::$CMSDB.'.SecurityQuestion';
		$this->PrimaryKey = 'SecurityQuestionID';
		$this->UseCache = true;
		$this->Properties = array(
			"SecurityQuestionID",
			"Name"
		);
	}
}