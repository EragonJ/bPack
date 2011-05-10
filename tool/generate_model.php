<?php
// bpack g model Document documents name,collection,url,text,length,assign_to

$options = $argv;

if(!isset($argv[1]))
{
    echo "Required model name\n";
    exit;
}

$model_name = ;



$to_write = '<?php

class Model_'.$model_name.' extends bPack_DB_ActiveRecord
{
    protected $table_name = "'.$table_name.'";

    protected $table_column = array(
        "id" => array("tag"=> "autofill_on_create primary_key", "type" => "int(20)"),
        "created_at" => array("tag"=> "autofill_on_create current_timestamp", "type"=> "datetime"),
        "updated_at" => array("tag"=> "autofill_on_create current_timestamp update_every_time", "type" => "datetime"),

        '.$fields.'
    );
}';
