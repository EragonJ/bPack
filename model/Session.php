<?php
class bPack_Session
{
    public function __construct()
    {
        if(!$this->_ifSessionStarted())
        {
            session_start();
        }
    }
    
    protected function _ifSessionStarted()
    {
        if(isset($_SESSION))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function get($var_name)
    {
        if(isset($_SESSION[$var_name]))
        {
            return $_SESSION[$var_name];
        }
        else
        {
            return false;
        }
    }
    
    public function set($var_name, $value)
    {
        $_SESSION[$var_name] = $value;
        
        return true;
    }
    
    public function getSessionID()
    {
        if(!$this->_ifSessionStarted())
        {
            session_start();
        }
        
        return session_id();
    }
    
    public function clear($var_name)
    {
        unset($_SESSION[$var_name]);
        
        return true;
    }
}