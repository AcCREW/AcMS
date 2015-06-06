<?php

class ODUser extends AcObjectDescriptor {
	
	public function __construct() {
		$this->Name = 'User';
		$this->Text = 'User';
		$this->ObjectTableName = 'tmp';
		$this->PrimaryKey = 'id';
		$this->UseCache = true;
		$this->Properties = array(
			'Email',
			'Username',
			'SHAPassHash',
			'Expansion',
			'JoinDate',
			'LastIP',
			'LastLogin',
			'Avatar',
			'Gender',
			'Location',
			'DonatePoints',
			'VotePoints',
			'Posts',
			'Rank',
			'Reputation',
			'SecurityAnswe',
			'SecurityQuestionID',
			'Name'
		);
	}
}