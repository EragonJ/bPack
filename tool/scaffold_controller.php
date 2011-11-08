<?php

class Controller_%module%_%controller% extends ScaffoldController 
{
	public function startupAction()
	{
		$this->%lowercase_models% = new Model_%model_name%;
	}

	public function defaultAction()
	{
		$this->index();
	}

	public function view()
	{
		$%lowercase_model%_id = $this->request->get('id', 0);

		$%lowercase_model%_data = $this->%lowercase_models%->find_by_id($%lowercase_model%_id);

		$this->view->assign('%lowercase_model%data', $%lowercase_model%_data);
		
		$this->view->assign('list_url', $this->response->get_internal_link('%module%.%controller%/index'));

		$this->view->output('%module%/%controller%/view.html');
	}

	public function index()
	{
		$%lowercase_models% = $this->%lowercase_models%->retrieve_all_entries();

		$this->view->assign('%lowercase_models%', $%lowercase_models%);

		$this->view->assign('create_link', $this->response->get_internal_link('%module%.%controller%/create'));
		$this->view->assign('delete_link', $this->response->get_internal_link('%module%.%controller%/remove?id=ID'));
		$this->view->assign('modify_link', $this->response->get_internal_link('%module%.%controller%/modify?id=ID'));
		$this->view->assign('view_link', $this->response->get_internal_link('%module%.%controller%/view?id=ID'));

		$this->view->output('%module%/%controller%/list.html');
	}

	public function create()
	{
		if($this->request->isPostback())
		{
			$this->create_postback();

			return;
		}

		$this->view->assign('create_form_url', $this->response->get_internal_link('%module%.%controller%/create'));
		$this->view->assign('list_url', $this->response->get_internal_link('%module%.%controller%/index'));

		$this->view->output('%module%/%controller%/create.html');
	}

	protected function create_postback()
	{
		$%lowercase_model%_entity = $this->%lowercase_models%->create_new_entry();

		$user_input_%lowercase_model%data = $this->request->postdata;

		try
		{
			foreach($user_input_%lowercase_model%data as $key => $value)
			{
				$%lowercase_model%_entity->{$key} = $value;
			}
		}
		catch(Exception $e)
		{
			$this->_flash_msg('Some problem were occured when writing data into database. ('.$e->getMessage().')', 'error');
			$this->response->goBack();
		}
		
		/* if everything goes right, and then we save it into database. */
		$%lowercase_model%_entity->save();

		/* return list page and show message */
		$this->_flash_msg('New %lowercase_model% had been created.');
		$this->response->go('%module%.%controller%/index');

	}

	public function modify()
	{
		if($this->request->isPostBack())
		{
			$this->modify_postback();

			return;
		}

		$%lowercase_model%_id = $this->request->get('id', 0);

		try
		{
			$%lowercase_model%_data = $this->%lowercase_models%->find_by_id($%lowercase_model%_id);
		}
		catch(Exception $e)
		{
			$this->_flash_msg('No data found', 'error');
			$this->response->go('%module%.%controller%/index');

		}

		$this->view->assign('%lowercase_model%data', $%lowercase_model%_data);

		$this->view->assign('modify_form_url', $this->response->get_internal_link('%module%.%controller%/modify?id=ID'));
		$this->view->assign('list_url', $this->response->get_internal_link('%module%.%controller%/index'));

		$this->view->output('%module%/%controller%/modify.html');
	}

	public function modify_postback()
	{
		$%lowercase_model%_id = $this->request->get('id', 0);

		$%lowercase_model%_entity = $this->%lowercase_models%->find_by_id($%lowercase_model%_id);

		$modified_%lowercase_model%data = $this->request->postdata;

		try
		{
			foreach($modified_%lowercase_model%data as $key => $value)
			{
				$%lowercase_model%_entity->{$key} = $value;
			}
		}
		catch(Exception $e)
		{
			$this->_flash_msg('Some problem were occured when writing data into database. ('.$e->getMessage().')', 'error');
			$this->response->goBack();
		}
		
		/* if everything goes right, and then we save it into database. */
		$%lowercase_model%_entity->save();

		/* return list page and show message */
		$this->_flash_msg('Modification had been made.');
		$this->response->go('%module%.%controller%/index');
	}

	public function remove()
	{
		/* try to find %lowercase_model% by given ID and delete it. */
		$%lowercase_model%_id = $this->request->get('id', 0);
		$this->%lowercase_models%->find_by_id($%lowercase_model%_id)->destroy();
	
		/* return list page and show message */
		$this->_flash_msg("%lowercase_model% had been removed.");
		$this->response->go('%module%.%controller%/index');
	}
}
