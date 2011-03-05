<?php

abstract class bPack_Event_Model
{
    protected $callable_function_list = array();
    protected $plugin_list = array();
    
    abstract public function defaultAction();
    abstract public function startupAction();
    abstract public function tearDownAction();

    public function plugin_add($plugin_name) 
    {
        $plugin_name = 'Plugin_' . $plugin_name;

        $this->plugin_list[] = new $plugin_name($this);
        
        return true;
    }

    public function addPlugin(bPack_Event_Plugin $plugin)
    {
        $this->plugin_list[] = $plugin;

        return true;
    }

    /* Plugins */
    public function registerPluginFunction($function_name, $callback)
    {
        if(array_key_exists($function_name, $this->callable_function_list))
        {
            throw new bPack_Exception('bPack_Event: plugin function `'.$name.'` had been register before.');
        }

        $this->callable_function_list['_' . $function_name] = $callback;

        return $this;
    }

    public function unregisterPluginFunction($function_name)
    {
        if(array_key_exists($function_name, $this->callable_function_list))
        {
            unset($this->callable_function_list[$function_name]);
        }
        else
        {
            throw new Exception('bPack_Event: No corresponding function were exist.');
        }
    }

    public function __call($name, $argument)
    {
        if(array_key_exists($name, $this->callable_function_list))
        {
            return call_user_func_array($this->callable_function_list[$name],$argument);
        }
        else
        {
            throw new bPack_Exception('bPack_Event_Model->call: no corresponding helper function were found.');
        }
    }
}
