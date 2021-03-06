<?php
class bPack_View_Twig implements bPack_View_Adaptee
{
    protected $_parent;
    protected $twig;
    protected $_filename;
    protected $values = array();

	protected function loadTwig($pear = true)
	{
		$twig_location = bPack_Application_Directory . 'lib/Twig/lib/Twig/Autoloader.php';

		if(!$pear)
		{
			if(file_exists($twig_location))
			{
				require_once $twig_location;
			}
			else
			{
				throw new Exception('no twig');
			}
		}
		else
		{
			try
			{
				include_once 'Twig/Autoloader.php';
			}
			catch(Exception $e)
			{
				return $this->loadTwig(false);
			}
		}

		return true;
	}
    
    public function __construct($load_path = '')
    {
		$this->loadTwig();

		Twig_Autoloader::register();

        if($load_path == '')
        {
            $load_path = bPack_Application_Directory . 'tpl';
        }

        $loader = new Twig_Loader_Filesystem($load_path);
        
        if(defined('bPack_Debug') && bPack_Debug)
        {
            $this->twig = new Twig_Environment($loader);
        }
        else
        {
            $this->twig = new Twig_Environment($loader, array('cache' => bPack_Application_Directory . 'tmp/' ));
        }

        $this->assign('bPack_rootpath',bPack_Application_BASE_URI);
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
