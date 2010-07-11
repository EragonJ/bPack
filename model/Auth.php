<?php
abstract class bPack_Auth
{
    public $_logonManager = null;
    public $_storage_bin = null;
    public $_storage_name = '';
    
    final public function __construct($storage_bin, $storage_name = 'Auth')
    {
        $this->_storage_bin = $storage_bin;
        $this->_storage_name = $storage_name;
        $this->_setLogonManager();
    }
    
    public function getUserID()
    {
        $data = $this->getStorageData();
        
        return $data['id'];
    }
    
    public function getStorageData()
    {
        return $this->_storage_bin->get($this->_storage_name);
    }
    
    public function setStorageData($data)
    {
        $this->_storage_bin->set($this->_storage_name, $data);
        
        return true;
    }
    
    abstract protected function _setLogonManager();
}

abstract class bPack_Auth_LogonManager
{
    protected $_parent_obj = null;
    
    public function __construct($parent_obj)
    {
        $this->_parent_obj = $parent_obj;
    }
    
    public function logout()
    {
        $this->_parent_obj->setStorageData(null);

        return true;
    }
    
    public function isLogged()
    {
        if($this->_parent_obj->getStorageData() !== FALSE)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    abstract public function login($username, $password);
}

