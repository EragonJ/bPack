<?php
//               Model name  Table name  Fields (with p. -> primary key)
// bpack g model Document    documents   "name,collection,url,text,length,assign_to"

if(!isset($argv[1]) || !(isset($argv[2])))
{
    echo "missing needed\n";
    exit;
}

$model_name = $argv[1];
$table_name = $argv[2];

if(isset($argv[3]))
{
	$field = $argv[3];
	$fields = explode(',', $field);
}
else
{
	$fields = array();
}

$to_write = '<?php

class Model_'.$model_name.' extends bPack_DB_ActiveRecord
{
	protected $table_name = \''.$table_name.'\';

	/* tags: autofill_on_create, primary_key, required, current_timestamp, update_everytime */
	/* datatype: datetime, int, varchar, text, char,... */

	protected $table_column = array(';

if(sizeof($fields) == 0) 
{
	$fields = array("p.id");
}

$to_write .= "\n";
$current_pos = 0;
$sizeof_field = sizeof($fields);

foreach($fields as $field_name)
{
	$field_name = trim($field_name);
	if(strpos($field_name, "p.") !== FALSE)
	{
		$field_name = str_replace('p.','',$field_name);

		$to_write .= "\t\t" . "'$field_name' => array('tag'=>'primary_key', 'type'=>'int(20)')";
	}
	else
	{
		$to_write .= "\t\t". "'$field_name' => array('tag'=>'required', 'type'=>'varchar(32)')";
	}

	$to_write .= ((++$current_pos < $sizeof_field) ? ",\n": "") ;
}

$to_write .= "\n\t" . ');' . "\n";
$to_write .= '}'. "\n";

if(!file_exists('model/Model/'))
{
	mkdir('model/Model');
}

file_put_contents('model/Model/' . $model_name . '.php', $to_write);
