<?php

require_once '../../model/ErrorHandler.php';
require_once '../../model/DB/ActiveRecord.php';
require_once '../../model/DB/ActiveRecord/DataObject.php';
require_once '../../model/DB/ActiveRecord/Collection.php';
require_once '../../model/DB/ActiveRecord/Entry.php';

require_once 'ActiveRecord_TestCommon.php';

class ActiveRecord_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_model = new TestModel;
		$this->_dummyDB = new DummyDatabase;

		$this->_model->setDatabase($this->_dummyDB);
	}

	public function testFirst()
	{
		$entry = $this->_model->first();
		
		$this->assertEquals('SELECT `id`, `text` FROM `table` LIMIT 1;', $this->_dummyDB->getLastSQL());

		$this->assertInstanceOf('bPack_DB_ActiveRecord_Entry', $entry);
	}

	public function testLast()
	{
		$entry = $this->_model->last();

		$this->assertEquals('SELECT `id`, `text` FROM `table` ORDER BY `id` DESC LIMIT 1;', $this->_dummyDB->getLastSQL());

		$this->assertInstanceOf('bPack_DB_ActiveRecord_Entry', $entry);
	}

	public function testFindBy()
	{
		$entry = $this->_model->find_by_id(1);

		$this->assertEquals("SELECT `id`, `text` FROM `table` WHERE `id` = '1';", $this->_dummyDB->getLastSQL());

		$this->assertInstanceOf('bPack_DB_ActiveRecord_Entry', $entry);
	}

	public function testFindAllBy()
	{
		$dataset = $this->_model->find_all_by_text('Dance');

		$this->assertInstanceOf('bPack_DB_ActiveRecord_Collection', $dataset);

		$this->assertEquals("`text` = 'Dance'", $dataset->getCondition());
	}

	/**
	 * @expectedException ActiveRecord_ColumnNotExistException
	 */
	public function testFindAllByNonExistColumn()
	{
		$dataset = $this->_model->find_all_by_foo('Dance');
	}

	public function testCreateNewEntry()
	{
		$this->assertInstanceOf('bPack_DB_ActiveRecord_Entry', $this->_model->create_new_entry());
	}

	public function testRetreieveAllEntries()
	{
		$this->assertInstanceOf('bPack_DB_ActiveRecord_Collection', $this->_model->retrieve_all_entries());

		$this->assertEmpty($this->_dummyDB->getLastSQL());
	} 

	public function tearDown()
	{
		$this->_model = null;
		$this->_dummyDB = null;
	}
}

