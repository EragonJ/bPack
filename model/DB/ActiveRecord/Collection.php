<?php

class bPack_DB_ActiveRecord_Collection implements ArrayAccess, Countable, Iterator
{
    private $position = 0;
    private $sql = '';

    protected $columns = array();
    protected $connection = null;
    protected $table_name = '';
    protected $table_column = '';

    protected $limit = null;
    protected $orderby = array();
	protected $group_by = '';
    protected $offset = 0;

    protected $condition = '';
    protected $selection = array();

    protected $generated_data = null;
    protected $required_regenerate = true;

    public function __call($function_name, $argument)
    {
        if(strpos($function_name, 'having_') !== FALSE)
        {
            $col_condition = str_replace($function_name, 'having_', '');

            if(strpos($col_condition, '_by_') !== FALSE)
            {
                $col_temp = explode('_', $col_condition);

                $col = $col_temp[0];
                $col_by = $col_temp[1];
            }
            else
            {
                $col = $col_condition;
                $col_by = 'id';
            }
        }
    }

	protected function generateSelection()
	{
		$columns = array();
		
		foreach($this->selection as $column=>$alias)
		{
			if($column == $alias)
			{
				$columns[] = "$column";
			}
			else
			{
				$columns[] = "$column as `$alias`";
			}
		}

		return implode(",", $columns);
	}

 	public function removeAllSelect()
	{
		$this->selection = array($this->generatePrimaryKey());

		return $this;
	}

	public function removeSelect($select)
	{
		unset($this->selection[$select]);

		return $this;
	}

	public function addSelect($select, $alias = '')
	{
		if($select instanceof AR_modifier)
		{
			if($alias == '')
			{
				$alias = $select->returnData();
			}

			$this->selection[$select->returnData()] = $alias;
		}
		else
		{
			if($alias == '')
			{
				$alias = $select;
			}

			if(!in_array($select, $this->columns))
			{
				throw new Exception('no column');
			}

			$this->selection["`$select`"] = $alias;
		}
		
		return $this;
	}



    public function getLastSQL()
    {
        return $this->sql;
    }

    protected function IsDataRequireGenerate()
    {
        if($this->required_regenerate === true)
        {
            return true;
        }

        if($this->required_regenerate === false && is_null($this->generated_data))
        {
            return true;
        }

        return false;
    }

    public function generateOrderBySQL()
    {
        if(sizeof($this->orderby) > 0)
        {
            $sql = array();
            
            foreach($this->orderby as $col => $dir)
            {
				if(strpos($col, '`') !== FALSE)
				{
					$sql[] = "$col $dir";
				}
				else
				{
                	$sql[] = "`$col` $dir";
				}
            }

            return ' ORDER BY ' . implode(',',$sql);
        }
        else
        {
            return '';
        }
    }

    public function generateLimitSQL()
    {
        if($this->limit > 0)
        {
            return " LIMIT {$this->offset}, {$this->limit}";
        }

        return '';
    }

    public function count()
    {
        if($this->IsDataRequireGenerate())
        {
            $this->generateData();       
        }
        
        return sizeof($this->generateData);

    }

    public function current()
    {
        if($this->IsDataRequireGenerate())
        {
            $this->generateData();       
        }
        
        return $this->generateData[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        if($this->IsDataRequireGenerate())
        {
            $this->generateData();       
        }

        return isset($this->generateData[$this->position]);
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

	protected function generateGroupBySQL()
	{
		if($this->group_by)
		{
			return " GROUP BY `{$this->group_by}`";
		}

		return '';
	}

    protected function generateData()
    {
        $this->sql = "SELECT " . $this->generateSelection() . "FROM `{$this->table_name}` ". $this->condition . $this->generateGroupBySQL() . $this->generateOrderBySQL() . $this->generateLimitSQL() . ';';

        $dataset = $this->connection->query($this->sql )->fetchAll(PDO::FETCH_ASSOC);

        $result_set = array();

        foreach($dataset as $data)
        {
            $result_set[] = $this->generateEntryObject($data);
        }

        $this->generateData = $result_set;

        $this->required_regenerate = false;
    }

    public function offsetExists($offset)
    {
        if($this->IsDataRequireGenerate())
        {
            echo '';
            $this->generateData();       
        }

        return isset($this->generateData[$offset]);
    }

    public function offsetGet($offset)
    {
        if($this->IsDataRequireGenerate())
        {
            $this->generateData();
        }

        return $this->generateData[$offset];
    }

    public function exposeData()
    {
        if($this->IsDataRequireGenerate())
        {
            $this->generateData();
        }

        $data = array();
        foreach($this->generateData as $v)
        {
            $data[] = $v->exposeData();
        }

        return $data;
    }

    public function offsetSet($offset, $value)
    {
        return false;
    }

    public function offsetUnset($offset)
    {
        return false;
    }

    public function __construct($connection, $table_name, $columns, $selection, $condition)
    {
        $this->connection = $connection;
        $this->table_name = $table_name;
        $this->condition = $condition;
        $this->selection = $selection;
        $this->columns = array_keys($columns);
        $this->table_column = $columns;
    }

    public function limit($limit_count)
    {
        $this->limit = $limit_count;
        $this->required_regenerate = true;

        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        $this->required_regenerate = true;

        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
		if($column instanceof AR_modifier)
		{
			$column = $column->returnData();
		}

        $this->orderby[$column] = $direction;
        $this->required_regenerate = true;

        return $this;
    }

	public function group_by($column)
	{
		if($this->group_by == '')
		{
			$this->group_by = $column;
			return $this;
		}

		throw new Exception('two groupby');
	}
}

interface AR_modifier
{
	public function returnData();
	public function __toString();
}

class AR_Plain  implements AR_modifier
{
	public function __construct($col)
	{
		$this->col = $col;
	}

	public function __toString()
	{
		return $this->returnData();
	}

	public function returnData()
	{
		return $this->col;
	}
	
}
