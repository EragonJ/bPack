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

	/* for: developer access */
	protected $_row_data = null;

	public function setConnection(PDO $connection)
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
		$this->_row_data = $data;
		return $this;
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

	public function hasData()
	{
		return !is_null($this->_row_data);
	}
}
