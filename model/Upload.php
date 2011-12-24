<?php

class bPack_Upload
{
	private $_field_name;

	protected $_handlers = array();
	protected $_current_handler = null;

	protected $_file_name = 'untitled';
	protected $_file_ext = 'tmp';

	public function __construct($field_name)
	{
		$this->_setField($field_name);

		$this->addHandler(new bPack_Upload_XHR);
		$this->addHandler(new bPack_Upload_Form);
	}

	public function addHandler(bPack_Upload_Handler $handle_obj)
	{
		$this->_handlers[] = $handle_obj;

		$handle_obj->setFieldName($this->_field_name);
	}

	protected function _setField($field_name = NULL)
	{
		if(is_null($field_name)) 
		{
			throw new bPack_Exception("Field name not given.");
		}

		$this->_field_name = $field_name;
	}

	public function checkUpload()
	{
		foreach($this->_handlers as $handler)
		{
			if($handler->match())
			{
				$this->_current_handler = &$handler;

				return true;
			}
		}

		throw new bPack_Exception('No handler fit request');
		return false;
	}

	public function setFilename($filename)
	{
		$this->_file_name = $filename;

		return $this;
	}

	public function setExt($ext)
	{
		$this->_file_ext = $ext;

		return $this;
	}

	public function getFilename($with_ext = true)
	{
		if($with_ext)
		{
			return $this->_file_name . '.' . $this->_file_ext;
		}

		return $this->_file_name;
	}

	public function saveTo($save_path = './')
	{
		 return $this->_current_handler->moveTo($save_path, $this->getFilename());
	}
}

abstract class bPack_Upload_Handler
{
	public function setFieldName($field_name)
	{
		$this->_field_name = $field_name;

		return true;
	}

	abstract public function match();
	abstract public function moveTo($save_path, $filename);
	abstract public function getSize();
}

class bPack_Upload_XHR extends bPack_Upload_Handler
{
	public function match()
	{
		$xhr_header = bPack_Request::server('HTTP-X-Requested-With', NULL); 

		return (!is_null($xhr_header));
	}

	public function getSize()
	{
		return bPack_Request::server('Content-Length', 0);
	}

	public function moveTo($save_path, $filename)
	{
		$input = fopen("php://input", "r");
        $temp = tmpfile();

        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ( $realSize != $this->getSize() )
		{
            return false;
        }
        
        $target = fopen($save_path . $filename, "w");        
        fseek($temp, 0, SEEK_SET);

        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
	}
}

class bPack_Upload_Form extends bPack_Upload_Handler
{
	public function match()
	{
		return ( (isset($_FILES[$this->_field_name])) && ($_FILES[$this->_field_name]['tmp_name']!="") );
	}

	public function moveTo($save_path, $filename)
	{
		if(is_uploaded_file($this->_getTempFilePath()))
		{
			return move_uploaded_file($this->_getTempFilePath(), $save_path . $filename);
		}

		throw new bPack_Exception("Request file is not uploaded by PHP");

	}

	protected function _getTempFilePath()
	{
		return $this->_FILES[$this->_field_name]['tmp_name'];
	}

	public function getSize()
	{
		return $this->_FILES[$this->_field_name]['size'];
	}
}
