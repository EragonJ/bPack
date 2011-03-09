<?php

abstract class bPack_DB_ActiveRecord
{
    const FetchAll = 1;
    const FetchOne = 2;

    protected $columns = array();
    protected $table_column = array();

    public function getSchema()
    {
        $database_backend  = 'MYSQL';
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

            $schema_sql .= "(".implode(',', $field_schema).' ' .$index_sql.")  ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
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
        $sql = "SELECT * FROM `{$this->table_name}` ORDER BY `id` DESC LIMIT 1;";
        $data = $this->connection->query($sql)->fetch(PDO::FETCH_ASSOC);

        return $this->generateEntryObject($data);
    }

    protected function generateEntryObject($data = null)
    {
        if($data === FALSE)
        {
            throw new ActiveRecord_RecordNotExistException("ActiveRecord: requested condition had found no data.");
        }

        if(is_null($data))
        {
            return new bPack_DB_ActiveModel_Entry($this->connection, $this->table_name, $this->table_column);
        }
        else
        {
            return new bPack_DB_ActiveModel_Entry($this->connection, $this->table_name, $this->table_column, $data);
        }
    }

    public function create_new_entry()
    {
        return $this->generateEntryObject();
    }

    public function retrieve_all_entries($option = null)
    {
        return $this->find_all_by_id();
    }

    protected function retrieve_entry_by($column, $value, $retrieve_count)
    {
        if(!in_array($column, $this->columns))
        {
            throw new ActiveRecord_ColumnNotExistException("Requested column [ $column ] was't in the defination.");
        }

        $value_sql = $this->generateValue($column, $value);

        $sql = "SELECT * FROM `{$this->table_name}` WHERE {$value_sql};";

        if ($retrieve_count == self::FetchOne)
        {
            $data = $this->connection->query($sql)->fetch(PDO::FETCH_ASSOC); 
            $result_set = $this->generateEntryObject($data);
        }
        else
        {
            $dataset = $this->connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            $result_set = array();

            foreach($dataset as $data)
            {
                $result_set[] = $this->generateEntryObject($data);
            }
        }

        return $result_set;
    }

    protected function generateValue($column, $value)
    {
        if(sizeof($value) == 0)
        {
            return '1=1';
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

    public function __call($function_name, $attributes)
    {
        if(strpos($function_name, 'find_by_') !== FALSE)
        {
            $column_name = str_replace('find_by_', '', $function_name);

            return $this->retrieve_entry_by($column_name, $attributes, $fetch_type = self::FetchOne);
        }
        
        if(strpos($function_name,'find_all_by_') !== FALSE)
        {
            $column_name = str_replace('find_all_by_', '', $function_name);

            return $this->retrieve_entry_by($column_name, $attributes, $fetch_type = self::FetchAll);
        }

        throw new bPack_Exception("ActiveRecord: No corresponding method exists. (requested: $function_name)");
    }
}

class bPack_DB_ActiveModel_Collection implements ArrayAccess, Countable, Iterator
{
    protected $dataset = array();

    public function offsetExists($offset)
    {
        return isset($this->entry_original_data[$offset]);
    }

    public function offsetGet($offset)
    {
        # todo: consider this stripslashes
        return (isset($this->entry_original_data[$offset])) ? stripslashes($this->entry_original_data[$offset]) : null;
    }

    public function offsetSet($offset, $value)
    {
        return true;
    }

    public function offsetUnset($offset)
    {
        return true;
    }

    public function current ()
    {

    }

    public function key ()
    {

    }

    public function next ()
    {

    }

    public function rewind ()
    {

    }

    public function valid ()
    {

    }

    public function count()
    {

    }

}

class bPack_DB_ActiveModel_Entry implements ArrayAccess
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
            throw new ActiveRecord_NoInputException('there is no data to update');
        }

        if(isset($this->entry_original_data['id']) && $this->entry_original_data['id'] !== '')
        {
            $this->processUpdateEveryTime($data_be_updated);

            $prepare_sql = "UPDATE `{$this->table_name}` SET " . $this->extractColumnPreparedName($data_be_updated) . " WHERE `id` = {$this->entry_original_data['id']};";

            $prepared_stmt = $this->connection->prepare($prepare_sql);

            $data_prepared =array();
            foreach($data_be_updated as $k=> $v)
            {
                $data_prepared[':'.$k] = $v;
            }


            //$sql = "UPDATE `{$this->table_name}` SET ".$this->extractColValueHash($data_be_updated)." where `id` = {$this->entry_original_data['id']};";

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
        foreach($this->tag_columns['update_every_time'] as $col)
        {
            if(in_array('current_timestamp',$this->column_tags[$col]))
            {
                $data[$col] = date('Y-m-d H:i:s' ,time());
            }
        }
    }

    protected function processTagRequired(&$data)
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

    protected function processAutofill(&$data_be_updated)
    {
        foreach($this->tag_columns['autofill_on_create'] as $column)
        {
            if(in_array('current_timestamp', $this->column_tags[$column]))
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

    public function destory()
    {
        // return true of false
        $sql = "DELETE FROM `{$this->table_name}` WHERE `id` = '{$this->entry_original_data['id']}';";

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
            if(!is_array($this->entry_original_data[$attribute_name]))
            {
                return stripslashes($this->entry_original_data[$attribute_name]);
            }

            return $this->entry_original_data[$attribute_name];
        }
        else
        {
            if(in_array($attribute_name, array_keys($this->entry_new_data)))
            {
                return $this->entry_new_data[$attribute_name];
            }
        }

        throw new ActiveRecord_ColumnNotExistException("requested field '$attribute_name' doest not exist in schema");
    }
}

interface ActiveRecord_ConditionOperator
{
    public function getSQL();
    public function setColumn($col);
}

class ActiveRecord_Condition_Plain implements ActiveRecord_ConditionOperator
{
    public function __construct($value)
    {
        $this->statement = $value;
    }

    public function getSQL()
    {
        return "`{$this->col}`='{$this->statement}'";
    }

    public function setColumn($name)
    {
        $this->col = $name;
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

class ActiveRecord_Exception extends bPack_Exception {}
class ActiveRecord_EmptyRequiredFieldException extends ActiveRecord_Exception {}
class ActiveRecord_RecordNotExistException extends ActiveRecord_Exception {}
class ActiveRecord_ColumnNotExistException extends ActiveRecord_Exception {}
class ActiveRecord_NoInputException extends ActiveRecord_Exception {}
