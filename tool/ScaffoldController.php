<?php
class ScaffoldController extends ApplicationController
{
    public function __construct() 
    {
        $this->request = new bPack_Request;
        $this->response = new bPack_Response;

		$this->view = new bPack_View(new bPack_View_Twig);

        $this->plugin_add('Flash');

        $this->view->assign('flash', $this->_flash_expose());
        $this->view->assign('sitename', 'Scaffold');
    }
}
