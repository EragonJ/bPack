<?php
require_once bPack_App_BaseDir . 'lib/Twig/Autoloader.php';
Twig_Autoloader::register();
 
class bPack_View_Twig implements bPack_View_Adaptee
{
    protected $_parent;
    protected $twig;
    protected $_filename;
    protected $values = array();
    
    public function __construct()
    {
        $loader = new Twig_Loader_Filesystem(bPack_App_BaseDir . 'tpl');
        $this->twig = new Twig_Environment($loader, array(
          'cache' => bPack_App_BaseDir . 'tmp/',
        ));

        $this->assign('bPack_rootpath',bPack_BASE_URI);
    }
    
    public function assign($key,$value = '')
    {
        $this->values[$key] = $value;

        return true;
    }
    
    public function output()
    {
        if(empty($this->_filename))
        {
            throw new bPack_Exception('bPack_View_Twig: No template file are specified to display.');
        }

        $tpl= $this->twig->loadTemplate($this->_filename);
    
        return $tpl->render($this->values);
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
        return $this->twig;
    }
}
