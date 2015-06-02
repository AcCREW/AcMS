<?php

class How {
    public function How() {
    }
    
    public function Render() {
        $ar = array(
            'SiteTitle' => Application::GetConfig('SITE_TITLE'),
            'Realmlist' => Application::GetConfig('REALMLIST')
            );
        return CParser::Parse('How', 'How', $ar);
    }
}
