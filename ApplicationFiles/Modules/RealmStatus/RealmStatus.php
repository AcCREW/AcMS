<?php

class RealmStatus {
    public function RealmStatus() {
    }
    
    public function Render() {
        $ar = array(
            'SiteTitle' => Application::GetConfig('SITE_TITLE'),
            'Realmlist' => Application::GetConfig('REALMLIST')
            );
        return CParser::Parse('RealmStatus', 'RealmStatus', $ar);
    }
}
