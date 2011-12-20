<?php

class Plugin_TwigHelper extends bPack_Event_Plugin implements Twig_ExtensionInterface
{
    protected function pluginInitialization()
    {
		return;
    }

	public function get_internal_link($path = '')
	{
		return $this->parent->response->get_internal_link($path);
	}

	public function javascript_include_tag($filename = '')
	{
		return '<script type="text/javascript" src="' . bPack_Application_BASE_URI . 'public/js/' . $filename . '.js"></script>';
	}

	public function stylesheet_link_tag($filename = '')
	{
		return '<link rel="stylesheet" type="text/css" href="' . bPack_Application_BASE_URI . 'public/css/' . $filename . '.css" />';
	}

    protected function registerFunctions()
    {
		$this->parent->view->getEngine()->addExtension($this);
    }

    public function initRuntime(Twig_Environment $environment) { return; }
    public function getTokenParsers() { return array(); }
    public function getNodeVisitors() { return array(); }
    
	public function getFilters() 
	{
		return array('nl2br' => new Twig_Filter_Function('nl2br', array('is_safe' => array('html'))));
	}
    
	public function getTests() { return array(); }
    
	public function getFunctions() 
	{ 
		return array(
			'internal_link' => new Twig_Function_Method($this,'get_internal_link', array('is_safe' => array('html'))),
			'javascript_include_tag' => new Twig_Function_Method($this,'javascript_include_tag', array('is_safe' => array('html'))),
			'stylesheet_link_tag' => new Twig_Function_Method($this,'stylesheet_link_tag', array('is_safe' => array('html')))
		);
	}

    public function getOperators() { return array(); }
    public function getGlobals() { return array(); }
    public function getName() { return 'TwigURL'; }
}
