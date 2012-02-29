<?php

class bPack_DB_ActiveRecord_Collection implements ArrayAccess, Countable, Iterator
{
/* tested*/

	/* database connection */
	protected $_connection = null;

	/* model info */
	protected $_model = null;
	protected $_schema_name = null;
	protected $_schema = null;

	/* request condition (where) */
	protected $_condition = null;

	/* field to fetch */
	protected $_selected_fields = null;

	protected $_limit = null;
	protected $_offset = null;
	protected $_orderBy = null;
	protected $_group_by = null;
	protected $_having = null;

	/* for iterator */
	protected $_position = 0;

	/* logging */
	protected $_last_sql = null;
		
	/* cache */
	protected $_columns = null;
	protected $_entry_dataObject = null;

	/* after queried, store data here */
	protected $_stored_data = null;

	/* */

    public function __construct(bPack_DB_ActiveRecord_DataObject $dataObject)
    {
        $this->_connection = $dataObject->getConnection();
        $this->_model = $dataObject->getModel();

		$this->_condition =  $dataObject->hasCondition() ? $dataObject->getCondition() : null;

        $this->_schema_name = $dataObject->getSchemaName();
        $this->_schema = $dataObject->getSchema();

		$this->_prepareSelection();
		$this->_prepareColumnCache();
    }

	protected function _prepareSelection()
	{
		$this->_selected_fields = array_keys($this->_schema);
	}

	public function removeAllSelect()
	{
		$this->_selected_fields = array($this->_model->_id);
	}

	public function resetSelect()
	{
		$this->_prepareSelection();
	}

	protected function _prepareColumnCache()
	{
		$this->_columns = array_keys($this->_schema);
	}

	protected function _dataExists()
	{
		return !is_null($this->_stored_data);
	}

	protected function _generateData()
	{
		// build the query
		$sql_statement = array();
		
		$sql_statement[] = $this->_getSelectedListing();
		$sql_statement[] = $this->_getFrom();
		$sql_statement[] = $this->_getCondition();
		$sql_statement[] = $this->_getGroupBy();
		$sql_statement[] = $this->_getHaving();
		$sql_statement[] = $this->_getOrderBy();
		$sql_statement[] = $this->_getLimit();
		$sql_statement[] = $this->_getOffset();
		
		// remove all empty slice
		$sql_statement = array_filter($sql_statement);

		// join them with a space and add a trailing ;
		$sql = implode(' ', $sql_statement) . ";";
		
		$this->_stored_data = $this->_getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$this->_last_sql = $sql;
	}

	protected function _getCondition()
	{
		if( $this->_condition )
		{
			return "WHERE " . $this->_condition;
		}

		return;
	}

	protected function _getHaving()
	{
		if( !is_null($this->_having))
		{
			return "HAVING " . $this->_having;
		}

		return;
	}

	protected function _getSelectedListing()
	{
		$schema = $this->_schema;

		return "SELECT `" . implode('`, `', array_filter($this->_selected_fields, function($value) use ($schema) {
			return !($schema[$value]['type'] == 'virtual');
		})) ."`";
	}

	protected function _getFrom()
	{
		return "FROM `{$this->_schema_name}`";
	}

	protected function _getLimit()
	{
		if( !is_null($this->_limit) )
		{
			if( !is_null($this->_offset) )
			{
				return "LIMIT " . $this->_offset . ", ". $this->_limit;
			}

			return "LIMIT ". $this->_limit;
		}

		return;
	}

	protected function _getOffset()
	{
		if( !is_null($this->_offset) )
		{
			// if limit is not null, then we could guess that we had produced that before
			if( is_null($this->_limit) )
			{
				return "OFFSET " . $this->_offset;
			}
		}
		
		return;
	}

	public function group_by($group_by_column = null)
	{
		if( !is_null($this->_group_by) )
		{
			throw new ActiveRecord_CollectionException("Two groupby column!!");
		}

		$this->_group_by = $group_by_column;

		return $this;
	}

	public function _getGroupBy()
	{
		if( !is_null($this->_group_by) )
		{
			return "GROUP BY `{$this->_group_by}`";
		}
	}
	
	public function limit($limit_value = null)
	{
		$this->_limit = $limit_value;

		return $this;
	}

	public function offset($offset_value = null)
	{
		$this->_offset = $offset_value;

		return $this;
	}

	public function orderBy($column, $direction = 'DESC')
	{
		if( is_null($this->_orderBy) ) 
		{
			$this->_orderBy = array();
		}

		$this->_orderBy[] = array('column' => $column, 'direction' => $direction);
		
		return $this;
	}

	public function _getOrderBy()
	{
		if( !is_null($this->_orderBy) )
		{
			$orderBy_array = array();

			foreach($this->_orderBy as $order)
			{
				$orderBy_array[] = "`{$order['column']}` " . $order['direction'];
			}

			return "ORDER BY " . implode(', ', $orderBy_array);
		}
	}

	public function refresh()
	{
		$this->_generateData();

		return $this;
	}

	public function destroy()
	{
		$sql = "DELETE FROM `{$this->_schema_name}` WHERE {$this->_condition};";

		return ($this->_getConnection()->query($sql) === FALSE);
	}

	protected function _getConnection()
	{
		return $this->_connection;
	}

	public function getCondition()
	{
		return $this->_condition;
	}

	// implements of Countable interface

    public function count()
    {
		$this->_dataExists() ?: $this->_generateData();       
        
        return sizeof($this->_stored_data);
    }

    public function __call($function_name, $argument)
    {
        if(strpos($function_name, 'having_') !== FALSE)
        {
			if( is_null($this->_group_by) )
			{
				throw new ActiveRecord_Collection_HavingWithoutGroupByException("No groupBy statement were given");
			}

            $col_condition = str_replace( 'having_', '', $function_name);

			/* eg: having_agent_viewed_at() get agent_viewed_at <--- column name*/
			/* todo: implment a query parser */

			if($argument[0] instanceof ActiveRecord_ConditionOperator)
			{
				$this->_having = $argument[0]->setColumn($col_condition)->getSQL();
			}
			else
			{
				$this->_having = "`$col_condition` = " . $this->_getConnection()->quote($argument[0]);
			}

			return $this;
        }

		if(strpos($function_name, 'also_') !== FALSE)
		{
			$col_condition = str_replace('also_', '', $function_name);
			
			if($argument[0] instanceof ActiveRecord_ConditionOperator)
			{
				$this->_condition .= ' AND ' . $argument[0]->setColumn($col_condition)->getSQL();
			}
			else
			{
				$this->_condition .= ' AND `' .$col_condition. '` = '. "'".$argument[0]."'";
			}

			return $this;
		}
    }

	public function removeSelect($select)
	{
		$this->_selected_fields = array_diff($this->_selected_fields, array($select));

		return $this;
	}

	public function addSelect($select)
	{
		if($select instanceof AR_modifier)
		{
			$this->_selected_fields = $select->returnData();
		}
		else
		{
			if(!in_array($select, $this->columns))
			{
				throw new Exception('no column');
			}

			$this->_selected_fields[] = $select;
		}
		
		return $this;
	}

    public function getLastSQL()
    {
        return $this->_last_sql;
    }

    public function current()
    {
		$this->_dataExists() ?: $this->_generateData();
        
        return $this->_generateEntryObject($this->_stored_data[$this->_position]);
    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function valid()
    {
		$this->_dataExists() ?: $this->_generateData();

        return isset($this->_stored_data[$this->_position]);
    }

    protected function _generateEntryObject($data = null)
    {
        if($data === FALSE)
        {
            throw new ActiveRecord_RecordNotExistException("ActiveRecord: requested condition had found no data.");
        }
		
		$dataObject = new bPack_DB_ActiveRecord_DataObject;

		$dataObject
			->setConnection( $this->_getConnection() )
			->setModel($this->_model)
			->setSchemaName($this->_schema_name)
			->setSchema($this->_schema)
			->setData($data);

		return new bPack_DB_ActiveRecord_Entry($dataObject);
    }

    public function offsetExists($offset)
    {
		$this->_dataExists() ?: $this->_generateData();

        return isset($this->_stored_data[$offset]);
    }

    public function offsetGet($offset)
    {
		$this->_dataExists() ?: $this->_generateData();

        return $this->_generateEntryObject($this->_stored_data[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        return false;
    }

    public function offsetUnset($offset)
    {
        return false;
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

class ActiveRecord_CollectionException extends ActiveRecord_Exception {}
class ActiveRecord_Collection_HavingWithoutGroupByException extends ActiveRecord_CollectionException {}

