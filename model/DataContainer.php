<?php
class bPack_DataContainer
{
    protected $data = array();

    public function __set($name,$value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if(isset($this->data[$name]))
        {
            return $this->data[$name];
        }
        else
        {
            return false;
        }
    }

    public function getStoredData()
    {
        return $this->data;
    }
}
