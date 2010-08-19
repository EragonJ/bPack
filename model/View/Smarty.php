<?php
require_once(bPack_App_BaseDir . 'lib/Smarty/Smarty.class.php');

class bPack_View_Smarty implements bPack_View_Adaptee
{
    protected $_parent;
    protected $_smarty;
    protected $_filename;
    
    public function __construct()
    {
        $this->_smarty = new Smarty();
        $this->_smarty->template_dir = bPack_App_BaseDir . 'tpl/';
        $this->_smarty->compile_dir = bPack_App_BaseDir . 'tmp/';
        $this->_smarty->trusted_dir= bPack_App_BaseDir;
        $this->_smarty->assign('bPack_rootpath',bPack_BASE_URI);
    }
    
    public function assign($key,$value = '')
    {
        $this->_smarty->assign($key,$value);
    
        return true;
    }
    
    public function output()
    {
        if(empty($this->_filename))
        {
            throw new bPack_Exception('bPack_View_Smarty: No template file are specified to display.');
        }
    
        return $this->_smarty->fetch($this->_filename);
    }
    
    public function setFilename($filename)
    {
        $this->_filename = $filename;
    
        return true;
    }
    
    public function setParent(bPack_View $parent)
    {
        $this->_parent = $parent;
    
        return true;
    }
    
    public function getEngine()
    {
        return $this->_smarty;
    }
}
