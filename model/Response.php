<?php

class bPack_Response
{
    public function go($address = '')
    {
        $this->redirect($this->generateAddress($this->addressParse($address)));
    }

    public function IsAttachment($filename, $mime)
    {
        if(headers_sent())
        {
            throw new bPack_Exception('bPack_Response: IsAttachment called, but some content had been sent out.');
        }

        header("Content-type: $mime");
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        return true;
    }


    public function addressParse($address)
    {
        $parser = new GoParser;
        return $parser->parse($address);
    }

    public function get_internal_link($address = '')
    {
        return $this->generateAddress($this->addressParse($address));
    }

    public function generateAddress($route)
    {
        if(defined('bPack_Application_RewriteEnabled'))
        {
            return $this->generateRewriteAddress($route);
        }

        $address = 'index.php?module=';
        $address .= $route->module;
        $address .= '&controller=';
        $address .= $route->controller;
        $address .= '&action=';
        $address .= $route->action;

        if(sizeof($route->parameters) > 0)
        {
            foreach($route->parameters as $k=>$v)
            {
                $address .= "&$k=".urlencode($v);
            }
        }

        return $address;
    }

    public function generateRewriteAddress($route)
    {
        $address = bPack_Application_BASE_URI;
        $address .= $route->module;
        $address .= '/';
        $address .= $route->controller;
        $address .= '/';
        $address .= $route->action;

        if($parameters_numbers = sizeof($route->parameters) > 0)
        {
            $address .= '?';

            $i = 0;
            foreach($route->parameters as $k=>$v)
            {
                $i++;
                $address .= "$k=".urlencode($v);
                
                if(($parameters_numbers-$i) > 0)
                {
                    $address .= '&';
                }
            }

        }

        return $address;

    }

    public function redirect($address, $http_status_code = 302)
    {
        // http_status_code = 301 permanently moved, 302 found, 307 temporaily moved, 303 see other
        if(headers_sent())
        {
            echo "<script type=\"text/javascript\">window.location.href='$address';</script>";
            exit;
        }
        else
        {
            header('location: ' . $address, true, $http_status_code);
        }
    }
}

class GoParser 
{
    function parsePath()
    {
        # exists /
        # if so /action
        # if not /action = default
        $action_spliter_location = strpos($this->path, '/');

        if($action_spliter_location === FALSE)
        {
            $this->route->action = 'defaultAction';

            $host = substr($this->path, 0, strlen($this->path));
        }
        else
        {
            $this->route->action = substr($this->path, ( $action_spliter_location + 1 ), (strlen($this->path) - $action_spliter_location));
        
            $host = substr($this->path, 0, $action_spliter_location);
        }

        
        # exists .
        # if not route = module
        # if not, split . into two part
        # module.controller
        $module_controller_spliter_location = strpos($host, '.');

        if($module_controller_spliter_location === FALSE)
        {
            if($host == '')
            {
                $this->route->module = 'default';
            }
            else
            {
                $this->route->module = $host;
            }

            $this->route->controller = 'default';
        }
        else
        {
            $host_parts = explode('.', $host);
            
            list($module, $controller) = $host_parts;

            if($module == '')
            {
                $module = 'default';
            }

            if($controller == '')
            {
                $controller = 'default';
            }

            $this->route->module = $module;
            $this->route->controller = $controller;
        }
    }

    function parseParameters()
    {
        parse_str($this->query, $query);

        $this->route->parameters = $query;
    }

    function parseAddress()
    {
        $address_parts = parse_url($this->address);

        $this->path = (isset($address_parts['path'])) ? $address_parts['path'] : '';
        $this->query = (isset($address_parts['query'])) ? $address_parts['query'] : '';

        return true;
    }

    function parse($address) 
    {
        $this->route = new bPack_DataContainer;
        $this->address = $address;

        $this->parseAddress();

        $this->parseParameters();

        $this->parsePath();

        return $this->route;
    }
}
