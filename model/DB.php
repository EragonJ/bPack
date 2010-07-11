<?php
class bPack_DB
{
    protected static $_instance = null;
    
    static public function getInstance()
    {
        if(is_null(self::$_instance))
        {
            $database_config = bPack_Config::getInstance()->setProvider(new bPack_Config_YAML(bPack_App_ConfigDir . 'database.yml'));
            $adaptor_name = $database_config->get(bPack_APP_ENV . '.adapter');
            
            $db_obj = new $adaptor_name($database_config);
        
            self::$_instance = $db_obj->getEngine();
        }
        
        return self::$_instance;
    }
}