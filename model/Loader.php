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
        if(!defined('bPack_App_BaseDir'))
        {
            die("bPack Loader: Please give bPack_App_BaseDir");
        }
        
        if(!defined('bPack_BaseDir'))
        {
            die("bPack Loader: Please give bPack_BaseDir");
        }
        
        // Register __autoload
        self::autoload();
        
        // Inital Error Handle
        bPack_ErrorHandler::setup();
        
        // check timezone
        if(!defined('bPack_TIMEZONE'))
        {
            define('bPack_TIMEZONE','UTC');
        }
        
        date_default_timezone_set(bPack_TIMEZONE);
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
                /**
                 * Check if the migration adaptor were created for moved class
                 */
                if(!file_exists(bPack_BaseDir . 'model/Migration_Adaptor/'.$request_className.'.php'))
                {
                    return false;
                }
                else
                {
                    include(bPack_BaseDir . 'model/Migration_Adaptor/'.$request_className.'.php');
                }
            }
            else
            {
                include(bPack_BaseDir . 'model/'.$request_classPath.'.php');
            }
            
            return true;
        }
        else
        {
            $request_classPath = str_replace('_','/',$request_className);
            
            if(!file_exists(bPack_App_BaseDir . 'model/'.$request_classPath.'.php'))
            {
                return false;
            }
            
            include(bPack_App_BaseDir . 'model/'.$request_classPath.'.php');
            
            return true;
        }
        
        return false;
    }
}