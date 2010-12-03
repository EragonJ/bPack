<?php
/**
 * bPack Dispatcher
 */

class bPack_Dispatcher
{
    public static function run(bPack_DataContainer $route)
    {
        # check if the object(dispatcher) file exist
        $file_path = self::checkObjectExists($route);
        
        # include the file
        require_once $file_path;
        
        # initlazte the object
        $className = self::generateClassname($route);
        $object = new $className;
        
        # startup
        call_user_func(array($object, 'startupAction'));
        
        # if parameter is given, try to give parameter
        if(is_array($route->parameter) && isset($route->parameter))
        {
            call_user_func_array(array($object, $route->action), $route->parameter);
        }
        else
        {
            call_user_func(array($object, $route->action));
        }
        
        # teardown
        call_user_func(array($object, 'tearDownAction'));

        return true;
    }

    public static function checkObjectExists(bPack_DataContainer $route)
    {
        $file_path = bPack_Application_Directory . 'do/' . $route->module . '/' . $route->controller . '.php';

        if(!file_exists($file_path))
        {
            throw new bPack_Exception('bPack Dispatcher: The request file is not accessible. (calling `'.$file_path.'`)');
        }

        return $file_path;
    }

    public static function generateClassname($route)
    {
        return 'Controller_' . $route->module . '_' . $route->controller;

    }
}
