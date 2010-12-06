<?php
    class bPack_DB_PDO_MySQL
    {
        private $__engine;

        public function __construct($config)
        {
            $this->__engine =  new PDO('mysql:host='.$config->get(bPack_Application_Environment . '.host').';dbname='.$config->get(bPack_Application_Environment . '.name'), $config->get(bPack_Application_Environment . '.username'), $config->get(bPack_Application_Environment . '.password'));

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
