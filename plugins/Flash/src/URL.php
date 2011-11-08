<?php

define('bP_REDIRECT_HTTP', 0);
define('bP_REDIRECT_JS', 1);

class Plugin_URL extends bPack_Event_Plugin
{
    protected function pluginInitialization()
    {
        // this plugin doesn't need any initaliztion
    }

    protected function registerFunctions()
    {
        $this->parent
            ->registerPluginFunction('internal_link', array($this, 'get_internal_link'))
            ->registerPluginFunction('go', array($this, 'go'))
            ->registerPluginFunction('redirect', array($this, 'goto'));
    }

    //-----------------------------------------------

    public function redirect($link, $usage = bP_REDIRECT_HTTP)
    {
        if(headers_sent() === FALSE && $usage == bP_REDIRECT_HTTP)
        {
            header('location: ' . $link);
        }
        else
        {
            echo '<script type="text/javascript">location.href=\''.$link.'\';</script>';
            exit;
        }
        
        return true;
    }

    public function go($module = '', $controller = '', $action = '')
    {
        $this->redirect($this->get_internal_link($module, $controller, $action));

        return true;
    }

    public function get_internal_link($module = '', $controller = '', $action = '')
    {
        if(defined('bPack_Application_RewriteEnable'))
        {
            return bPack_Application_BASE_URI;
        }
        else
        {
            return bPack_Application_BASE_URI . 'index.php?module=' . $module . '&controller=' . $controller . '&action='. $action;
        }
    }
}
