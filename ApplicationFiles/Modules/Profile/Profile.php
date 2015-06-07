<?php

class Profile {
    public function Render() {
        $ar = array(
            'SiteTitle' => Application::GetConfig('SITE_TITLE'),
            'Realmlist' => Application::GetConfig('REALMLIST')
            );
        return CParser::Parse('How', 'How', $ar);
    }
}
