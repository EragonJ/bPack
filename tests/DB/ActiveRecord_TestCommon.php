<?php

class DummyDatabase 
{
	protected $_last_sql = '';

	public function getEngine()
	{
		return $this;
	}

	public function query($sql)
	{
		$this->_last_sql = $sql;

		return new DummyDatabase_Statement($sql);
	}

	public function exec($sql)
	{
		return $this->query($sql);
	}
	
	public function getLastSQL()
	{
		return $this->_last_sql;
	}

	public function quote($string)
	{
		return "'$string'";
	}
}

class DummyDatabase_Statement
{
	public function __construct($sql = '')
	{
		$this->_sql = $sql;
	}

	public function fetch()
	{
		if($this->_sql == "SELECT `id`, `text` FROM `table` WHERE `id` > 1;")
		{
			return array(
				array('id' => 2, 'text' => 'test2'),
				array('id' => 3, 'text' => 'test3')
			);
		}
		
		return array('id' => 1, 'text' => 'test');
	}

	public function fetchAll()
	{
		$this->_sql = "SELECT `id`, `text` FROM `table` WHERE `id` > 1;";
		$this->fetch();
	}
}

class TestModel extends bPack_DB_ActiveRecord
{
	protected $table_name = 'table';

	protected $table_column = array(
		'id' => array('tag'=>'primary_key', 'type'=>'int(20)'),
		'text' => array('tag'=>'required', 'type'=>'text')
	);
}
