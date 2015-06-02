<?php

/**
 * Check class provides options to check what time and memory costs a function or whole application
 *
 * @author Венцислав Кьоровски
 */
class CCheck {
    private static $MarkedTimes = array();
    private static $MarkedMemories = array();
    
    public static function _Initialize() {
        global $nAppStartTime, $nAppStartMemory;
        self::MarkTime(APP_START, $nAppStartTime);
        self::MarkMemory(APP_START, $nAppStartMemory);
    }
    
    public static function MarkTime($sMarker, $nTime = null) {
        if(is_null($nTime)) {
            $nTime = array_sum(explode(' ', microtime()));
        }
        self::$MarkedTimes[$sMarker] = $nTime;
    }
    
    public static function MarkMemory($sMarker, $nSize = null) {
        if(is_null($nSize)) {
            $nSize = memory_get_usage(true);
        }
        self::$MarkedMemories[$sMarker] = $nSize;
    }
    
    public static function CompareMemories($sStartMarker = null, $sEndMarker = null) {
        return self::Compare($sStartMarker, $sEndMarker, 'Memory');
    }
    
    public static function CompareTimes($sStartMarker = null, $sEndMarker = null) {
        return self::Compare($sStartMarker, $sEndMarker, 'Time');
    }
    
    private static function Compare($sStartMarker = null, $sEndMarker = null, $sType = 'Time') {
        $arMarketValues = $sType == 'Time' ? self::$MarkedTimes : self::$MarkedMemories;
        if(!isset($arMarketValues[$sStartMarker])) {
            if($sType == 'Time') {
                show_error("Can't find start time.");
            } else {
                $nStartValue = 0;
            }
        } else {
            $nStartValue = $arMarketValues[$sStartMarker];
        }
        
        if(!isset($arMarketValues[$sEndMarker])) {
            $nEndValue = $sType == 'Time' ? array_sum(explode(' ' , microtime())) : memory_get_usage(true);
        } else {
            $nEndValue = $arMarketValues[$sEndMarker];
        }
        
        return $sType == 'Time' ? sprintf('%.4f', $nEndValue - $nStartValue) : ConvertBytesToString($nEndValue - $nStartValue); 
    }
}
