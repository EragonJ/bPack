<?php

require_once '../../model/ErrorHandler.php';
require_once '../../model/DB/ActiveRecord.php';
require_once '../../model/DB/ActiveRecord/DataObject.php';
require_once '../../model/DB/ActiveRecord/Collection.php';

require_once 'ActiveRecord_TestCommon.php';

class ActiveRecordCollection_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_dataObject = new bPack_DB_ActiveRecord_DataObject(bPack_DB_ActiveRecord::FetchAll);
		$this->_database = new DummyDatabase;
		
		$model = new TestModel;

		$this->_dataObject
			->setConnection($this->_database)
			->setModel($model)
			->setSchema($model->getSchema())
			->setSchemaName($model->getSchemaName());
	}
	
	public function testCreateWithNoCondition()
	{
		$this->_collection = new bPack_DB_ActiveRecord_Collection($this->_dataObject);

		$this->assertInstanceOf('bPack_DB_ActiveRecord_Collection', $this->_collection);
	}

	public function testCreateWithCondition()
	{
		$this->_dataObject->setCondition('WHERE `id` > 1');

		$this->_collection = new bPack_DB_ActiveRecord_Collection($this->_dataObject);

		$this->assertInstanceOf('bPack_DB_ActiveRecord_Collection', $this->_collection);
	}

	public function testDestroy()
	{
		$this->testCreateWithCondition();

		$this->_collection->destroy();
		$this->assertEquals("DELETE FROM `table` WHERE `id` > 1;", $this->_database->getLastSQL());
	}

	public function testCountable()
	{
		$this->testCreateWithCondition();

		// test of countable, and envoke data generate
		$data = sizeof($this->_collection);
	}

	public function testSetLimitOnly()
	{
		$this->testCreateWithCondition();

		$this->_collection->limit(3)->refresh();

		$this->assertEquals('SELECT `id`, `text` FROM `table` WHERE `id` > 1 LIMIT 3;', $this->_database->getLastSQL());
	}

	public function testSetOffsetOnly()
	{
		$this->testCreateWithCondition();
		
		$this->_collection->offset(3)->refresh();

		$this->assertEquals('SELECT `id`, `text` FROM `table` WHERE `id` > 1 OFFSET 3;', $this->_database->getLastSQL());
	}

	public function testSetLimitAndOffset()
	{
		$this->testCreateWithCondition();

		$this->_collection->limit(5)->offset(3)->refresh();

		$this->assertEquals('SELECT `id`, `text` FROM `table` WHERE `id` > 1 LIMIT 3, 5;', $this->_database->getLastSQL());
	}

	public function testOrderBy()
	{
		$this->testCreateWithCondition();

		$this->_collection->orderBy('id', 'DESC')->refresh();

		$this->assertEquals('SELECT `id`, `text` FROM `table` WHERE `id` > 1 ORDER BY `id` DESC;', $this->_database->getLastSQL());
	}

	public function testGroupBy()
	{
		$this->testCreateWithCondition();

		$this->_collection->group_by('id')->refresh();

		$this->assertEquals('SELECT `id`, `text` FROM `table` WHERE `id` > 1 GROUP BY `id`;', $this->_database->getLastSQL());
	}

	/**
	 * @expectedException ActiveRecord_Collection_HavingWithoutGroupByException
	 */
	public function testHavingWithoutGroupBy()
	{
		$this->testCreateWithCondition();

		$this->_collection->having_id(5)->refresh();
	}

	public function testHavingWithGroupBy()
	{
		$this->testCreateWithCondition();

		$this->_collection->group_by('id')->having_text("1")->refresh();

		$this->assertEquals("SELECT `id`, `text` FROM `table` WHERE `id` > 1 GROUP BY `id` HAVING `text` = '1';", $this->_database->getLastSQL());
	}




	public function tearDown()
	{
		$this->_collection = null;
		$this->_database = null;
		$this->_dataObject = null;
	}
}
