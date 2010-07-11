<?php
define('bP_REDIRECT_HTTP', 0);
define('bP_REDIRECT_JS', 1);

class bPack_Event_Helper_Default extends bPack_Event_Helper
{
    public $name = 'default';
    
    public function msgbox ($text,$location = null ,$charset = 'utf-8')
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
    
    public function go($inner_location = null)
    {
        if(is_null($inner_location))
        {
            throw new bPack_Exception('bPack_Event_Helper_Default->go(): No location where given.');
        }
        
        $this->redirect(bPack_BASE_URI . $inner_location);
    }
    

    public function redirect($location, $usage  = bP_REDIRECT_HTTP)
    {
	if(headers_sent() == FALSE && $usage == bP_REDIRECT_HTTP)
	{
	    header('location:'.$location);
	    exit;
	}
	else
	{
	    echo '<script type="text/javascript">location.href=\''.$location.'\';</script>';
	    exit;
	}
    }

    public function js_back()
    {
        echo '<script type="text/javascript">history.go(-1);</script>';
        exit;
    }
}