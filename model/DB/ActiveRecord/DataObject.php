<?php

class bPack_DB_ActiveRecord_DataObject
{
	/* for: database connection */
	protected $_connection = null;

	/* for: access */
	protected $_model_class = null;

	/* for: schema check */
	protected $_schema_name = null;
	protected $_schema = null;

	/* for: type */
	protected $_type = null;

	/* for: collection */
	protected $_condition = null;

	/* for: developer access */
	protected $_row_data = null;

	public function __construct($type = bPack_DB_ActiveRecord::FetchOne)
	{
		$this->_type = $type;
	}

	public function setConnection($connection)
	{
		$this->_connection = $connection;
		return $this;
	}

	public function setModel(bPack_DB_ActiveRecord $model_class)
	{
		$this->_model_class = $model_class;
		return $this;
	}

	public function setSchemaName($schema_name)
	{
		$this->_schema_name = $schema_name;
		return $this;
	}

	public function setSchema(array $schema)
	{
		$this->_schema = $schema;
		return $this;
	}

	public function setData($data)
	{
		if($this->_type == bPack_DB_ActiveRecord::FetchAll)
		{
			throw new ActiveRecord_Exception('DataObject for Collections cannot filled with data');
		}

		$this->_row_data = $data;
		return $this;
	}

	public function setCondition($value)
	{
		if($this->_type == bPack_DB_ActiveRecord::FetchOne)
		{
			throw new ActiveRecord_Exception('DataObject for Entry cannot filled with condition, give data array instead');
		}

		$this->_condition = $value;
	}

	public function getCondition()
	{
		return $this->_condition;
	}

	public function getData()
	{
		return $this->_row_data;
	}

	public function getSchemaName()
	{
		return $this->_schema_name;
	}

	public function getSchema()
	{
		return $this->_schema;
	}

	public function getModel()
	{
		return $this->_model_class;
	}

	public function getConnection()
	{
		return $this->_connection;
	}

	public function hasData()
	{
		return !is_null($this->_row_data);
	}

	public function hasCondition()
	{
		return !is_null($this->_condition);
	}
}
