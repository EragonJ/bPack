<?php

class bPack_DB_ActiveRecord_Entry implements ArrayAccess
{
	protected $_dataObject = null;
	protected $_dataStorage = null;

	/*
		construct 
	*/

    public function __construct(bPack_DB_ActiveRecord_DataObject $data_obj)
    {
		$this->_dataObject = $data_obj;
		$this->_dataStorage = new bPack_DB_ActiveRecord_Entry_DataStorage($data_obj);
    }

	/*
		interface
	*/

    public function destroy()
    {
		$deleteObj = new bPack_DB_ActiveRecord_Entry_DeleteObject($this);

		return $deleteObj->delete();
    }

    public function save()
    {
		$savObj = new bPack_DB_ActiveRecord_Entry_SavingObject($this);

		return $savObj->save();
	}

    public function __set($attribute_name, $value)
    {
		return $this->_dataStorage->set($attribute_name, $value);
    }

    public function __get($attribute_name)
    {
		return $this->_dataStorage->get($attribute_name);
    }

	/* 
		method of ArrryAccess
	*/

	public function offsetExists($offset)
	{
		return $this->_dataStorage->checkExistence($offset);
	}

	public function offsetGet($offset)
	{
		return $this->_dataStorage->get($offset);
	}

	public function offsetSet($offset, $value)
	{
		return $this->_dataStorage->set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->_dataStorage->remove($offset);
	}

	/*
		helper function 
	*/

	public function getDataObject()
	{
		return $this->_dataObject;
	}

	public function getDataStorage()
	{
		return $this->_dataStorage;
	}
}

/* 
	for exception throw by entry classes 
*/
class ActiveRecord_EntryException extends ActiveRecord_Exception {}
