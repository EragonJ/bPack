<?php

$options = $argv;

$actions = array(
    'defaultAction',
    'startupAction',
    'tearDownAction'
);

if(isset($argv[1])) {
    $module = strtolower($argv[1]); 
}
else
{
    $module = 'default';
}

if(isset($argv[2])) 
{
    $controller = strtolower($argv[2]);
}
else
{
    $controller = 'default';
}

for($i=3;$i<=(sizeof($argv));$i++) {
    if(isset($argv[$i])) {
        $actions[] = $argv[$i];
    }
}

$ApplicationController = ucfirst($module) . 'Controller';

$controller_file = "<?php

class Controller_{$module}_{$controller} extends {$ApplicationController} \n\n{\n";

foreach($actions as $action_method) {
    $controller_file .= "    public function {$action_method}() \n    {\n        #TODO: {$action_method}\n    }\n\n";
}

$controller_file .= "}";

if(!file_exists('do/'.$module)) {
    mkdir('do/'.$module);
}

file_put_contents('do/'.$module.'/'.$controller.'.php', $controller_file);

echo "\n\n";
echo 'Successfully Created!!';
echo "\n\n";
