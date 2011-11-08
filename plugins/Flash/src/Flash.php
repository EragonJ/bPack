<?php

class Plugin_Flash extends bPack_Event_Plugin
{
    protected function pluginInitialization()
    {
        $this->session = new bPack_Session;
    }

    protected function registerFunctions()
    {
        $this->parent
            ->registerPluginFunction('flash_msg', array($this, 'msg'))
            ->registerPluginFunction('flash_expose', array($this, 'expose'));
    }

    public function expose()
    {
        return $this;
    }

    protected $message = '';
    protected $type = 'msg';
    protected $avaliable_type = array('error','success','msg');

    public function getMessage()
    {
        $msg =  $this->session->get('plugin.flash.message');
        $this->session->clear('plugin.flash.message');

        return $msg;

    }

    public function getType()
    {
        $type = $this->session->get('plugin.flash.type');
        $this->session->clear('plugin.flash.type');

        return $type;
    }

    public function isNotEmpty()
    {
        return !($this->session->get('plugin.flash.message') == '');
    }

    public function setType($type)
    {
        if(!in_array($type,$this->avaliable_type))
        {
            throw new Exception('Plugin_Flash: No corrsponding message type.');
        }

        $this->session->set('plugin.flash.type', $type);
    }

    public function setMessage($message)
    {
        $this->session->set('plugin.flash.message', $message);
    }

    public function msg($message, $type = 'msg')
    {
        $this->setType($type);
        $this->setMessage($message);

        return true;
    }
}
