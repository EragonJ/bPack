<?php
/**
 * Creating object that stores key-value data 
 * for passing though bPack Modules
 * 
 * @author bu <bu@hax4.in>
 * @package bPack
 * @subpackage DataContainer
 * 
 * @todo implements Iterator, Countable, ArrayAccess
 */

class bPack_DataContainer
{
    /**
     * The stored data
     *
     * @access protected
     * @var array
     */
    protected $data = array();

    /**
     * Setter method
     * 
     * @param string $name the key of the value
     * @param mixed $value the value being stored
     */
    public function __set($name,$value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Getter method
     *
     * return false if the key haven't been set; otherwise, return value
     * 
     * @param string $name the key-name of the request value
     * @return mixed
     */
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
    
    /**
     * Dump the stored data
     *
     * @param void
     * @return array
     */
    public function getStoredData()
    {
        return $this->data;
    }
}
