<?php

class Controller_%module%_%controller% extends ScaffoldController 
{
	public function startupAction()
	{
		$this->%lowercase_model% = new Model_%model%;
	}

	public function defaultAction()
	{
		$this->index();
	}

	public function index()
	{
		$%short%_list = $this->%lowercase_model%->retrieve_all_entries();

		$this->view->assign('%shorts%', $%short%_list);

		$this->view->output('%module%/%controller%/list.html');
	}

	public function create()
	{
		if($this->request->isPostback())
		{
			return $this->create_postback();
		}

		$this->view->output('%module%/%controller%/create.html');
	}

	protected function create_postback()
	{
		$%short%_entity = $this->%lowercase_model%->create_new_entry();

		$user_input_data = $this->request->postdata;

		try
		{
			foreach($user_input_data as $key => $value)
			{
				$%short%_entity->{$key} = $value;
			}

			$%short%_entity->save();

			$this->_flash_msg('New %shorts% created.');
		}
		catch(Exception $e)
		{
			$this->_flash_msg('Problem occured when writing into database. ('.$e->getMessage().')', 'error');
		}
		
		$this->response->go('%module%.%controller%');
	}

	public function modify()
	{
		if($this->request->isPostBack())
		{
			return $this->modify_postback();
		}

		$%short%_id = $this->request->get('id', 0);

		try
		{
			$%short%_data = $this->%lowercase_model%->find_by_id($%short%_id);
		}
		catch(Exception $e)
		{
			$this->_flash_msg('No data found', 'error');
			$this->response->go('%module%.%controller%');
		}

		$this->view->assign('%short%', $%short%_data);

		$this->view->output('%module%/%controller%/modify.html');
	}

	public function modify_postback()
	{
		$%short%_id = $this->request->get('id', 0);

		$%short%_entity = $this->%lowercase_model%->find_by_id($%short%_id);

		$modified_data = $this->request->postdata;

		try
		{
			foreach($modified_data as $key=>$value)
			{
				$%short%_entity->{$key} = $value;
			}

			$%short%_entity->save();

			$this->_flash_msg('Modification had been made.', 'success');
		}
		catch(ActiveRecord_NullUpdate $e)
		{
			$this->_flash_msg('There was nothing to update.');
		}
		catch(Exception $e)
		{
			$this->_flash_msg('Problem occured when writing into database. ('.$e->getMessage().')', 'error');
		}

		$this->response->go('%module%.%controller%');
	}

	public function remove()
	{
		$%short%_id = $this->request->get('id', 0);
		$this->%lowercase_model%->find_by_id($%short%_id)->destroy();
	
		$this->_flash_msg('Specific %lowercase_model% had removed.', 'success');
		$this->response->go('%module%.%controller%');
	}
}
