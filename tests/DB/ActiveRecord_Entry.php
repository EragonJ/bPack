<?php

require_once '../../model/ErrorHandler.php';
require_once '../../model/DB/ActiveRecord.php';

require_once 'ActiveRecord_TestCommon.php';

require_once '../../model/DB/ActiveRecord/Entry.php';
require_once '../../model/DB/ActiveRecord/Entry/Task.php';
require_once '../../model/DB/ActiveRecord/Entry/Task/JSON.php';
require_once '../../model/DB/ActiveRecord/Entry/DataStorage.php';

class ActiveRecordEntry_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_dataObject = new bPack_DB_ActiveRecord_DataObject;
	}

	public function testCreateEntryWithoutData()
	{
		$model = new TestModel;

		// this should be ok and should be a bPack_DB_ActiveRecord_Entry
		$this->_dataObject
			->setConnection(new DummyDatabase)
			->setModel($model)
			->setSchemaName($model->getSchemaName())
			->setSchema($model->getSchema())
			->setData(null);

		$this->_model = new bPack_DB_ActiveRecord_Entry($this->_dataObject);

		$this->assertInstanceOf('bPack_DB_ActiveRecord_Entry', $this->_model);
	}

	public function testCreateWithData()
	{
		$model = new TestModel;

		$raw_data = array('text' => 'test', 'id'=> 1);

		// this should be ok and should be a bPack_DB_ActiveRecord_Entry
		$this->_dataObject
			->setConnection(new DummyDatabase)
			->setModel($model)
			->setSchemaName($model->getSchemaName())
			->setSchema($model->getSchema())
			->setData($raw_data);

		$this->_model = new bPack_DB_ActiveRecord_Entry($this->_dataObject);

		$this->assertInstanceOf('bPack_DB_ActiveRecord_Entry', $this->_model);
	}

	public function testGetData()
	{
		// let us create some data
		$this->testCreateWithData();
		// test with model get prop
		$this->assertEquals('test', $this->_model->text);
		// test with offset get
		$this->assertEquals(1, $this->_model['id']);
	}

	public function testSetData()
	{
		// file data
		$this->testCreateWithData();

		// if we override data, then we should get new data instead of old one
		$this->_model->text = "happy testing";
		$this->assertEquals('happy testing', $this->_model->text);

		// if non exists, should let us write in but not store at db
		$this->_model->happy_ending = true;
		$this->assertTrue($this->_model->happy_ending);

		// testing offset set
		$this->_model['testing'] = 1;
		$this->assertEquals(1, $this->_model->testing);
	}

	public function tearDown()
	{
		$this->_model = null;
		$this->_dataObject = null;
	}
}
