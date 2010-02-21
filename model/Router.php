<?php
    abstract class bPack_Router
    {
        protected $_routes = array();
        protected $_singleton;
        
        public function parse()
        {
            // pull routes in to router
            $this->draw();
            
            // check each route if match
            foreach($this->_routes as $route)
            {
                // if the source is match the route
                if($route->matchRoute() === true)
                {
                    return $route->getRoute();
                }
            }
            
            // else, throw exception alert user
            throw new bPack_Exception('bPack_Router: No matched router');
            
            return false;
        }
        
        protected function draw()
        {
            $this->map(new bPack_Route_Default);
        }
        
        protected function map(bPack_Route $route)
        {
            $this->_routes[] = $route;
            
            return true;
        }
    }
    
    abstract class bPack_Route
    {
        protected $_pattern, $_URI;
        
        abstract public function matchRoute();
        abstract public function getRoute();
        
	public function __construct()
	{
            $this->_URI = $_SERVER['REQUEST_URI'];
            
            $this->cleanURI();
	}
        
        protected function cleanURI()
	{
		$this->_URI = str_replace(bPack_BASE_URI,'',$this->_URI);

		return $this;
	}
    }
    
    class bPack_Route_Default extends bPack_Route
    {
        public function matchRoute()
        {
            // default route fit all situation
            return true;
        }
        
        public function getRoute()
        {
            $this->cleanParam()->splitRoute();

            return $this->_RouteInformation();
        }

        protected $_URI;
	protected $_URI_Array;
        
	protected function _baseURI_Length()
	{
	    return strlen(bPack_BASE_URI);
	}

	protected function _RequestURI_Length()
	{
	    return strlen($this->_URI);
	}

	protected function _Param_Length()
	{
	    return strlen(strstr($this->_URI,'?'));
	}
        
	protected function cleanParam()
	{
            /**
             * Check if params are exists
             */
            if(strstr($this->_URI,'?'))
            {
                    $this->_URI = substr($this->_URI,0, $this->_RequestURI_Length() - $this->_Param_Length());
            }

            return $this;
	}

	protected function splitRoute()
	{
	    $this->_URI_Array = explode('/',$this->_URI);
	}

	protected function _URI_arrayValue( $needle_key , $default_value)
	{
            if(isset($this->_URI_Array[$needle_key]) && !(empty($this->_URI_Array[$needle_key])))
            {
                return $this->_URI_Array[$needle_key];
            }
            else
            {
                return $default_value;
            }
	}

	protected function _RouteInformation()
	{
            $route_obj = new bPack_DataContainer();

            $route_obj->module = $this->_URI_arrayValue(0 , 'default');
            $route_obj->controller = $this->_URI_arrayValue(1 , 'default');
            $route_obj->action = $this->_URI_arrayValue(2 , 'defaultAction');

            return $route_obj;
	}
    }