<?php
define('bP_STRING', FILTER_SANITIZE_STRING);
define('bP_INT',FILTER_SANITIZE_NUMBER_INT);
define('bP_FLOAT',FILTER_SANITIZE_NUMBER_FLOAT);
define('bP_ENCODED',FILTER_SANITIZE_ENCODED);
define('bP_EMAIL',FILTER_SANITIZE_EMAIL);
define('bP_ARRAY',FILTER_REQUIRE_ARRAY);

class bPack_Request
{
    public static function get($var_name ,$default_value = false, $type = bP_STRING,$option = '')
    {
        return self::_input(INPUT_GET,$var_name ,$default_value, $type,$option);
    }

    public static function post($var_name ,$default_value = false, $type = bP_STRING,$option = '')
    {
        return self::_input(INPUT_POST,$var_name ,$default_value, $type,$option);
    }

    public static function cookie($var_name ,$default_value = false, $type = bP_STRING,$option = '')
    {
        return self::_input(INPUT_COOKIE,$var_name ,$default_value, $type,$option);
    }

    public static function server($var_name ,$default_value = false, $type = bP_STRING,$option = '')
    {
        return self::_input(INPUT_SERVER,$var_name ,$default_value, $type,$option);
    }

    public static function clean($value, $type = bP_STRING)
    {
        return filter_var($value, $type);
    }

    protected static function _input($var_type ,$var_name,$default_value = false, $type = bP_STRING, $option = '')
    {
        $value = filter_input($var_type,$var_name,$type,$option);
        
        if($value == NULL)
        {
            return $default_value;
        }
        else
        {
            return $value;
        }
    }
    
    public static function load($var, $default_value = '')
    {
        if(isset($var))
        {
            return $var;
        }
        else
        {
            return $default_value;
        }
    }

    
}
