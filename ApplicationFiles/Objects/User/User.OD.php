<?php

class ODUser extends AcObjectDescriptor {
	
	public function __construct() {
		$this->Name = 'User';
		$this->Text = 'User';
		$this->ObjectTableName = 'tmp';
		$this->PrimaryKey = 'id';
		$this->UseCache = true;
		$this->Properties = array();
	}
}