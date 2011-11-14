<?php

class bPack_DB_ActiveRecord_Entry implements ArrayAccess
{
    protected $entry_original_data = array();
    protected $entry_new_data = array();

    protected $columns = array();
    protected $tags = array();

    protected $connection = null;
    protected $table_name = '';

    protected $column_tags;
    protected $tag_columns;
    protected $table_column;

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

    public function exposeData()
    {
        return $this->entry_original_data;
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

    public function __construct($connection, $table_name, $columns, $data = null)
    {
        $this->connection = $connection;

        $this->table_name = $table_name;
        $this->table_column = $columns;

        $this->processTableColumn($columns);

        if(!is_null($data))
        {
            foreach($data as $k => $v)
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

    protected function processTableColumn($columns)
    {
        $this->columns = array_keys($columns);

        $this->processTags($columns);
        
        return true;
    }

    protected function processTags($columns)
    {
        foreach($columns as $col=>$col_setting)
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


    public function save()
    {
        $data_be_updated = array();

        foreach($this->entry_new_data as $name=>$value)
        {
            if(is_array($value))
            {
                $value = "__JSON__" . json_encode($value);
            }

            if(! $this->checkIfSame($value, $name))
            {
                $data_be_updated[$name] = $value;
            }
        }

        if(sizeof($data_be_updated) == 0)
        {
			throw new ActiveRecord_NullUpdate;
        }
		
		$primary_key_column = $this->generatePrimaryKey();

        if(isset($this->entry_original_data[$primary_key_column]) && $this->entry_original_data[$primary_key_column] !== '')
        {
            $this->processUpdateEveryTime($data_be_updated);

            $prepare_sql = "UPDATE `{$this->table_name}` SET " . $this->extractColumnPreparedName($data_be_updated) . " WHERE ".$this->generatePrimaryKeyQuery().";";

            $prepared_stmt = $this->connection->prepare($prepare_sql);

            $data_prepared =array();
            foreach($data_be_updated as $k=> $v)
            {
                $data_prepared[':'.$k] = $v;
            }
            
            //$sql = "UPDATE `{$this->table_name}` SET ".$this->extractColValueHash($data_be_updated)." where " . $this->generatePrimaryKeyQuery() . ";";

            # return update, true or false
            return $prepared_stmt->execute($data_prepared);
        }
        else
        {
            // check if required data were not given
            $this->processTagRequired($data_be_updated);

            $this->processAutofill($data_be_updated);

            $prepare_sql = "INSERT INTO `{$this->table_name}` (".$this->extractColumnName($data_be_updated).") VALUES (".$this->extractPreparedColumnName($data_be_updated).")";

            $prepared_stmt = $this->connection->prepare($prepare_sql);
            
            $data_prepared =array();
            foreach($data_be_updated as $k=> $v)
            {
                $data_prepared[':'.$k] = $v;
            }

            #$sql = "INSERT INTO `{$this->table_name}` (".$this->extractColumnName($data_be_updated).") VALUES (".$this->extractColumnValue($data_be_updated).");";

            // return rowid
            $prepared_stmt->execute($data_prepared);

            return $this->connection->lastInsertId();
        }

        return false;
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
                $values_sql[] = $this->connection->quote($hash[$col]);
            }
        }
        
        return implode(',', $values_sql);
    }

    public function destroy()
    {
        // return true of false
        $sql = "DELETE FROM `{$this->table_name}` WHERE ".$this->generatePrimaryKeyQuery(). ";";

        return $this->connection->exec($sql);
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

			return null;
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

			return null;
        }

        throw new ActiveRecord_ColumnNotExistException("requested field '$attribute_name' doest not exist in schema");
    }
}

class ActiveRecord_NullUpdate extends ActiveRecord_Exception {}
