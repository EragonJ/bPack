<?php

abstract class bPack_DB_ActiveRecord
{
	/* cache */
	protected $_cache = array();

	/* columns cahce */
	protected $_type_columns = array();
	protected $_tag_columns = array();
	protected $_columns = array();

	/* database connection */
	protected $_connection = null;

	/* this should be overwrite by developer */
	protected $table_column = null;
	protected $table_name = null;

	/* for objects */
    const FetchAll = 1;
    const FetchOne = 2;

	/* 
		interface 
	*/

    public function __construct()
    {
		$this->_prepareColumnCache();
    }

    public function create_new_entry()
    {
        return $this->_generateEntry();
    }

    public function retrieve_all_entries()
    {
        return $this->_generateCollectionBy( $this->_getPrimaryKey(), null );
    }

    public function first()
    {
        $sql = "SELECT {$this->_getColumnListing()} FROM `{$this->table_name}` LIMIT 1;";

		return $this->_getQueryResult($sql);
    }

    public function last()
    {
        $sql = "SELECT {$this->_getColumnListing()} FROM `{$this->table_name}` ORDER BY `".$this->_getPrimaryKey()."` DESC LIMIT 1;";

		return $this->_getQueryResult($sql);
    }

	public function getSchemaName()
	{
		return $this->table_name;
	}

	public function getSchema()
	{
		return $this->table_column;
	}

	public function setDatabase($obj)
	{
		$this->_connection = $obj;

		return true;
	}

    public function __call($function_name, $attributes)
    {
        if(strpos($function_name, 'find_by_') !== FALSE)
        {
            $column_name = str_replace('find_by_', '', $function_name);
			
			/* when call id, we think it called primary key */
			if($column_name == 'id')
			{
				$column_name = $this->_getPrimaryKey();
			}

            return $this->_generateEntryBy($column_name, $attributes);
        }
        
        if(strpos($function_name,'find_all_by_') !== FALSE)
        {
            $column_name = str_replace('find_all_by_', '', $function_name);

			/* when call id, we think it called primary key */
			if($column_name == 'id')
			{
				$column_name = $this->_getPrimaryKey();
			}

            if(strpos($column_name,'_with_'))
            {
                $column = substr($column_name, 0, strpos($column_name, '_'));
                $column_condtion = substr($column_name, strpos($column_name, '_with_') + 6, strlen($column_name) - strlen($column));
                
                $entry_data = $this->_generateEntryBy($column_condtion, $attributes);

                return $this->_generateCollectionBy($column, $entry_data->id);
            }

            return $this->_generateCollectionBy($column_name, $attributes);
        }

        throw new bPack_Exception("ActiveRecord: No corresponding method exists. (requested: $function_name)");
    }

	public function __get($name)
	{
		if(substr($name, 0, 1) == '_')
		{
			$column_name = substr($name, 1, strlen($name) - 1);

			if(in_array($column_name, $this->_columns))
			{
				return $column_name;
			}

			if($column_name == 'id')
			{
				return $this->_getPrimaryKey();
			}

			throw new ActiveRecord_ColumnNotExistException('not found column ' . $column_name);
		}

		throw new ActiveRecord_Exception('not found ' . $name);
	}

	/*
		helper
	*/

	protected function _getPrimaryKey()
	{
		$primary = $this->_getTagColumns('primary_key');

		return $primary[0];
	}

	protected function _getConnection()
	{
		if(is_null($this->_connection))
		{
			$this->_connection = bPack_DB::getInstance();
		}

		return $this->_connection;
	}

	protected function _prepareColumnCache()
	{
		if(is_null($this->table_column))
		{
			throw new ActiveRecord_Exception('Table column not overwrite, empty schema.');
		}
		
		// column list
		$this->_columns = array_keys($this->table_column);

		// tag or type list
		foreach($this->table_column as $column => $value)
		{
			if( isset($value['tag']) )
			{
				$this->_proceedTag($column, $value['tag']);
			}

			if( isset($value['type']) )
			{
				$this->_proceedType($column, $value['type']);
			}
		}
			
		return true;
	}

	protected function _proceedTag($column, $tag_string)
	{
		$tags = explode(' ', $tag_string);

		foreach($tags as $tag)
		{
			$this->_tag_columns[$tag][] = $column;
		}

		return true;
	}

	protected function _proceedType($column, $type)
	{
		$this->_type_columns[$type][] = $column;

		return true;
	}

	protected function _getTagColumns($tag_name)
	{
		if( !isset($this->_tag_columns[$tag_name]) )
		{
			return array();
		}

		return $this->_tag_columns[$tag_name];
	}

	protected function _getTypeColumns($type_name)
	{
		if( !isset($this->_type_columns[$type_name]) )
		{
			return array();
		}

		return $this->_type_columns[$type_name];
	}

	protected function _getColumnListing()
	{
		$columns = $this->table_column;

		foreach($this->_getTypeColumns('virtual') as $column)
		{
			unset($columns[$column]);
		}

		return '`' . implode('`, `', array_keys($columns)) . '`';
	}

	protected function _getQueryResult($sql = '')
	{
		if ( $sql == '')
		{
			throw new ActiveRecord_Exception("using First/Last function without sql");
		}

		$hash = sha1($sql);

		if(isset($this->_cache[$hash]))
		{
			return $this->_generateEntry($this->_cache[$hash]);
		}

        $rs = $this->_getConnection()->query($sql);
		
		if($rs !== FALSE)
		{
			$data = $rs->fetch(PDO::FETCH_ASSOC);

			$this->_cache[$hash] = $data;

			return $this->_generateEntry($data);
		}

		throw new ActiveRecord_Exception("Database connection failed.");
	}

    protected function _generateCollectionBy($column, $value)
    {
        if(!in_array($column, $this->_columns))
        {
            throw new ActiveRecord_ColumnNotExistException("Requested column [ $column ] was't in the defination.");
        }

        $value_sql = $this->_generateValueExpr($column, $value);

		$dataObject = new bPack_DB_ActiveRecord_DataObject(self::FetchAll);

		$dataObject
			->setConnection( $this->_getConnection() )
			->setModel($this)
			->setSchemaName($this->table_name)
			->setSchema($this->table_column)
			->setCondition($value_sql);

		return new bPack_DB_ActiveRecord_Collection($dataObject);
	}

    protected function _generateEntryBy($column, $value)
    {
        if(!in_array($column, $this->_columns))
        {
            throw new ActiveRecord_ColumnNotExistException("Requested column [ $column ] was't in the defination.");
        }

        $value_sql = $this->_generateValueExpr($column, $value);

		if($value_sql != '')
		{
			$value_sql = "WHERE $value_sql";
		}

        $sql = "SELECT ".$this->_getColumnListing()." FROM `{$this->table_name}` {$value_sql};";

		return $this->_getQueryResult($sql);
    }

	protected function _generateEntry($data = null)
	{
		if($data === FALSE)
        {
            throw new ActiveRecord_RecordNotExistException("ActiveRecord: requested condition had found no data.");
        }

		$dataObject = new bPack_DB_ActiveRecord_DataObject(self::FetchOne);

		$dataObject
			->setConnection($this->_getConnection())
			->setModel($this)
			->setSchemaName($this->table_name)
			->setSchema($this->table_column)
			->setData($data);

		return new bPack_DB_ActiveRecord_Entry($dataObject);
	}

	protected function _generateValueExpr($column, $value)
    {
        if(sizeof($value) == 0)
        {
            return '';
        }
        elseif(sizeof($value) == 1)
        {
            $value = $value[0];

            if(is_a($value, 'ActiveRecord_ConditionOperator'))
            {
                $object = $value;
            }
            elseif(is_array($value))
            {
                $object = new ActiveRecord_Condition_MultipleAnd($value);
            }
            else
            {
                $object = new ActiveRecord_Condition_Plain($value);
            }
        }
        else
        {
            $object = new ActiveRecord_Condition_MultipleOr($value);
        }

        $object->setColumn($column);
		return $object->getSQL(); 
	}
}

interface ActiveRecord_ConditionOperator
{
    public function getSQL();
    public function setColumn($col);
}

class ActiveRecord_Condition_NotNull implements ActiveRecord_ConditionOperator
{
	public function getSQL()
	{
		return "`{$this->col}` != NULL";
	}

	public function setColumn($col)
	{
		$this->col = $col;

		return $this;
	}
}

class ActiveRecord_Condition_isNull implements ActiveRecord_ConditionOperator
{
	public function getSQL()
	{
		return "`{$this->col}` = NULL";
	}

	public function setColumn($col)
	{
		$this->col = $col;

		return $this;
	}
}
class ActiveRecord_Condition_Plain implements ActiveRecord_ConditionOperator
{
	protected $statement = ''; public function __construct($value)
    {
        $this->statement = (string) $value;
    }

    public function getSQL()
    {
        return "`{$this->col}` = '{$this->statement}'";
    }

    public function setColumn($name)
    {
        $this->col = $name;
		return $this;
    }
}

class ActiveRecord_Condition_Like implements ActiveRecord_ConditionOperator
{
    public function __construct($value)
    {
        $this->string = $value;
    }

    public function getSQL()
    {
        return "`{$this->col}` LIKE '{$this->string}'";
    }

    public function setColumn($name)
    {
        $this->col = $name;
		return $this;
    }
}

class ActiveRecord_Condition_StatementAnd implements ActiveRecord_ConditionOperator
{
    public function __construct()
    {
        if(func_num_args() > 1)
        {
        $this->statement = func_get_args();
        }
        else
        {
            if(is_array(func_get_arg(0)))
            {
                $this->statement = func_get_arg(0);
            }
        }
    }

    public function setColumn($col)
    {
        $this->col = $col;
		return $this;
    }

    public function getSQL()
    {
        $statements = array();

        foreach($this->statement as $v)
        {
			$v->setColumn($this->col);
            $statements[] = $v->getSQL();
        }

        return implode(' AND ', $statements);
    }
}



class ActiveRecord_Condition_MultipleAnd implements ActiveRecord_ConditionOperator
{
    public function __construct()
    {
        if(func_num_args() > 1)
        {
        $this->statement = func_get_args();
        }
        else
        {
            if(is_array(func_get_arg(0)))
            {
                $this->statement = func_get_arg(0);
            }
        }
    }

    public function setColumn($col)
    {
        $this->col = $col;
		return $this;
    }

    public function getSQL()
    {
        $statements = array();
        foreach($this->statement as $v)
        {
            $statements[] = "`{$this->col}`='{$v}'";
        }

        return implode(' AND ', $statements);
    }
}

class ActiveRecord_Condition_MultipleOr implements ActiveRecord_ConditionOperator
{
    public function __construct()
    {
        if(func_num_args() > 1)
        {
        $this->statement = func_get_args();
        }
        else
        {
            if(is_array(func_get_arg(0)))
            {
                $this->statement = func_get_arg(0);
            }
        }
    }

    public function setColumn($col)
    {
        $this->col = $col;
		return $this;
    }

    public function getSQL()
    {
        $statements = array();
        foreach($this->statement as $v)
        {
            $statements[] = "`{$this->col}` = '{$v}'";
        }

        return implode(' OR ', $statements);
    }
}

class ActiveRecord_Condition_NotAnd implements ActiveRecord_ConditionOperator
{
    public function __construct()
    {
        $this->operators = func_get_args();
    }

    public function setColumn($col)
    {
        $this->col = $col;
		return $this;
    }
    
    public function getSQL()
    {
        $statements = array();

        foreach($this->operators as $v)
        {
            $statements[] = "`{$this->col}` != '{$v}'";
        }

        return implode(' AND ', $statements);
    }
}

class ActiveRecord_Condition_Between implements ActiveRecord_ConditionOperator
{
    public function __construct($a, $b)
    {
		$this->a = $this->givenValue($a);
		$this->b = $this->givenValue($b);
    }

	protected function givenValue($data)
	{
		if(!is_numeric($data))
		{
			return "'$data'";
		}

		return $data;
	}

	public function setColumn($col)
	{
		$this->col = $col;
		return $this;
	}

    public function getSQL()
    {
		return "`{$this->col}` BETWEEN {$this->a} AND {$this->b}";
    }

	public function __toString()
	{
		return $this->getSQL();
	}
}

class ActiveRecord_Condition_Equal implements ActiveRecord_ConditionOperator
{
	protected $col = '';
	protected $src_col = '';
	protected $obj = '';

    public function __construct($value)
    {
		$this->noequal_to = $value;
    }

	public function setColumn($col)
	{
		if($this->col == '')
		{
			$this->col = $col;
		}

		return $this;
	}

    public function getSQL()
    {
		return "`{$this->col}` = '{$this->noequal_to}'";
    }

	public function __toString()
	{
		return $this->getSQL();
	}
}
class ActiveRecord_Condition_NotEqual implements ActiveRecord_ConditionOperator
{
	protected $col = '';
	protected $src_col = '';
	protected $obj = '';

    public function __construct($value)
    {
		$this->noequal_to = $value;
    }

	public function setColumn($col)
	{
		if($this->col == '')
		{
			$this->col = $col;
		}

		return $this;
	}

    public function getSQL()
    {
		return "`{$this->col}` != '{$this->noequal_to}'";
    }

	public function __toString()
	{
		return $this->getSQL();
	}
}

class ActiveRecord_Condition_In implements ActiveRecord_ConditionOperator
{
	protected $col = '';
	protected $src_col = '';
	protected $obj = '';

    public function __construct($obj,$source_col = 'id')
    {
		$this->obj = $obj;

		$this->setSourceColumn($source_col);
    }

	public function setSourceColumn($col)
	{
		if($this->src_col == '')
		{
			$this->src_col = $col;
		}

		return $this;
	}

	public function setColumn($col)
	{
		if($this->col == '')
		{
			$this->col = $col;
		}

		return $this;
	}

	protected function generateIn()
	{
		$data =array();

		if($this->obj instanceOf bPack_DB_ActiveRecord_Entry)
		{
			$data[] = "'". $this->obj->{$this->src_col} . "'";
		}
		elseif($this->obj instanceOf bPack_DB_ActiveRecord_Collection)
		{
			foreach($this->obj as $obj)
			{
				$data[] = "'". $obj->{$this->src_col} . "'";
			}
		}
		elseif(is_array($this->obj))
		{
			foreach($this->obj as $obj)
			{
				$data[] = "'". $obj . "'";
			}

		}
		else
		{
			throw new ActiveRecord_Exception("In need bPack ActiveRecord collection or entry");
		}

		return implode(",", $data);
	}

    public function getSQL()
    {
		return "`{$this->col}` IN ({$this->generateIn()})";
    }

	public function __toString()
	{
		return $this->getSQL();
	}
}

class ActiveRecord_Condition_NotIn implements ActiveRecord_ConditionOperator
{
	protected $col = '';
	protected $src_col = '';
	protected $obj = '';

    public function __construct($obj,$source_col = 'id')
    {
		$this->obj = $obj;

		$this->setSourceColumn($source_col);
    }

	public function setSourceColumn($col)
	{
		if($this->src_col == '')
		{
			$this->src_col = $col;
		}

		return $this;
	}

	public function setColumn($col)
	{
		if($this->col == '')
		{
			$this->col = $col;
		}

		return $this;
	}

	protected function generateIn()
	{
		$data =array();

		if($this->obj instanceOf bPack_DB_ActiveRecord_Entry)
		{
			$data[] = "'". $this->obj->{$this->src_col} . "'";
		}
		elseif($this->obj instanceOf bPack_DB_ActiveRecord_Collection)
		{
			foreach($this->obj as $obj)
			{
				$data[] = "'". $obj->{$this->src_col} . "'";
			}
		}
		elseif(is_array($this->obj))
		{
			foreach($this->obj as $obj)
			{
				$data[] = "'". $obj . "'";
			}

		}
		else
		{
			throw new ActiveRecord_Exception("In need bPack ActiveRecord collection or entry");
		}

		return implode(",", $data);
	}

    public function getSQL()
    {
		return "`{$this->col}` NOT IN ({$this->generateIn()})";
    }

	public function __toString()
	{
		return $this->getSQL();
	}
}




class ActiveRecord_Exception extends bPack_Exception {}
class ActiveRecord_EmptyRequiredFieldException extends ActiveRecord_Exception {}
class ActiveRecord_RecordNotExistException extends ActiveRecord_Exception {}
class ActiveRecord_ColumnNotExistException extends ActiveRecord_Exception {}
class ActiveRecord_NoInputException extends ActiveRecord_Exception {}
