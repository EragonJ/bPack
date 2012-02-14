<?php

class bPack_DB_ActiveRecord_Entry_Task_dynamicProp implements bPack_DB_ActiveRecord_Entry_Task
{
	public function __construct($saving_object)
	{
		$this->_schema = $saving_object->_schema;
		$this->_modelClass = $saving_object->_modelClass;
	}

	public function executeRead(array &$data)
	{
		return true;
	}

	public function executeWrite(array &$data)
	{
		$data_fields = array_keys($data);
		var_dump($data_fields);
		echo "<hr>";
		$schema_fields = array_keys($this->_schema);
		var_dump($schema_fields);

		echo "<hr>";
		$field_not_exists = array_diff($data_fields, $schema_fields);
		var_dump($field_not_exists);
		exit;

		foreach($field_not_exists as $field_name)
		{
			# if prop not in schema and exists a create/update/getter
			if( is_array($value) )
			{
				$value = $this->serializeDataToString($value);
			}
		}

		return true;
	}
}
