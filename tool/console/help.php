<?php

$helps = array(
	'init' => 'Initlization a new bpack project here [void]',
	'help' => 'Provides command list and description [void]',

	'modify.command' => 'Provide a shortcut for command debugging (module action)',
	'modify.help' => 'Modify the help file [void]',
	'modify' => 'Provided a shortcut for editing bpack main script [void]',

	'controller.list' => 'Provided a list of current project Controllers',

	'bundle.init' => 'Init the bundle setting [void]',
	'bundle.modify' => 'Modify the bundle setting [void]',
	'bundle.update' => 'Update the project bundles [void]',

	'database.mysql' => 'Set up database settings (host, username, password, database)',

	'generate.model' => 'Generate the model file for project (Model name, tablename, column)',
	'generate.scaffold' => 'Generate the scaffold (project module, project controller, model name, model name in purual form)',

	'model.init' => 'Create to database schema, based on Model setting [void]',
	'model.destroy' => 'Destroy the model schema [void]',
	'model.list' => 'List all models [void]',
	'model.create' => 'Generate the model file for project (Model name, tablename, column)',
);

if(isset($argv[2]))
{
	$asking_command = $argv[1].".". $argv[2];
}
else
{
	$asking_command = $argv[1];
}

if(array_key_exists($asking_command, $helps))
{
	echo $helps[$asking_command];
}
else
{
	echo "not yet";
}
