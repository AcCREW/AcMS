<?php
$nAppStartTime = array_sum(explode(' ', microtime()));
$nAppStartMemory = memory_get_usage(true);

require_once("./Controlls/System/Libraries/Application.php");

$Application = new Application();
$Application->Start();

//echo CCheck::CompareTimes(APP_START);
//echo '<br />';
//echo CCheck::CompareMemories();

//Dump('asd', false);
//echo 'asd';
?>