<?php
    class bPack_DB_PDO_MySQL
    {
        private $__engine;

        public function __construct($config)
        {
            $this->__engine =  new buPDO('mysql:host='.$config->get('host').';dbname='.$config->get('name'), $config->get('username'), $config->get('password'));

            $this->__engine->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        public function getEngine()
        {
            return $this->__engine;
        }

        public function set_names_utf8()
        {
            $this->__engine->exec("SET NAMES 'utf8';"); 
            return true;
        }
    }

	class buPDO extends PDO
	{
		protected $_count = 0;
		public function query($sql)
		{
			//$this->_count++;
			//echo "<p>$this->_count.$sql</p>";
			//echo "<hr>";
			return parent::query($sql);
		}
	}
