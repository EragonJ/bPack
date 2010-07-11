<?php
require(bPack_BaseDir . 'lib/sfYaml/sfYaml.php');

class bPack_Config_YAML extends bPack_Config_Provider
{
    protected $_is_writeable = false;
    private $__filename = null;
    private $__config = null;
    
    public function __construct($filename = null)
    {
        if(is_null($filename))
        {
            throw new InvalidArgumentException('bPack_Config_YAML->__construct: no filename were given for loading');
        }
        
        $this->setFilename($filename);
    }
    
    public function setFilename($filename)
    {
        if(is_null($filename))
        {
            throw new InvalidArgumentException('bPack_Config_YAML->setFilename: no filename were given for setting');
        }
        
        $this->__filename = $filename;
        
        $this->reload();
        
        return $this;
    }
    
    public function reload()
    {
        if(file_exists($this->__filename) === FALSE)
        {
            throw new bPack_Exception('bPack_Config_YAML->reload: no file were exists for loading');
        }

        $this->__config = sfYaml::load($this->__filename);
        
        return $this;
    }
    
    public function get($config_name)
    {
        if(strpos($config_name,'.') === FALSE)
        {
            if(array_key_exists($config_name, $this->__config) === FALSE)
            {
                return false;
            }
            else
            {
                return $this->__config[$config_name];
            }
        }
        else
        {
            $config_name_array = explode('.',$config_name);
            
            $eval_text = '';
            foreach($config_name_array as $slice)
            {
                $eval_text .= "['$slice']";
            }

            eval('$result = $this->__config'.$eval_text.';');
            
            return $result;
        }
    }
    
    public function set($config_name, $config_value)
    {
        
    }
}