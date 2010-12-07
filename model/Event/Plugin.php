<?php

abstract class bPack_Event_Plugin
{
    protected $parent = null;

    final public function __construct(bPack_Event_Model $parent)
    {
        $this->parent = $parent;

        // setup plugin dependency
        $this->pluginInitialization();
        // register plugin functions to the Controller
        $this->registerFunctions();
    }

    abstract protected function pluginInitialization();
    abstract protected function registerFunctions();
}
