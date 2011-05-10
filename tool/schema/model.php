<?php

class Model_Document extends bPack_DB_ActiveRecord
{
    protected $table_name = 'documents';

    protected $table_column = array(
        'title' => array('tag'=>'required', 'type' => 'text'),
        'param' => array('tag'=>'required', 'type'=>'text'),
        'page_module' => array('tag'=>'required', 'type'=> 'text'),

        'parent' => array('tag'=>'required has_default_value', 'type' => 'int(20)', 'default'=> 0),
        'slug' => array('tag'=>'required', 'type' => 'varchar(64)'),

        'ordinal' => array('tag'=> 'default', 'type' => 'int(20)', 'default' => 0),

        'id' => array('tag'=> 'autofill_on_create primary_key', 'type' => 'int(20)'),
        'created_at' => array('tag'=> 'autofill_on_create current_timestamp', 'type'=> 'datetime'),
        'updated_at' => array('tag'=> 'autofill_on_create current_timestamp update_every_time', 'type' => 'datetime')
    );
}
