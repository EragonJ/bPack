<?php

class bPack_Upload
{
	protected $field_name;
	protected $new_filename;
	protected $new_save_path;
	
	public function setFieldName($field_name = NULL)
	{			
		$this->field_name = $field_name;
		$this->new_filename = '';
		$this->new_save_path = './';
		
		return $this;
	}
	
	public function isUploaded()
	{			
		if( (isset($_FILES[$this->field_name])) && ($_FILES[$this->field_name]['tmp_name']!="") ) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function setName($new_filename = NULL)
	{
		if($new_filename == NULL)
		{
			$new_filename = md5(microtime(true));
		}
		
		$this->new_filename = $new_filename;
		
		return true;
	}
	
	public function getExt()
	{
		return strtolower(substr($_FILES[$this->field_name]['name'], strrpos($_FILES[$this->field_name]['name'] , ".")+1));
	}		
	
	public function getName()
	{
		if(empty($this->new_filename))
		{
			$this->new_filename = md5(microtime(true));
		}
		
		return $this->new_filename;
	}
	
	public function setSavePath($path = './')
	{
		$this->new_save_path = $path;
		
		return true;
	}				
	
	public function getSavePath()
	{			
		return $this->new_save_path;
	}		
	
	public function save()
	{			
		$save_filename = $this->getName() . '.' . $this->getExt();
		
		$flag = copy($_FILES[$this->field_name]['tmp_name'], $this->new_save_path. $save_filename);
		
		return $flag;
	}
	
	public function getFullPath()
	{
		$save_filename = $this->getName() . '.' . $this->getExt();
		
		return $this->new_save_path. $save_filename;
	}		
	
	public function getFullName()
	{
		$save_filename = $this->getName() . '.' . $this->getExt();
		
		return $save_filename;
	}
}
