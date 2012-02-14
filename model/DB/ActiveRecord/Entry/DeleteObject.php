<?php

class bPack_DB_ActiveRecord_Entry_DeleteObject
{
	protected $_connection = null;
	protected $_storageData = null;

	protected $_schema_name = null;
	protected $_primary_key_name = null;

	public function __construct(bPack_DB_ActiveRecord_Entry $parent_object)
	{
		$this->_schema_name = $parent_object->getDataObject()->getSchemaName(); 
		$this->_primary_key_name = $parent_object->getDataObject()->getModel()->extractPrimaryKey();

		$this->_connection = $parent_object->getDataObject()->getConnection();
		$this->_storageData = $parent_object->getDataStorage();
	}

	public function delete()
	{
		$delete_statement = $this->prepareSQLStatement();

		return $delete_statement->execute(array(
			'primary_key_value' => $this->_storageData->get('id')
		));
	}

	protected function prepareSQLStatement()
	{
		return $this->_connection->prepare("DELETE FROM `{$this->_schema_name}` WHERE `{$this->_primary_key_name}` = :primary_key_value");
	}
}
