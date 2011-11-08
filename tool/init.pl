#!/usr/bin/perl -w

use strict;
use Cwd;
use File::Basename;

print "bPack MVC Project Init Script v0.4\n\n";
print "Are you sure create bPack MVC Project under this directory? (y/N)  ";

my $confirm = <STDIN>;
chomp $confirm;
exit unless ($confirm eq "y" || $confirm eq "Y");

# start creation

# config
mkdir("config",0755);
mkdir("config/dev",0755);

# config/constant.php
open (CONSTANT_FILE_HANDLE, '>>config/constant.php');
print CONSTANT_FILE_HANDLE "<?php
#
# bPack Constants
#";

close (CONSTANT_FILE_HANDLE);

# config/dev/config.php
open (DEV_FILE_HANDLE, ">>config/dev/config.php");
print DEV_FILE_HANDLE "<?php
#
# bPack MVC Environment [Developement]
#

# debugging?
define('bPack_Debug', 1);

# should we enable rewrite? (remove comment mark to enable that)
#define('bPack_Application_RewriteEnabled', true);

# When constructing URL, how should bPack begins with?
define('bPack_Application_BASE_URI','/');";
close (DEV_FILE_HANDLE);

# do
mkdir("do",0755);
mkdir("do/default",0755);

# need do/default/default.php
open (DEFAULT_CONTROLLER_HANDLE, ">>do/default/default.php");
print DEFAULT_CONTROLLER_HANDLE '<?php
# Controller - default_default
# Shown a default welcoe page for developers.

class Controller_default_default extends ApplicationController 
{
    public function startupAction() 
    {
        # do nothing
    }

    public function defaultAction() 
    {
        # default action
        echo "<p>Hi, Welcome aboard. bPack is set up.</p>";
        echo "<p>please check ".$this->response->get_internal_link("default.default/anotherAction")." to try another action.";
        echo "<p>If you found the link different from your current position, please fix the BASE_URL locate in config/".bPack_Application_Environment."/config.php</p>";

        # note: in controller if a function is begun with underscore, that means it is a plugin action (just a convestion)
    }

    public function anotherAction() 
    {
        echo "<p>This is another action</p>";
    }

    public function tearDownAction() 
    {
        # do nothing
    }
}';
close (DEFAULT_CONTROLLER_HANDLE);

# lib
mkdir("lib",0755);
symlink( '/home/bu/Playground/bPack', "lib/bPack" );


mkdir("lib/plugin",0755);

# public
mkdir("public",0755);
mkdir("public/img",0755);
mkdir("public/js",0755);
mkdir("public/css",0755);

# model
mkdir("model", 0755);

# ApplicationController.php
open (APPLICTION_CONTROLLER_HANDLE, ">>model/ApplicationController.php");
print APPLICTION_CONTROLLER_HANDLE '<?php
# Default Application Controller Skelton

class ApplicationController extends bPack_Event_Model 
{
    public function __construct() 
    {
        $this->request = new bPack_Request;
        $this->response = new bPack_Response;
    }

    public function startupAction() 
    {
        # define here to avoid each controller exists empty function
        # but if need for change, it enable possiblity to overwrite.
    }

    public function defaultAction() 
    {
        # throw an expection to notify developer that controller missing a defaultAction(and that may cause issues)
        throw new Exception("ApplicationController: This controller does not have a own defaultAction.");
    }

    public function tearDownAction() 
    {
        # define here to avoid empty function in each controller
    }
}';
close (APPLICTION_CONTROLLER_HANDLE);

# tmp
mkdir("tmp",0777);

# tpl
mkdir("tpl",0755);
mkdir("tpl/controller",0755);
mkdir("tpl/plugin",0755);

# need index.php
open (INDEX_HANDLE, ">>index.php");
print INDEX_HANDLE '<?php
# bPack MVC Front Controller

# define application location
define("bPack_Application_Directory","' . cwd() . '/");
define("bPack_Application_Environment", ((!getenv("bPack_ENV")) ? "dev" : getenv("bPack_ENV")) );

# load config
require bPack_Application_Directory . "config/constant.php";
require bPack_Application_Directory . "config/" . bPack_Application_Environment . "/config.php";

# load bPack Loader
require bPack_Application_Directory . "lib/bPack/model/Loader.php";
bPack_Loader::run();

# parse route for dispatching
$router = new bPack_Router;

if(defined(\'bPack_Application_RewriteEnabled\'))
{
	$router->setRouterModule(new bPack_Router_Rewrite);
}
else
{
	$router->setRouterModule(new bPack_Router_Parameter);
}

$route = $router->getRoute();

# dispatch page to the right position
bPack_Dispatcher::run($route);';
close (INDEX_HANDLE);

# END
print "\n\nbPack MVC inited.\nBut there's few thing should do: \n\n";
print "fix URI prefix in config/dev/config.php, according to your need\n";

