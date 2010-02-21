<?php
class bPack_View_Loadfile implements bPack_View_Adaptee
{
    protected $_parent;
    protected $_filename;

    public function assign($key,$value = '')
    {
        return true;
    }

    public function output()
    {
        if(empty($this->_filename))
        {
            throw new bPack_Exception('bPack_View_Loadfile: No template file are specified to display.');
        }
        
        return file_get_contents(bPack_App_BaseDir . 'tpl/'.$this->_filename);
    }

    public function setFilename($filename)
    {
        $this->_filename = $filename;

        return true;
    }

    public function setOption($key,$value = '')
    {
        return true;
    }

    public function setParent(bPack_View $parent)
    {
        $this->_parent = $parent;

        return true;
    }
    
    public function getEngine()
    {
        return true;
    }
}