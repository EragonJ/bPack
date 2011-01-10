<?php
class bPack_Config_DB extends bPack_Config_Provider
{
    protected $_is_writeable = true;
    private $__config = null;
    protected $_table_name = null;
    protected $_db = null;
    
    public function __construct(PDO $database_source, $database_config_tablename)
    {
        $this->_db = $database_source;
        $this->_table_name = $database_config_tablename;

        $this->reload();
    }
    
    public function reload()
    {
        $raw_configs = $this->_db->query("SELECT * FROM `".$this->_table_name."`;")->fetchAll(PDO::FETCH_ASSOC);
        
        $configs = array();
        foreach($raw_configs as $config)
        {
            $configs[$config['name']] = $config['value'];
        }

        $this->__config = $configs;
        
        return $this;
    }
    
    public function get($config_name)
    {
        return $this->__config[$config_name];
    }
    
    public function set($config_name, $config_value)
    {
        $this->_db->exec("INSERT INTO `".$this->_table_name."` SET `value` = '" . $config_value . "', `name` = '".$config_name."' ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

        $this->reload();

        return true;
    }
}
