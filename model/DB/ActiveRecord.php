<?php

abstract class bPack_DB_ActiveRecord
{
    const FetchAll = 1;
    const FetchOne = 2;

    protected $collection_instances = array();

    protected $columns = array();
    protected $table_column = array();

    public function getSchema()
    {
        $database_backend  = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        if($database_backend == 'sqlite')
        {
            $schema_sql = '';

            $schema_sql .= "CREATE TABLE `{$this->table_name}` ";

            $field_schema = array();
            foreach($this->table_column as $col => $col_info)
            {
                if(isset($col_info['tag']) && (strpos( $col_info['tag'],'primary_key') !== FALSE))
                {
                    $col_type = 'INTEGER PRIMARY KEY';
                }
                else
                {
                    $col_type = '';
                }

                $field_schema[] = " $col $col_type";
            }

            $schema_sql .= "(".implode(',', $field_schema).");";
        }
        else
        {
            #mysql

            $schema_sql = "CREATE TABLE  IF NOT EXISTS `{$this->table_name}` ";
            $index_sql = '';

            $field_schema = array();
            foreach($this->table_column as $col => $col_info)
            {
                if(isset($col_info['tag']) && (strpos( $col_info['tag'],'primary_key') !== FALSE))
                {
                    $col_type = 'int(20) unsigned not null AUTO_INCREMENT';
                    $index_sql .= ',PRIMARY KEY (`'.$col.'`)';
                }
                else
                {
                    if(strpos(strtolower($col_info['type']),'int') === FALSE)
                    {
                        $col_type = $col_info['type'];
                    }
                    else
                    {
                        $col_type = $col_info['type'] . ' unsigned';
                    }
                }

                $field_schema[] = " `$col` $col_type NOT NULL";
            }

            $schema_sql .= "(".implode(',', $field_schema).' ' .$index_sql.")  ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
        }
        
        return $schema_sql;
    } 

    public function destroySchema()
    {
        return $this->connection->exec('DROP TABLE `'.$this->table_name.'`;');
    }

    public function createSchema()
    {
        return $this->connection->exec($this->getSchema());
    }

	public function __get($name)
	{
		if(substr($name, 0, 1) == '_')
		{
			$column_name = substr($name, 1, strlen($name) - 1);

			if(in_array($column_name, $this->columns))
			{
				return $column_name;
			}
			else
			{
				throw new ActiveRecord_ColumnNotExistException('not found column ' . $column_name);
			}
		}

		throw new ActiveRecord_Exception('not found ' . $name);
	}

    public function __construct()
    {
        $this->connection = bPack_DB::getInstance();
        $this->columns = array_keys($this->table_column);    
    }

    public function first()
    {
        $sql = "SELECT * FROM `{$this->table_name}` LIMIT 1;";
        $data = $this->connection->query($sql)->fetch(PDO::FETCH_ASSOC);

        return $this->generateEntryObject($data);
    }

    public function last()
    {
        $sql = "SELECT * FROM `{$this->table_name}` ORDER BY `".$this->extractPrimaryKey()."` DESC LIMIT 1;";
        $data = $this->connection->query($sql)->fetch(PDO::FETCH_ASSOC);

        return $this->generateEntryObject($data);
    }

    public function generateEntryObject($data = null)
    {
        if($data === FALSE)
        {
            throw new ActiveRecord_RecordNotExistException("ActiveRecord: requested condition had found no data.");
        }

        if(is_null($data))
        {
            return new bPack_DB_ActiveRecord_Entry($this->connection, $this->table_name, $this->table_column);
        }
        else
        {
            return new bPack_DB_ActiveRecord_Entry($this->connection, $this->table_name, $this->table_column, $data);
        }
    }

	public function getColumns()
	{
		return $this->table_column;
	}

    public function create_new_entry()
    {
        return $this->generateEntryObject();
    }

    public function retrieve_all_entries($option = null)
    {
        return $this->find_all_by_id();
    }

	protected function generateColumnListing()
	{
		$columns = array();
		
		foreach($this->columns as $column)
		{
			$columns[] = "`$column`";
		}

		return implode(",", $columns);
	}

    protected function retrieve_multiple_entries_by($column, $value)
    {
        if(!in_array($column, $this->columns))
        {
            throw new ActiveRecord_ColumnNotExistException("Requested column [ $column ] was't in the defination.");
        }

        $value_sql = $this->generateValue($column, $value);

		if($value_sql != "")
		{
			$value_sql = " WHERE $value_sql";
		}

		return new bPack_DB_ActiveRecord_Collection($this->connection, $this->table_name, $this->table_column, $this->generateSelection(), $value_sql);
    }

	protected function generateSelection()
	{
		$raw_columns = $this->columns;
		$columns = array();

		foreach($raw_columns as $col)
		{
			$columns["`$col`"] = $col;
		}

		return $columns;
	}

    protected function retrieve_entry_by($column, $value)
    {
        if(!in_array($column, $this->columns))
        {
            throw new ActiveRecord_ColumnNotExistException("Requested column [ $column ] was't in the defination.");
        }

        $value_sql = $this->generateValue($column, $value);

		if($value_sql != '')
		{
			$value_sql = " WHERE $value_sql";
		}

        $sql = "SELECT ".$this->generateColumnListing()." FROM `{$this->table_name}` {$value_sql};";

        $data = $this->connection->query($sql)->fetch(PDO::FETCH_ASSOC); 

        return $this->generateEntryObject($data);
    }

    protected function generateValue($column, $value)
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
	return $object->getSQL(); }
	public function extractPrimaryKey()
	{
		foreach($this->table_column as $col_name => $data)
		{
			if(strpos('primary_key', $data['tag']) !== FALSE)
			{
				return $col_name;
			}
		}

		throw new Exception("No primaykey found.");
	}

    public function __call($function_name, $attributes)
    {
        if(strpos($function_name, 'find_by_') !== FALSE)
        {
            $column_name = str_replace('find_by_', '', $function_name);
			
			/* when call id, we think it called primary key */
			if($column_name == 'id')
			{
				$column_name = $this->extractPrimaryKey();
			}

            return $this->retrieve_entry_by($column_name, $attributes);
        }
        
        if(strpos($function_name,'find_all_by_') !== FALSE)
        {
            $column_name = str_replace('find_all_by_', '', $function_name);

			/* when call id, we think it called primary key */
			if($column_name == 'id')
			{
				$column_name = $this->extractPrimaryKey();
			}

            if(strpos($column_name,'_with_'))
            {
                $column = substr($column_name, 0, strpos($column_name, '_'));
                $column_condtion = substr($column_name, strpos($column_name, '_with_') + 6, strlen($column_name) - strlen($column));
                
                $entry_data = $this->retrieve_entry_by($column_condtion, $attributes);

                return $this->retrieve_multiple_entries_by($column, $entry_data->id);
            }

            return $this->retrieve_multiple_entries_by($column_name, $attributes);
        }

        throw new bPack_Exception("ActiveRecord: No corresponding method exists. (requested: $function_name)");
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
	protected $statement = '';
    public function __construct($value)
    {
        $this->statement = (string) $value;
    }

    public function getSQL()
    {
        return "`{$this->col}`='{$this->statement}'";
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



class ActiveRecord_Exception extends bPack_Exception {}
class ActiveRecord_EmptyRequiredFieldException extends ActiveRecord_Exception {}
class ActiveRecord_RecordNotExistException extends ActiveRecord_Exception {}
class ActiveRecord_ColumnNotExistException extends ActiveRecord_Exception {}
class ActiveRecord_NoInputException extends ActiveRecord_Exception {}
