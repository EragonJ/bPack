<?php
#
# bPack Loader
#
# @package bPack
# @subpackage Loader
#

class bPack_Loader {
    #
    # run - a sequence of action to initialize bPack
    # 
    # @param void
    # @return void
    #
    public static function run()
    {
        #
        # Check PHP Version
        # bPack require PHP 5.2 or newer to run (for JSON_support)
        #
        if (version_compare(PHP_VERSION, '5.2.0', '<'))
        {
            die('bPack Loader: bPack required PHP 5.2.0 or newer to run.');
        }
        
        #
        # Prevent user include this file directly
        # and had not define the needed constant
        # We should check this here, just in case.
        #
        if(!defined('bPack_Application_Directory'))
        {
            die("bPack Loader: Runtime constant bPack_Application_Directory is not defined.");
        }
        
        #
        # Register bPack Autoload Function to PHP
        # It enable us to load class freely
        #
        self::autoload();
        
        #
        # Setting up Error Handler
        #
        bPack_ErrorHandler::setup();
        
        #
        # If timezone is not set, will occur serveral serious problem
        # Check for that constant here, or set it to UTC
        #
        if(!defined('bPack_Application_Timezone'))
        {
            define('bPack_Application_Timezone','UTC');
        }
        
        date_default_timezone_set(bPack_Application_Timezone);
    }
    
    #
    # Register bPack's Autoload function to PHP
    #
    public static function autoload()
    {
        spl_autoload_register(array( 'bPack_Loader','Process'));
    }
    
    #
    # This function process as bPack's autoload function
    #
    # It will detect serveral defined prefix, and load from specified location
    #
    # @ TODO: We should put these defined prefix as registerable, that we can add defined prefix at any time without regular modify here.
    # 
    # @param string $request_className -> the classname to find file
    # @return bool -> the result of loading
    #
    public static function Process($request_className)
    {
        #
        # If classname begins with bPack_, that might be a bPack Module.
        #
        if(substr($request_className,0,6) == 'bPack_')
        {
            $request_className = str_replace('bPack_','',$request_className);
            $request_classPath = str_replace('_','/',$request_className);
            
            if(!file_exists(bPack_Application_Directory . 'lib/bPack/' . 'model/'.$request_classPath.'.php'))
            {
                throw new bPack_Exception('Requeseted class '.$request_className.' not found.');
            }
            else
            {
                include bPack_Application_Directory . 'lib/bPack/' . 'model/'.$request_classPath.'.php';
            }
            
            return true;
        }
        #
        # If classname begins with Plugin_, that might be a Plugin Module
        #
        elseif(substr($request_className,0,7) == 'Plugin_')
        {
            $plugin_name = str_replace('Plugin_','',$request_className);

            $filename = bPack_Application_Directory . 'lib/plugin/' . $plugin_name . '/src/' . $plugin_name.'.php';

            if(file_exists($filename))
            {
                include $filename;
            }
            else
            {
                throw new bPack_Exception('Requeseted class '.$request_className.' not found.');
            }

            return true;
        }
        #
        # If the classname has no prefix that defined here, we call checkModel() to help us find the class file
        #
        else
        {
            return self::checkModel($request_className);
        }

        return false;
    }
    
    #
    # We assert that all class without prefix is a user model, so we should check if the coresponding file exists.
    #
    # If yes, load it.
    # If no, return false.
    #
    public static function checkModel($request_className)
    {
        $request_classPath = str_replace('_','/',$request_className);
            
        if(!file_exists(bPack_Application_Directory . 'model/'.$request_classPath.'.php'))
        {
            return false;
        }
            
        include bPack_Application_Directory . 'model/'.$request_classPath.'.php';
    }
}
