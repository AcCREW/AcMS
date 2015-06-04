<?php

class ODRealmlist extends AcObjectDescriptor {
	
	public function __construct() {
		$this->Name = 'Realmlist';
		$this->Text = 'Realmlist';
		$this->ObjectTableName = Application::$AuthDB.'.realmlist';
		$this->PrimaryKey = 'id';
		$this->UseCache = true;
		$this->Properties = array(
			"id",
			"name",
			"address",
			"localAddress",
			"localSubnetMask",
			"port",
			"icon",
			"flag",
			"timezone",
			"allowedSecurityLevel",
			"population",
			"gamebuild",
			"Region",
			"Battlegroup",
			"char_db",
			"world_db",
			"p_limit",
			"core",
			"ra",
			"ra_port",
			"soap",
			"soap_port",
			"unstuck_price",
			"teleport_price",
			"unstuck",
			"teleport",
			"changes",
			"change_faction_price",
			"change_race_price",
			"change_appearance_price",
			"3d_char_preview"
		);
	}
}