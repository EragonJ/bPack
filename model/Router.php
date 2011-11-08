<?php

class bPack_Router_Parameter implements bPack_Router_Module
{
	public function getRoute()
	{
		# parse route for dispatching
		$route = new bPack_DataContainer;
		$route->module = bPack_Request::get("module", "default");
		$route->controller = bPack_Request::get("controller", "default");
		$route->action = bPack_Request::get("action" , "defaultAction");

		return $route;
	}
}

class bPack_Router_Rewrite_DefaultRoute extends Route
{
	public function match($uri)
	{
		return true;
	}

	public function draw($uri)
	{
		# do/hint/show
		if(sizeof($rs = explode('/', $uri)) > 2)
		{
			return "{$rs[0]}.{$rs[1]}/{$rs[2]}";
		}
		
		return str_replace('/', '.', $uri);
	}
}

class bPack_Router_Rewrite implements bPack_Router_Module
{
	protected $routes = array();
	
	public function loadRoute()
	{
		/* if user has user-defined route */
		if(file_exists(bPack_Application_Directory . 'model/Route/'))
		{
			$directory = new DirectoryIterator(bPack_Application_Directory . 'model/Route/');

			foreach($directory as $fileinfo)
			{
				if(!$fileinfo->isDot() && $fileinfo->isFile())
				{
					$route_object_name = "Route_" . $fileinfo->getBasename('.php');
					$this->routes[] = new $route_object_name;
				}
			}
		}

		$this->routes[] = new bPack_Router_Rewrite_DefaultRoute;
	}

	protected function prepareRequestURI()
	{
		$raw_request_uri = str_replace(bPack_Application_BASE_URI, '', $_SERVER['REQUEST_URI']);
		$request_uri_array = parse_url($raw_request_uri);

		$request_path = $request_uri_array['path'];

		while(substr($request_path, -1) == '/')
		{
			$request_path = substr($request_path, 0, (strlen($request_path) - 1));
		}
	
		while(substr($request_path, 0,  1) == '/')
		{
			$request_path = substr($request_path, 1, (strlen($request_path) -1 ));
		}
	
		$this->request_uri = $request_path;
	}

	protected function getRouteObject($action_uri = '')
	{
	
		/* we will get a bpack action uri in return, which is needed transaction  */
		require_once bPack_Application_Directory . 'lib/bPack/model/Response.php';

		$action_parser = new GoParser();
		$route = $action_parser->parse($action_uri);

		/* mapping goparser parameter with dispatcher parameter */
		$route->parameter = $route->parameters;

		return $route;
	}

	public function getRoute()
	{
		/* analyzse the URI and give out the route */
		$this->prepareRequestURI();

		/* load series of route */
		$this->loadRoute();
		
		/* check each route if it's capable */
		foreach($this->routes as $route)
		{
			if($route->match($this->request_uri) === True)
			{
				$route_obj =  $this->getRouteObject($route->draw($this->request_uri));
				$route_obj->generateBy = $route->getName();

				return $route_obj;
			}
		}

		/* if no route is capable, then return default action */
		return $this->getRouteObject('default.default/defaultAction');
	}

}

interface bPack_Router_Module
{
	public function getRoute();
}

class bPack_Router
{
	protected $module = null;

	public function setRouterModule(bPack_Router_Module $obj)
	{
		$this->module = $obj;
	}

	public function getRoute()
	{
		if($this->module == null)
		{
			throw new Exception('no rewrite module');
		}

		return $this->module->getRoute();
	}
}

abstract class Route
{
	abstract public function match($destination);
	abstract public function draw($destination);

	public function getName()
	{
		return get_class($this);
	}


}
