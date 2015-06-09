<?php

class Profile extends Form {
    public function Render() {
		if(!Application::$IsLogged) {
			$this->Location = ACPATH;
			return null;
		}
        return CParser::Parse('Profile', 'Profile', Application::$User->arData);
    }
}
