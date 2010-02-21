<?php
class bPack_DataContainer
{
    public function __set($name,$value)
    {
        $this->{$name} = $value;
    }

    public function __get($name)
    {
        return false;
    }
}