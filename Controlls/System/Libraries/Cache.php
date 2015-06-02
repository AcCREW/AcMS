<?php
/**
 * Cache Class
 *
 * @package		CodeIgniter
 * @category	Security
 */
class CCache {
    
    private static $CACHE_PATH = './Temp/';

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public static function _Initialize() {
        Loader::LoadHelper('File');
	}
    
    public static function Get($sID) {
		if (!file_exists(self::$CACHE_PATH.$sID)) {
			return false;
		}
		
		$vData = read_file(self::$CACHE_PATH.$sID);
		$vData = unserialize($vData);
		
		if (time() > $vData['time'] + $vData['ttl']) {
			unlink(self::$CACHE_PATH.$sID);
			return false;
		}
		
		return $vData['data'];
	}
    
    public static function IsSupported() {
		return is_really_writable(self::$CACHE_PATH);
	}
    
    public static function Save($sID, $vData, $nTTL = 60) {		
		$arContents = array(
				'time'		=> time(),
				'ttl'		=> $nTTL,			
				'data'		=> $vData
			);
		
		if (write_file(self::$CACHE_PATH.$sID, serialize($arContents))) {
			@chmod(self::$CACHE_PATH.$sID, 0777);
			return true;			
		}

		return false;
	}
}

/* End of file Cache.php */