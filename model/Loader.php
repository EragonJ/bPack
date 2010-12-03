<?php
class bPack_Loader
{
    public static function run()
    {
        // php version check
        if (version_compare(PHP_VERSION, '5.2.0', '<'))
        {
            die('bPack Loader: bPack required PHP 5.2.0 or newer to run.');
        }
        
        // constants check
        if(!defined('bPack_Application_Directory'))
        {
            die("bPack Loader: Runtime constant bPack_Application_Directory is not defined.");
        }
        
        if(!defined('bPack_Directory'))
        {
            die("bPack Loader: Runtime constant bPack_Directory is not defined.");
        }
        
        // Register __autoload
        self::autoload();
        
        // Inital Error Handle
        bPack_ErrorHandler::setup();
        
        // check timezone
        if(!defined('bPack_Application_Timezone'))
        {
            define('bPack_Application_Timezone','UTC');
        }
        
        date_default_timezone_set(bPack_Application_Timezone);
    }
    
    public static function autoload()
    {
        spl_autoload_register(array( 'bPack_Loader','Process'));
    }
    
    public static function Process($request_className)
    {
        if(substr($request_className,0,6) == 'bPack_')
        {
            $request_className = str_replace('bPack_','',$request_className);
            $request_classPath = str_replace('_','/',$request_className);
            
            if(!file_exists(bPack_BaseDir . 'model/'.$request_classPath.'.php'))
            {
                return false;
            }
            else
            {
                include bPack_BaseDir . 'model/'.$request_classPath.'.php';
            }
            
            return true;
        }
        else
        {
            self::checkModel($request_className);
        }
        
        return false;
    }
    
    public static function checkModel($request_className)
    {
        $request_classPath = str_replace('_','/',$request_className);
            
        if(!file_exists(bPack_App_BaseDir . 'model/'.$request_classPath.'.php'))
        {
            return false;
        }
            
        include bPack_App_BaseDir . 'model/'.$request_classPath.'.php';
            
        return true;
    }
}
