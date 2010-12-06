<?php
    class bPack_DB_MySQL
    {
        private $database_link;

        public function __construct($config)
        {
            $this->database_link = mysql_connect($config->get('host'), $config->get('username'), $config->get('password'));
            
            mysql_select_db($config->get('name'));
        }
        
        public function getEngine()
        {
            return $this;
        }

        public function query($sql)
        {
            return new bPack_DB_MySQLi_PDO_Statement($this->database_link, $sql);
        }

        public function exec($sql)
        {
            $rs = mysql_query($sql, $this->database_link);

            if($rs === false)
            {
                return false;
            }
            else
            {
                return mysql_affected_rows();
            }
        }
        
        public function set_names_utf8()
        {
            $this->exec("SET NAMES 'utf8';");

            return true;
        }
 
    }

    class bPack_DB_MySQLi_PDO_Statement
    {
        public function __construct($link, $sql)
        {
            $this->rs = mysql_query($sql, $link);
        }
        
        public function fetch($mode)
        {
            switch($mode)
            {
                case PDO::FETCH_ASSOC:
                    return mysql_fetch_assoc($this->rs);
                break;
            }
        }
        
        public function fetchAll($mode)
        {
            switch($mode)
            {
                case PDO::FETCH_ASSOC:
                    $rows = array();

                    while($row=mysql_fetch_assoc($this->rs))
                    {
                        $rows[] = $row;
                    }
                    return $rows;
                break;
            }
        }

        public function rowCount()
        {
            return mysql_num_rows($this->rs);
        }

        public function __destroy()
        {
            mysql_free_result($this->rs);
        }
    }
