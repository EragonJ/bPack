<?php
/**
 * A setting loader for bPack 
 */
class bPack_Config
{
    protected static $_instance = null;
    protected $_provider = null;
    
    static public function getInstance()
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new bPack_Config();    
        }
        
        return self::$_instance;
    }
    
    public function setProvider(bPack_Config_Provider $provider = null)
    {
        if(is_null($provider))
        {
            throw new bPack_Exception('bPack_Config->setProvider: no provider were given.');
        }
        
        $this->_provider = $provider;
        
        return $this;
    }
    
    public function get($config_name)
    {
        return $this->_provider->get($config_name);
    }
    
    public function set($config_name, $config_value = null)
    {
        return $this->_provider->set($config_name, $config_value);
    }
}
