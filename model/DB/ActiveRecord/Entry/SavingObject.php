<?php

class bPack_DB_ActiveRecord_Entry_SavingObject
{
	const MODE_CREATE = 'bPack_DB_ActiveRecord_Entry_SavingObject_Create';
	const MODE_UPDATE = 'bPack_DB_ActiveRecord_Entry_SavingObject_Update';

	protected $_data = null;

	public function __construct(bPack_DB_ActiveRecord_Entry $entry_object)
	{
		$this->_schema = $entry_object->getDataObject()->getSchema();
		$this->_modelClass = $entry_object->getDataObject()->getModel();
		$this->_dataStorage = $entry_object->getDataStorage();
	}

	protected function setStoringData(array $storing_data)
	{
		if( sizeof($storing_data) == 0)
		{
			throw new ActiveRecord_Entry_NullUpdateException('No data to update');
		}

		$this->_data = $storing_data;

		return $this;
	}

	protected function registerOriginalDataPrepartionTask()
	{
		$this->addDataPrepartionTask(new bPack_DB_ActiveRecord_Entry_Task_JSON);
		$this->addDataPrepartionTask(new bPack_DB_ActiveRecord_Entry_Task_dynamicProp($this));
	}

	protected function getPreprationTasks()
	{
		return $this->_prepartionTasks;
	}

	protected function prepareData()
	{
		$this->registerOriginalDataPrepartionTask();

		foreach( $this->getPreprationTasks() as $task )
		{
			$task->executeWrite($this->_data);
		}

		return $this;
	}

	protected function addDataPrepartionTask(bPack_DB_ActiveRecord_Entry_Task $task)
	{
		$this->_prepartionTasks[] = $task;

		return true;
	}

	protected function getUpdateObject()
	{
		$primary_key_column = $this->_modelClass->extractPrimaryKey();

		try
		{
			$primary_key_value = $this->_dataStorage->get($primary_key_column);
		}
		catch(ActiveRecord_ColumnNotExistException $e)
		{
			return new bPack_DB_ActiveRecord_Entry_SavingObject_Create($this->_data, $this->_modelClass);
		}
		
		return new bPack_DB_ActiveRecord_Entry_SavingObject_Update($this->_data, $this->_modelClass, $primary_key_column, $primary_key_value);
	}
	
	// save agent, modify agent, delete agent
	protected function executePostOperatingTasks()
	{
		foreach($this->_postOperatingTask as $task)
		{
			if( ! $task->execute() )
			{
				return false;
			}
		}

		return true;
	}

	public function save()
	{
		$this->setStoringData( $this->_dataStorage->getDiff() )->prepareData();

		$saving_obj = $this->getUpdateObject();

		$saving_obj->performDataFilter();

		echo $saving_obj->execute();
		exit;

		if(!$this->executePostOperatingTasks())
		{
			$saving_obj->rollback();

			throw new ActiveRecord_Entry_SavingException("One of post-operating tasks return false, and the changes are rollbacked.");
		}

		return $saving_obj->getOperatingResult(); 
	}
}

abstract class bPack_DB_ActiveRecord_Entry_SavingObject_Method 
{
	protected function doFilter($filter_obj)
	{
		$filter_obj->setData($this->_data);
		$filter_obj->setModel($this->_model);

		return $filter_obj->execute();
	}
}

abstract class bPack_DB_ActiveRecord_Entry_Filter
{ 
	public function setData(array &$data)
	{
		$this->_data = $data;
	}

	public function setModel(bPack_DB_ActiveRecord $model)
	{
		$this->_model = $model;
	}

	protected function checkTagByCol($column, $tag)
	{
		return (! (strpos($this->_model->table_column[$column]['tag'], $tag) === FALSE) );
	}

	protected function checkTagByColValue($value, $tag)
	{
		return (! (strpos($value['tag'], $tag) === FALSE) );
	}

	abstract public function execute();
}

class bPack_DB_ActiveRecord_Entry_Filter_Everytime extends bPack_DB_ActiveRecord_Entry_Filter
{
	protected function extractEveryTimeColumn()
	{
		$field = array();

		foreach($this->_model->table_column as $field => $value)
		{
			if( $this->checkTagByColValue($value, 'update_every_time') )
			{
				continue;
			}

			$field[] = $field;
		}

		return $field;
	}

	protected function updateColumnsValue(array $columns)
	{
		foreach($columns as $column)
		{
			if( $this->checkTagByCol($column, 'current_timestamp') )
			{
				$this->_data[$column] = date('Y-m-d H:i:s');
			}
		}
	}

	public function execute()
	{
		$this->updateColumnsValue( $this->extractEveryTimeColumn() );
	}
}

class bPack_DB_ActiveRecord_Entry_SavingObject_Update extends bPack_DB_ActiveRecord_Entry_SavingObject_Method
{
	public function __construct(array $data_to_update, bPack_DB_ActiveRecord $model, $primary_key_column, $primary_key_value)
	{
		$this->_identifer_expr = $this->generateExpression($primary_key_column, $primary_key_value);
		$this->_data = $data_to_update;
		$this->_model = $model;
	}

	public function performDataFilter()
	{
		$this->doFilter(new bPack_DB_ActiveRecord_Entry_Filter_Everytime);
	}

	public function execute()
	{
		$sql = "UPDATE `" . $this->_model->getSchemaName() . "` SET " . $this->getColumnsExpr() . " WHERE " . $this->_identifer_expr;
		return $sql;
	}

	public function getColumnsExpr()
	{
		$exprs = array();

		foreach($this->_data as $field => $value)
		{
			$exprs[] = $this->generateExpression($field, $value);
		}

		return implode(",", $exprs);
	}

	protected function generateExpression($column, $value)
	{
		return " `$column` = " . $this->_model->getConnection()->quote($value);
	}
}

class bPack_DB_ActiveRecord_Entry_SavingObject_Create
{

}

class bPack_DB_ActiveRecord_Entry_SavingObject_Filter_AutofillOnCreate
{

}

class bPack_DB_ActiveRecord_Entry_SavingObject_Filter_Required
{

}

class bPack_DB_ActiveRecord_Entry_SavingObject_Filter_UpdateEveryTime
{

}

/* for no data to update */
class ActiveRecord_Entry_NullUpdateException extends ActiveRecord_EntryException {}
