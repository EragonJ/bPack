<?php
/*
	bPack_DB - a database wrapper for bPack
*/

class bPack_DB
{
    protected static $_instance = null;

    static public function getInstance()
    {
        #
        # if no instance created
        # create a new one

        if(is_null(self::$_instance))
        {
            $database_config = bPack_Config::getInstance()
                ->setProvider(new bPack_Config_YAML(bPack_Application_Directory . 'config/' . bPack_Application_Environment. '/database.yaml'));

            $adaptor_name = $database_config->get('adaptor');
            
            $db_obj = new $adaptor_name($database_config);
        
            self::$_instance = $db_obj->getEngine();

            if($action = $database_config->get('post_do'))
            {
                $db_obj->{$action}();
            }
        }
        
        return self::$_instance;
    }
}
