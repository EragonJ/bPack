<?php
class bPack_Dispatcher
{
    public static function run(bPack_DataContainer $route)
    {
        $file_path = bPack_App_BaseDir . 'do/' . $route->module . '/' . $route->controller . '.php';

        if(file_exists($file_path))
        {
            require_once($file_path);

            $className = $route->module.'_'.$route->controller;

            $obj = new $className;

            call_user_func(array($obj, 'startupAction'));

            if(is_array($route->parameter) && isset($route->parameter))
            {
                call_user_func_array(array($obj, $route->action), $route->parameter);
            }
            else
            {
                call_user_func(array($obj, $route->action));
            }
            
            call_user_func(array($obj, 'tearDownAction'));
        }
        else
        {
            throw new bPack_Exception('bPack Dispatcher: The request file is not accessible. (calling `'.$file_path.'`)');
        }

        return true;
    }
}

abstract class bPack_Event_Model
{
    abstract public function defaultAction();
    abstract public function startupAction();
    abstract public function tearDownAction();

    protected function msgbox ($text,$location = null ,$charset = 'utf-8')
    {
        echo '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">';
        echo '<script type="text/javascript">alert(\''.$text.'\');';

        if ($location != null)
        {
            echo 'location.href=\''.$location.'\';';
        }

        echo '</script>';

        if(!is_null($location))
        {
            exit;
        }
    }

    protected function redirect($location)
    {
        header('location:'.$location);
        exit;
    }

    protected function js_back()
    {
        echo '<script type="text/javascript">history.go(-1);</script>';
        exit;
    }
}