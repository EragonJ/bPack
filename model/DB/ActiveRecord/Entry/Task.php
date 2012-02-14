<?php

interface bPack_DB_ActiveRecord_Entry_Task
{
	public function executeRead(array &$data);
	public function executeWrite(array &$data);
}
