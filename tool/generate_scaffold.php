<?php

define('bPack_Application_Directory', getcwd() . "/");
define('bPack_Application_Environment','dev');

require "./lib/bPack/model/Loader.php";
bPack_Loader::run();

// 1,2,3,4 = controller, module, modelname, modelprual

$module = $argv[1];
$controller = $argv[2];

$modelname = $argv[3];

$short = $argv[4];
$shorts = $argv[5];

// get columns

$classname = "Model_$modelname";
$obj = new $classname;
$col = $obj->getColumns();
$obj = null;

// prepare vars

$lower_model = strtolower($modelname);

//list: lowercase_model, short, shorts, heading_field, data_field, module, controller
if(true)
{
	$list = file_get_contents('/home/bu/Playground/bPack/tool/scaffold_template/list.html');

	$list = str_replace('%lowercase_model%', $lower_model, $list);
	$list = str_replace('%short%', $short, $list);
	$list = str_replace('%shorts%', $shorts, $list);
	$list = str_replace('%module%', $module, $list);
	$list = str_replace('%controller%', $controller, $list);

	$heading_fields = '';
	$data_fields = '';

	foreach($col as $k=>$v)
	{
		$heading_fields .= "<th>$k</th>\n";
		$data_fields .= "<td>{{ $short.$k }}</td>\n";
	}

	$list = str_replace('%heading_field%', $heading_fields, $list);
	$list = str_replace('%data_field%', $data_fields, $list);

	file_put_contents(bPack_Application_Directory . "tpl/$module/$controller/list.html", $list);

	$list = '';
}

// create: lowercase_model, field_boxes
if(true)
{
	$create = file_get_contents('/home/bu/Playground/bPack/tool/scaffold_template/create.html');
	$create = str_replace('%lowercase_model%', $lower_model, $create);
	$create = str_replace('%module%', $module, $create);
	$create = str_replace('%controller%', $controller, $create);

	$modify = file_get_contents('/home/bu/Playground/bPack/tool/scaffold_template/modify.html');
	$modify = str_replace('%lowercase_model%', $lower_model, $modify);
	$modify = str_replace('%module%', $module, $modify);
	$modify = str_replace('%short%', $short, $modify);
	$modify = str_replace('%controller%', $controller, $modify);


	$field_boxes = $field_boxes_modify = '';

	foreach($col as $k => $v)
	{
		$field_boxes .= "<div class=\"clearfix\">
				<label for=\"$k\">$k</label>

				<div class=\"input\">
					<input type=\"text\" name=\"$k\" size=\"10\" />
				</div>
			</div>\n\n";

		$field_boxes_modify .= "<div class=\"clearfix\">
				<label for=\"$k\">$k</label>

				<div class=\"input\">
					<input type=\"text\" name=\"$k\" size=\"10\" value=\"{{".$short.".$k}}\" />
				</div>
			</div>\n\n";

	}

	$create = str_replace('%field_boxes%', $field_boxes, $create);
	$modify = str_replace('%field_boxes%', $field_boxes_modify, $modify);

	file_put_contents(bPack_Application_Directory . "tpl/$module/$controller/create.html", $create);
	file_put_contents(bPack_Application_Directory . "tpl/$module/$controller/modify.html", $modify);
}
