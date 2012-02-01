<?php

class bPack_DB_ActiveRecord_Entry implements ArrayAccess
{
    protected $entry_original_data = array();
    protected $entry_new_data = array();

    protected $columns = array();
    protected $tags = array();

    protected $column_tags;
    protected $tag_columns;
    protected $table_column;

	protected $_dataObject = null;

	/*
		constructor
	*/
    public function __construct(bPack_DB_ActiveRecord_DataObject $data_obj)
    {
		$this->_dataObject = $data_obj;

        $this->table_column = $data_obj->getSchema();

        $this->processTableColumn($this->table_column);

		$this->_processRowRecordData();

    }

	/* 
		public method 
	*/
    public function exposeData()
    {
        return $this->entry_original_data;
    }

    public function destroy()
    {
        // return true of false
        $sql = "DELETE FROM `" . $this->_dataObject->getSchemaName() . "` WHERE ".$this->generatePrimaryKeyQuery(). ";";

        return $this->_dataObject->getConnection()->exec($sql);
    }

    public function __set($attribute_name, $value)
    {
        $this->entry_new_data[$attribute_name] = $value;

        return true;
    }

    public function __get($attribute_name)
    {
        if(in_array($attribute_name, $this->columns))
        {
			if(isset($this->entry_original_data[$attribute_name]))
			{
				if(!is_array($this->entry_original_data[$attribute_name]))
				{
					return stripslashes($this->entry_original_data[$attribute_name]);
				}

				return $this->entry_original_data[$attribute_name];

			}
		}
        else
        {
            if(in_array($attribute_name, array_keys($this->entry_new_data)))
            {
                return $this->entry_new_data[$attribute_name];
            }

			if(isset($this->entry_original_data[$attribute_name]) && !is_array($this->entry_original_data[$attribute_name]))
            {
                return stripslashes($this->entry_original_data[$attribute_name]);
            }
			
			/* we assume that id means primary key */
			if($attribute_name == 'id')
			{
				return stripslashes($this->entry_original_data[$this->generatePrimaryKey()]);
			}
			
			if(isset($this->entry_original_data[$attribute_name]))
			{
				return $this->entry_original_data[$attribute_name];
			}

			/* if we just remove attribute instead giving a getter */
			if(method_exists($this->_dataObject->getModel() , 'get_' . ucfirst($attribute_name)))
			{
				return $this->_dataObject->getModel()->{ 'get_' . ucfirst($attribute_name) }($this);
			}

        }

        throw new ActiveRecord_ColumnNotExistException("requested field '$attribute_name' doest not exist in schema");
    }

    public function save()
    {
		/* data to store */
		$data_to_store = $this->_save_prepareData();
		
		/* if no data to store, then return exception */
		if(sizeof($data_to_store) == 0)
        {
			throw new ActiveRecord_NullUpdate;
        }

		/* before update, we have to know the idenitical column */
		$primary_key_column = $this->generatePrimaryKey();
		
		if( $this->_save_isUpdate($primary_key_column) )
		{
            $this->processUpdateEveryTime($data_to_store);

            $prepare_sql = "UPDATE `" . $this->_dataObject->getSchemaName() . "` SET " . $this->extractColumnPreparedName($data_be_updated) . " WHERE ".$this->generatePrimaryKeyQuery().";";

            $prepared_stmt = $this->_dataObject->getConnection()->prepare($prepare_sql);

            $data_prepared =array();
            foreach($data_to_store as $k=> $v)
            {
                $data_prepared[':'.$k] = $v;
            }
            
            # return update, true or false
            return $prepared_stmt->execute($data_prepared);
        }
        else
        {
            // check if required data were not given
            $this->processTagRequired($data_to_store);
            $this->processAutofill($data_to_store);

            $prepare_sql = "INSERT INTO `" . $this->_dataObject->getSchemaName() . "` (".$this->extractColumnName($data_to_store).") VALUES (".$this->extractPreparedColumnName($data_to_store).")";

            $prepared_stmt = $this->_dataObject->getConnection()->prepare($prepare_sql);
            
            $data_prepared =array();
            foreach($data_to_store as $k=> $v)
            {
                $data_prepared[':'.$k] = $v;
            }

            // return rowid
            $prepared_stmt->execute($data_prepared);

            return $this->_dataObject->getConnection()->lastInsertId();
        }

        return false;
    }

	/* 
		method of ArrryAccess
	*/
	public function offsetExists($offset)
	{
		try
		{
			$this->__get($offset);

			return true;
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	public function offsetSet($offset, $value)
	{
		return $this->__set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return true;
	}


	/*
		protected functin: helper function 
	*/
	protected function _processRowRecordData()
	{
		if($this->_dataObject->hasData())
        {
            foreach($this->_dataObject->getData() as $k => $v)
            {
                if(strpos($v,'__JSON__') === FALSE)
                {
                    $this->entry_original_data[$k] = $v;
                }
                else
                {
                    $v = str_replace('__JSON__','',$v);
                    $new_v = json_decode($v, true);

                    $this->entry_original_data[$k] = $new_v;
                }
            }
        }

	}

    protected function processTableColumn()
    {
        $this->columns = array_keys($this->table_column);

        $this->processTags();
        
        return true;
    }

    protected function processTags()
    {
        foreach($this->table_column as $col=>$col_setting)
        {
            if(!isset($col_setting['tag']))
            {
                continue;
            }

            $tag = $col_setting['tag'];

            if(strpos($tag, ' '))
            {
                $tags = explode(' ', $tag);
            }
            else
            {
                $tags = array($tag);
            }

            foreach($tags as $tagging)
            {
                $this->tag_columns[$tagging][] = $col;
                $this->column_tags[$col][] = $tagging;
            }
        }

    }

    protected function checkIfSame($value, $name)
    {
        if(!isset($this->entry_original_data[$name]))
        {
            return false;
        }

        return ($this->entry_original_data[$name] == $value);
    }

    protected function extractColValueHash($data)
    {
        $sql_statements = array();

        foreach($data as $k=>$v)
        {
            $sql_statements[] = "`$k`='$v'";
        }

        return implode(',', $sql_statements);
    }
    
    protected function extractColumnPreparedName($data)
    {
        $sql_statements = array();

        foreach($data as $k=>$v)
        {
            $sql_statements[] = "`{$k}`=:{$k}";
        }

        return implode(',', $sql_statements);
    }

	protected function generatePrimaryKey()
	{
		if(isset($this->tag_columns['primary_key']))
		{
			if(sizeof($this->tag_columns['primary_key']) > 1)
			{
				throw new Exception('too many primary key');
			}

			$primary_key_name = $this->tag_columns['primary_key'][0];

			return $primary_key_name;
		}
	}

	protected function generatePrimaryKeyQuery()
	{
		$primary_key_name = $this->generatePrimaryKey();

		return "`{$primary_key_name}` = '{$this->entry_original_data[$primary_key_name]}'";
	}
	
	protected function _save_prepareData()
	{
		$data_be_updated = array();

        foreach($this->entry_new_data as $name=>$value)
        {
			/* if value is array, convert into JSON */
            if(is_array($value))
            {
                $value = "__JSON__" . json_encode($value);
            }
			
			/* is the new data equals to old data? if so, don't update */
            if(! $this->checkIfSame($value, $name))
            {
                $data_be_updated[$name] = $value;
            }
        }

		return $data_be_updated;
	}

	protected function _save_isUpdate($primary_key_column)
	{
		return 
			isset($this->entry_original_data[$primary_key_column]) 
			&& 
			$this->entry_original_data[$primary_key_column] !== '';
	}
    protected function extractPreparedColumnName($data)
    {
        $data_sql  = array();
        foreach($data as $k=>$v)
        {
            $data_sql[] = ":{$k}";
        }

        return implode(',', $data_sql);
    }

    protected function processUpdateEveryTime(&$data)
    {
		if(array_key_exists('update_every_time', $this->tag_columns))
		{
			foreach($this->tag_columns['update_every_time'] as $col)
			{
				if(in_array('current_timestamp',$this->column_tags[$col]))
				{
					$data[$col] = date('Y-m-d H:i:s' ,time());
				}
			}
		}
		else
		{
			return true;
		}
    }

    protected function processTagRequired(&$data)
    {
		if(isset($this->tag_columns['required']))
		{
			foreach($this->tag_columns['required'] as $col)
			{
				if ( isset($data[$col]) )
				{
					if ((trim($data[$col]) == ''))
					{
						if(in_array('allow_empty', $this->column_tags[$col]))
						{
							continue;
						}

						if(in_array('has_default_value', $this->column_tags[$col]))
						{
							$data[$col] = $this->table_column[$col]['default'];
							continue;
						}
					}
					else
					{
						continue;
					}
				}

				throw new ActiveRecord_EmptyRequiredFieldException("$col is required, and should not be empty");
			}
		}
    }

    protected function processAutofill(&$data_be_updated)
    {
		if(!array_key_exists('autofill_on_create', $this->tag_columns))
		{
			return true;
		}

        foreach($this->tag_columns['autofill_on_create'] as $column)
        {
            if(in_array('current_timestamp', $this->column_tags[$column]) && !isset($data_be_updated[$column]))
            {
                $data_be_updated[$column] = date('Y-m-d H:i:s', time());
            }

            if(in_array('primary_key', $this->column_tags[$column]))
            {
                $data_be_updated[$column] = null;
            }
        }

        return true;
    }

    protected function extractColumnName($hash)
    {
        $columns = array_keys($hash);

        $columns_sql = array();
        foreach($columns as $col)
        {
            $columns_sql[] = "`$col`";
        }

        return implode(',' , $columns_sql);
    }

    protected function extractColumnValue($hash)
    {
        $columns = array_keys($hash);

        $values_sql = array();
        foreach($columns as $col)
        {
            if($hash[$col] === NULL)
            {
                $values_sql[] = "NULL";
            }
            else
            {
                $values_sql[] = $this->_dataObject->getConnection()->quote($hash[$col]);
            }
        }
        
        return implode(',', $values_sql);
    }
}

class ActiveRecord_ValueFilter_AutofillOnCreate extends ActiveRecord_ValueFilter
{

}

class ActiveRecord_ValueFilter_Arrayto_JSON extends ActiveRecord_ValueFilter
{

}

abstract class ActiveRecord_ValueFilter { }
class ActiveRecord_NullUpdate extends ActiveRecord_Exception {}
