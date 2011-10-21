<?php
define('bP_STRING', FILTER_SANITIZE_STRING);
define('bP_INT',FILTER_SANITIZE_NUMBER_INT);
define('bP_FLOAT',FILTER_SANITIZE_NUMBER_FLOAT);
define('bP_ENCODED',FILTER_SANITIZE_ENCODED);
define('bP_EMAIL',FILTER_SANITIZE_EMAIL);
define('bP_ARRAY',FILTER_REQUIRE_ARRAY);



class bPack_Request
{
    public $clean_vars = array();
	public $postdata = array();
	public $getdata = array();
        
    public function __construct()
    {
        foreach($_GET as $k => $v)
        {
            $this->clean_vars['get'][$k] = $this->get($k, '');
        }

        foreach($_POST as $k => $v)
        {
            if(is_array($v))
            {
                $this->clean_vars['post'][$k] = $v;
            }
            else
            {
                $this->clean_vars['post'][$k] = $this->post($k,'');
            }
        }

        foreach($_REQUEST as $k=> $v)
        {
            if(is_array($v))
            {
                $this->clean_vars['request'][$k] = $v;
            }
            else
            {
                $this->clean_vars['request'][$k] = $this->clean($v , bP_STRING);
            }
        }

		$this->postdata = &$this->clean_vars['post'];
		$this->getdata = &$this->clean_vars['get'];
    }


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

    public function IsPostBack()
    {
        return ((isset($_POST)) && (sizeof($_POST) > 0));
    }
}
