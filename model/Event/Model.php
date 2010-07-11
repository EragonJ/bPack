<?php

abstract class bPack_Event_Model
{
    protected $_helper = array();
    
    abstract public function defaultAction();
    abstract public function startupAction();
    abstract public function tearDownAction();
    
    protected function _registerHelper(bPack_Event_Helper $obj)
    {
        $this->_helper[$obj->name] = $obj;
        
        return true;
    }
    
    /**
     * 呼叫預設 Helper 方法時用
     */
    public function __call($name, $arguments)
    {
        try
        {
            call_user_func_array(array($this->_helper['default'],$name),$arguments);
        }
        catch (bPack_ErrorException $e)
        {
            throw new bPack_Exception('bPack_Event_Model->call: no corresponding helper function were found.');
        }
    }
    
    /**
     * 呼叫未知參數時用
     */
    public function __get($name)
    {
        if(strpos($name,'Helper') !== FALSE)
        {
            $helper_name = str_replace('Helper','',$name);
            
            if(array_key_exists($helper_name,$this->_helper))
            {
                return $this->_helper[$helper_name];    
            }
            else
            {
                throw new bPack_Exception('bPack_Event_Model->get: no corresponding helper were found.');
            }
        }
        
        throw new bPack_Exception('bPack_Event_Model->call: no corresponding attribute were found.');
    }
}