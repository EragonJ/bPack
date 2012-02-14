<?php

class bPack_DB_ActiveRecord_Entry_Task_JSON implements bPack_DB_ActiveRecord_Entry_Task
{
	public function executeRead(array &$data)
	{
		foreach($data as $key => &$value)
		{
			if( $this->isJSONSerialized($value) )
			{
				$value = $this->extractDataFromString($value);
			}
		}

		return true;
	}

	public function executeWrite(array &$data)
	{
		foreach($data as $key => &$value)
		{
			if( is_array($value) )
			{
				$value = $this->serializeDataToString($value);
			}
		}

		return true;
	}

	protected function isJSONSerialized($value)
	{
		return (strpos($value, '__JSON__') !== FALSE);
	}

	protected function extractDataFromString($string)
	{
		$string = str_replace('__JSON__', '', $string);

		return json_decode($string);
	}

	protected function serializeDataToString($value)
	{
		return '__JSON__' . json_encode($value);
	}
}
