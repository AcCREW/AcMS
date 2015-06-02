<?php

/**
 * DB Connection
 *
 * DB - MySQLi class databse provider
 *
 */
class CDBConnection{
    private static $Host = null;
    private static $Username = null;
    private static $Password = null;
    private static $DB = null;
    private static $Port = null;
    
    private static $_instance = null;
	private static $DbSocket = null;
	
	public static function _Initialize(){
		if(!is_null(self::$_instance)){
            return;
        }
        
        self::$Host = Application::GetConfig('MYSQL_HOST');
        self::$Username = Application::GetConfig('MYSQL_USER');
        self::$Password = Application::GetConfig('MYSQL_PASSOWRD');
        self::$DB = Application::GetConfig('MYSQL_DEFAULT_DB');
        $sPort = Application::GetConfig('MYSQL_PORT');
        
        if($sPort == null)
            self::$Port = ini_get('mysqli.default_port');
        else
            self::$Port = $sPort;
        
        self::Connect();
        self::$_instance->set_charset("utf8");
        if(mysqli_connect_errno()){
            show_error("Connect failed: %s\n", mysqli_connect_error());
            exit;
        }else{
            $sQuery = 'set names utf8;';
            mysqli_query(self::$_instance, $sQuery);
            $sQuery = 'set group_concat_max_len=1073741824;';
            mysqli_query(self::$_instance, $sQuery);
        }
	}
	
	private static function Connect($nAttempt = 1){
		self::$_instance = new mysqli(self::$Host, self::$Username, self::$Password, self::$DB, self::$Port, self::$DbSocket);
		if(self::$_instance->connect_error && $nAttempt < 30){
			sleep(2);
			self::Connect($nAttempt + 1);
		} elseif(self::$_instance->connect_error) {
            show_error('Connection to DB failed.('.self::$_instance->connect_error.')');
            exit;
        }
        
        Application::$DB_INITALIZED = true;
	}
    
	static function GetInstance(){
		return self::$_instance;
	}
    
	static function SetDBName($DbName){
		self::$DB= $DbName; mysqli_select_db(self::$_instance, self::$DB);
	}
}
