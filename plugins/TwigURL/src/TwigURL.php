<?php

class Plugin_TwigURL extends bPack_Event_Plugin implements Twig_ExtensionInterface
{
    protected function pluginInitialization()
    {
		return;
    }

	public function get_internal_link($path = '')
	{
		return $this->parent->response->get_internal_link($path);
	}

    protected function registerFunctions()
    {
		$this->parent->view->getEngine()->addExtension($this);
    }

    public function initRuntime(Twig_Environment $environment) { return; }
    public function getTokenParsers() { return array(); }
    public function getNodeVisitors() { return array(); }
    public function getFilters() { return array(); }
    public function getTests() { return array(); }
    
	public function getFunctions() 
	{ 
		return array(
			'internal_link' => new Twig_Function_Method($this,'get_internal_link')
		);
	}

    public function getOperators() { return array(); }
    public function getGlobals() { return array(); }
    public function getName() { return 'TwigURL'; }
}
