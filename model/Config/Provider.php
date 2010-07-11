<?php
/**
 * bPack_Config_Provider is a abstract class which allows you to setup any form
 * of config source to bPack_Config
 *
 * @author bu <bu@hax4.in>
 * 
 * @package bPack
 * @subpackage bPack_Config
 */

abstract class bPack_Config_Provider
{    
    public function isWriteable()
    {
        return $this->_is_writeable;
    }
}