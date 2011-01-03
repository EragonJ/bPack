<?php

// SQL 92 Generic Object Model

abstract class bPack_DB_Model
{
    const IDENITY_NUMBER = 1;
    const SQL_STATEMENT = 2;

    const RETURN_ONE = 1;
    const RETURN_ALL = 2;

    protected $db;
    
    public function parseStatement($statement)
    {
        #new
        if(is_numeric($statement)) {
            return self::IDENITY_NUMBER;
        }
        else
        {
            return self::SQL_STATEMENT;
        }
    }

    public function query($sql_statement, $return = self::RETURN_ALL)
    {
        $data = $this->db->query($sql_statement)->fetchAll(PDO::FETCH_ASSOC);

        if($return == self::RETURN_ONE)
        {
            return $data[0];
        }
        else
        {
            return $data;
        }
    }

    public function execute($sql_statement)
    {
        return $this->db->exec($sql_statement);
    }

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function read($statement = '')
    {
        if($statement == '')
        {
            $sql = "SELECT ROWID as `id`, * FROM `".$this->table_name."`;";
            return $this->query($sql);
        }

        switch($this->parseStatement($statement))
        {
            case self::IDENITY_NUMBER:
                $sql = "SELECT ROWID as `id`, * FROM `".$this->table_name."` WHERE `id` = '$statement';";
                return $this->query($sql, self::RETURN_ONE);
            break;

            case self::SQL_STATEMENT:
                $sql = "SELECT ROWID as `id`, * FROM `".$this->table_name."` WHERE " . $statement . ";";
                return $this->query($sql);
            break;
        }

        return false;
    }

    public function delete($statement)
    {
        switch($this->parseStatement($statement))
        {
            case self::IDENITY_NUMBER:
                $sql = "DELETE FROM `".$this->table_name."` WHERE ROWID = '$statement';";
                return $this->execute($sql);
            break;

            case self::SQL_STATEMENT:
                $sql = "DELETE FROM `".$this->table_name."` WHERE " . $statement;
                return $this->execute($sql);
            break;
        }

        return false;
       
    }

    public function create(bPack_DataContainer $data_obj)
    {
        $fields = array();
        $data = array();

        foreach($data_obj->getStoredData() as $field => $datum)
        {
            $fields[] = "`$field`";

            if($datum === FALSE)
            {
                $datum = 0;
            }

            if($datum === TRUE)
            {
                $datum = 1;
            }

            $data[] = "'$datum'";
        }

        $field_sql = implode(',', $fields);
        $data_sql = implode(',', $data);

        $sql = "INSERT INTO `".$this->table_name."` ($field_sql) VALUES ($data_sql);";

        if($this->execute($sql))
        {
            return $this->db->lastInsertId();
        }
        else
        {
            return false;
        }
    }

    public function update($statement, bPack_DataContainer $data_obj)
    {
        $sql_group = array();

        foreach($data_obj->getStoredData() as $field => $datum)
        {
            $sql_group[] = "`$field` = '$datum'";
        }

        $update_sql = implode(',',$sql_group);

        switch($this->parseStatement($statement))
        {
            case self::IDENITY_NUMBER:
                $sql = "UPDATE `".$this->table_name."` SET $update_sql WHERE ROWID = '$statement';";
                return $this->execute($sql);
            break;

            case self::SQL_STATEMENT:
                $sql = "UPDATE `".$this->table_name."` SET $update_sql WHERE " . $statement;
                return $this->execute($sql);
            break;
        }
    }

}
