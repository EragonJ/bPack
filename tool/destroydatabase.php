<?php

# define application location
define("bPack_Application_Directory", getcwd() . '/' );
define("bPack_Application_Environment", ((!getenv("bPack_ENV")) ? "dev" : getenv("bPack_ENV")) );

# load config
require bPack_Application_Directory . "config/constant.php";
require bPack_Application_Directory . "config/" . bPack_Application_Environment . "/config.php";

# load bPack Loader
require bPack_Application_Directory . "lib/bPack/model/Loader.php";
bPack_Loader::run();

$dir = new DirectoryIterator(bPack_Application_Directory . 'model/Model/');

$objects = array();
foreach ($dir as $fileinfo) 
{
    if (!$fileinfo->isDot()) 
    {
        require(bPack_Application_Directory . 'model/Model/' . $fileinfo->getFilename());

        $object_name = 'Model_' . $fileinfo->getBasename('.php');
        $objects[]  = new $object_name;
    }
}

$done = 0;
$error =array();
foreach($objects as $obj)
{
    try
    {
        $obj->destroySchema();
        $done++;
    }
    catch(Exception $e)
    {
        $error[] = $e;
    }
}

echo "$done had been created, but there were ".sizeof($error)." errors";

echo '------------------------------------';

var_dump($error);
