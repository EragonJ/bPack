<?php

class bPack_DB_ActiveRecord_Entry_DataStorage
{
	protected $_modelClass = null;
	protected $_newData = array();
	protected $_originalData = array();

	protected $_prepartionTasks = array();

	public function __construct(bPack_DB_ActiveRecord_DataObject $dataObject)
	{
		$this->_modelClass = $dataObject->getModel();
		$this->_originalData = $dataObject->hasData() ? $dataObject->getData() : array();

		$this->prepareOriginalData();
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __set($key, $value)
	{
		return $this->set($key, $value);
	}

	public function remove($key)
	{
		if( $this->exists_In_newData($key) )
		{
			unset($this->_newData[$key]);

			return true;
		}

		return false;
	}

	public function set($key, $value)
	{
		$this->_newData[$key] = $value;

		return $this;
	}

	public function get($key)
	{
		/* if request key is in schema column list */
		if( $this->exists_In_Schema($key) )
		{
			/* if value had been modified */
			if( $this->exists_In_newData($key) )
			{
				return $this->getNewData($key);
			}

			/* if not given new value, is original data has value of this key? */
			if( $this->exists_In_originalData($key) )
			{
				return $this->getOriginalData($key);
			}
		}

		/* 
			if not exists in schema, is this value settled by developer? 
			(as a template addon infor, or so)
		*/
		if( $this->exists_In_newData($key) )
		{
			return $this->getNewData($key);
		}

		/* if not exists in new data, and not in schema(original data) */
		/* such as when primary key not id, we figure out it as primary key */ 
		if( $key == 'id' && $this->exists_In_originalData( $this->_modelClass->getPrimaryKeyName() ) )
		{
			return $this->getOriginalData( $this->_modelClass->getPrimaryKeyName() );
		}

		/* if not id(implict primary key), but exists a getter in model class */
		if( $this->getterExists_In_ModelClass($key) )
		{
			return $this->_modelClass->{ $this->getGetterName($key) }($this);
		}

		/* 
			if not in schema, not in newdata, not imply primary key, getter is not set,
			the last thing we can do is to throw a exception to tell developer.
		*/
		throw new ActiveRecord_ColumnNotExistException("Column[$key] required is not exists .");
	}

	public function checkExistence($key)
	{
		return $this->exists_In_originalData($key) || $this->exists_In_newData($key);
	}

	public function getDiff()
	{
		return array_diff_assoc($this->_originalData, $this->_newData);
	}
	

	protected function exists_In_Schema($key)
	{
		return array_key_exists($key, $this->_modelClass->getSchema());
	}

	protected function exists_In_newData($key)
	{
		return isset($this->_newData[$key]);
	}

	protected function exists_In_originalData($key)
	{
		return isset($this->_originalData[$key]);
	}

	protected function getOriginalData($key)
	{
		if($this->exists_In_originalData($key))
		{
			return $this->_originalData[$key];
		}
		else
		{
			throw new ActiveRecord_EntryException('Data not xist.');
		}
	}

	protected function getNewData($key)
	{
		if($this->exists_In_newData($key))
		{
			return $this->_newData[$key];
		}
		else
		{
			throw new ActiveRecord_EntryException('Data not xist.');
		}

	}

	protected function getGetterName($key)
	{
		return 'get_' . ucfirst($key);
	}

	protected function getterExists_In_ModelClass($key)
	{
		return method_exists( $this->_modelClass, $this->getGetterName($key) );
	}

	protected function registerOriginalDataPrepartionTask()
	{
		$this->addDataPrepartionTask(new bPack_DB_ActiveRecord_Entry_Task_JSON);
	}

	protected function getPreprationTasks()
	{
		return $this->_prepartionTasks;
	}

	protected function prepareOriginalData()
	{
		$this->registerOriginalDataPrepartionTask();

		foreach( $this->getPreprationTasks() as $task )
		{
			$task->executeRead($this->_originalData);
		}

		return true;
	}

	protected function addDataPrepartionTask(bPack_DB_ActiveRecord_Entry_Task $task)
	{
		$this->_prepartionTasks[] = $task;

		return true;
	}
}
