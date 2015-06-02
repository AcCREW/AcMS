<?php

class AcObjectTypeDescriptor {
    protected static $ObjectName = null;
    protected static $PrimaryKey = null;
    protected static $ObjectTableName = null;
    protected static $UseCache = false;
    
    public function __construct($sObjectName = null, $sPrimaryKey = null, $sObjectTableName = null, $bUseCache = false) {
        self::$ObjectName = $sObjectName;
		self::$PrimaryKey = $sPrimaryKey;
		self::$ObjectTableName = $sObjectTableName;
		self::$UseCache = $bUseCache;
    }
}