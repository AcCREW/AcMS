<?php

class Alert {
	const ALERT_TYPE_ERROR = 'AlertError';
	const ALERT_TYPE_WARNING = 'AlertWarning';
	const ALERT_TYPE_SUCCESS = 'AlertSuccess';
	const ALERT_TYPE_INFORMATION = 'AlertInformation';
	
	public $_Type = 'Alert';
	public $AlertType = null;
	public $_Content = null;
	public $Content = null;
	
    public function __construct($sContent, $sAlertType = self::ALERT_TYPE_ERROR) {
		$this->_Content = $sContent;
		$this->AlertType = $sAlertType;
		$this->Content = $this->Render();
    }
	
	public function Render() {
        return CParser::Parse($this->AlertType, 'Alert', array('Content' => $this->_Content));
	}
    
    public function __toString() {
		return $this->Render();
    }
}
