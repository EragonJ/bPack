<?php

define('bPack_Application_Directory', getcwd() . "/");
define('bPack_Application_Environment','dev');

require "./lib/bPack/model/Loader.php";
bPack_Loader::run();

require bPack_Application_Directory . 'model/Model/' . $argv[1] . '.php';

$class_name = 'Model_' . $argv[1];
$model = new $class_name;

$fields = $model->getColumns();

foreach($fields as $column_name => $column_setting)
{
	echo "        " . $column_name . " => " . $column_setting['type'] . "\n";
}
