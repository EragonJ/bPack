<?php
define('bP_FETCH_ASSOC', PDO::FETCH_ASSOC);
define('bP_FETCH_NUM', PDO::FETCH_NUM);
define('bP_FETCH_OBJ', PDO::FETCH_OBJ);

abstract class bPack_DB_GenericObject
{
    protected $_db;
    protected $_table = '';
    protected $_pool = '';
    
    public function __construct($db_conn)
    {
        $this->_db = $db_conn;
    }
    
    protected function _get($criteria, $output_type = bP_FETCH_ASSOC)
    {
        if(is_numeric($criteria))
        {
            $sql = "SELECT * FROM `".$this->_table."` WHERE `id` = '".$criteria."';";
        }
        else
        {
            $sql = "SELECT * FROM `".$this->_table."` WHERE ".$criteria.";";
        }
        
        return $this->query($sql, $output_type);
    }
    
    protected function _getRow($criteria, $output_type = bP_FETCH_ASSOC)
    {
        $result = $this->_get($criteria , $output_type);
        
        if(isset($result[0]))
        {
            return $result[0];    
        }
        else
        {
            return false;
        }
    }
    
    protected function _delete($criteria)
    {
        if(is_numeric($criteria))
        {
            $sql = "DELETE FROM `".$this->_table."` WHERE `id` = '".$criteria."';";
        }
        else
        {
            $sql = "DELETE FROM `".$this->_table."` WHERE ".$criteria.";";
        }
        
        return $this->exec($sql);
    }
    
    public function query($sql , $output_type = bP_FETCH_ASSOC)
    {
        if(($rs = $this->_db->query($sql)) == FALSE)
        {
            throw new bPack_Exception('bPack_DB_GenericObject: SQL Execute Failed. SQL: '. $sql);
        }
        else
        {        
            return $rs->fetchAll($output_type);
        }
    }
    
    public function exec($sql)
    {
        if(($rs = $this->_db->exec($sql)) == FALSE)
        {
            throw new bPack_Exception('bPack_DB_GenericObject: SQL Execute Failed. SQL: '. $sql);
        }
        else
        {        
            return $rs;
        }
    }
    
    protected function _setField($column, $value)
    {
        $this->_pool[$column] = $value;
        
        return true;
    }
    
    protected function _getDataSQL()
    {
        $data_sql_items = array();
        
        foreach($this->_pool as $column=>$value)
        {
            $data_sql_items[] = "`$column` = '$value'";
        }
        
        return implode(',',$data_sql_items);
    }
    
    protected function _clearPool()
    {
        $this->_pool = null;
    }
    
    protected function _lastInsertID()
    {
        return $this->_db->lastInsertID();
    }
    
    protected function _save($item_id = 0)
    {
        if(is_null($this->_pool))
        {
            throw new bPack_Exception('bPack_Db_GenericObject->_save: No data to write.');    
        }
        
        if($item_id > 0)
        {
            $sql = "UPDATE `".$this->_table."` SET ".$this->_getDataSQL()." WHERE `id` = '$item_id';";
        }
        else
        {
            $sql = "INSERT INTO `".$this->_table."` SET ".$this->_getDataSQL().";";
        }
        
        $result = $this->exec($sql);
        
        if($result > 0 && $item_id == 0)
        {
            return $this->_lastInsertID();
        }
        else
        {
            return $result;
        }
    }
}