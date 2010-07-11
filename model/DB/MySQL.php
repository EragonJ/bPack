<?php
    class bPack_DB_MySQL
    {
        private $__engine;
        public function __construct($config)
        {
            $this->__engine =  new PDO('mysql:host='.$config->get(bPack_APP_ENV . '.host').';dbname='.$config->get(bPack_APP_ENV . '.name'), $config->get(bPack_APP_ENV . '.user'), $config->get(bPack_APP_ENV . '.password'));
            $this->__engine->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        public function getEngine()
        {
            return $this->__engine;
        }
    }