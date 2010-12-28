<?php

class bPack_DB_PDO_SQLite
{
    private $__engine;

    public function __construct($config)
    {
        $this->__engine =  new PDO("sqlite:".bPack_Application_Directory.$config->get('database_path'));

        $this->__engine->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function getEngine()
    {
        return $this->__engine;
    }
}
