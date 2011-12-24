<?php

class bPack_Image
{
	public function __construct($image_path = '')
	{
		if($image_path == '')
		{
			throw new bPack_Exception('bPack_Image: no image input.');
		}

		if (!file_exists($image_path))
		{
			throw new bPack_Exception('bPack_Image: image not exist.');
		}
		
		/* image file path */
		$this->_image_path = $image_path;
		
		/* given functions some metadata */
		$image_info = getimagesize($image_path);
		$this->_image_info = array('width'=> $image_info[0], 'height' => $image_info[1], 'type' => $image_info['mime']);
		
		/* create source image resource */
		$this->_image_resource = $this->_createImageResource();	
	}

	protected function _getImageTypeClass($type)
	{
		$object_type = str_replace('/', '_', $type);

		return 'bP_' . $object_type;
	
	}

	protected function _createImageResource()
	{
		$class_name = $this->_getImageTypeClass($this->_image_info['type']);
		return $class_name::read($this->_image_path);
	}

	protected function _calcPrecentageSize($limit)
	{
		$percentage = $limit['percentage'];

		return array();
	}

	protected function _calcLimitSizeOneSide($limit)
	{
		$height_existence = isset($limit['height']);

		// which side this is?
		$side = ($height_existence) ? 'height' : 'width';
		$side_value = ($height_existence) ? $limit['height'] : $limit['width'];

		// if not exist both, the other side would be set in scale
		$other_side = (($side == 'height') ? 'width' : 'height');
		$other_side_value =  $this->_getIntegerRounded($this->_image_info[$other_side] * $side_value / $this->_image_info[$side]);

		return array( $side => $side_value, $other_side => $other_side_value);
	}

	protected function _getIntegerRounded($float_val)
	{
		return intval(round($float_val));
	}

	protected function _calcLimitSizeTwoSide($limit)
	{
		$current_width = $this->_image_info['width'];
		$current_height = $this->_image_info['height'];

		while( !(($current_height <= $limit['height']) && ($current_width <= $limit['width'])) )
		{
			if($current_width > $limit['width'])
			{
				$ratio = $limit['width'] / $current_width;

				$current_width = $limit['width'];
				$current_height = $this->_getIntegerRounded($current_height * $ratio);
			}
			else
			{
				$ratio = $limit['height'] / $current_height;

				$current_height = $limit['height'];
				$current_width = $this->_getIntegerRounded($current_width * $ratio);
			}
		}

		return array('width' => $current_width, 'height' => $current_height);
	}

	protected function _calcLimitSize($limit)
	{
		$height_existence = isset($limit['height']);
		$width_existence = isset($limit['width']);

		if($height_existence && $width_existence)
		{
			return $this->_calcLimitSizeTwoSide($limit);
		}

		return $this->_calcLimitSizeOneSide($limit);
	}

	protected function _calcPercentageSize($limit)
	{
		$percentage = $limit['percentage'];

		$width = $this->_getIntegerRounded($this->_image_info['width'] * $percentage / 100);
		$height = $this->_getIntegerRounded($this->_image_info['height'] * $percentage / 100);

		return array('width' => $width, 'height' => $height);
	}

	public function calcResizedSize($limit = null)
	{
		if(is_null($limit))
		{
			throw new bPack_Exception('bPack_Image: resize without limit');
		}

		/* 
			we perform a check if percentage limit had been set and at the same time given a max width and height,
			that could cause a confuse that which one we should obey.
		*/

		$percentage_existence = isset($limit['percentage']);
		$width_height_existence = isset($limit['width']) || isset($limit['height']);

		if( $percentage_existence && $width_height_existence)
		{
			throw new bPack_Exception('bPack_Image: percentage limit cannot be used with width/height setting');
		}

		/* we start to calc */

		if($percentage_existence)
		{
			$targetSetting = $this->_calcPercentageSize($limit);
		}
		elseif($width_height_existence)
		{
			$targetSetting = $this->_calcLimitSize($limit);
		}
		else
		{
			throw new bPack_Exception('bPack_Image: no limit mode fit');
		}

		return $targetSetting;
	}

	public function resize($limit = null)
	{
		$targetSetting = $this->calcResizedSize($limit);

		$temp_image_resource = imagecreatetruecolor($targetSetting['width'], $targetSetting['height']);

		$resize_result = imagecopyresampled($temp_image_resource, $this->_image_resource, 0, 0, 0, 0, $targetSetting['width'], $targetSetting['height'], $this->_image_info['width'], $this->_image_info['height']);

		if($resize_result)
		{
			/* free old pic, and replace with new one. */
			imagedestroy($this->_image_resource);
			$this->_image_resource = $temp_image_resource;
		}
		else
		{
			throw new bPack_Exception('bPack_Image: resize failed');
		}

		return $this;
	}

	public function saveAs($dest = './', $filename = null, $file_type = 'image/jpeg')
	{
		$class_name = $this->_getImageTypeClass($file_type);

		return $class_name::write($this->_image_resource, $dest . $filename);
	}

	/* save back to the original */
	public function save()
	{
		$save_result = $this->saveAs( dirname($this->_image_path), "tmp_" . basename($this->_image_path) , $this->_image_info['type']);

		if($save_result)
		{
			imagedestroy($this->_image_resource);
			unlink($this->_image_path);

			rename( ".tmp_" . $this->_image_path , $this->_image_path);

			return true;
		}
		
		return false;
	}
}

class bP_image_jpeg
{
	static function read($path)
	{
		return imagecreatefromjpeg($path);
	}

	static public function write($resource, $filepath)
	{
		return imagejpeg($resource, $filepath);
	}
}

class bP_image_gif
{
	static function read($path)
	{
		return imagecreatefromgif($path);
	}

	static public function write($resource, $filepath)
	{
	
	}
}

class bP_image_bmp
{
	static function read($path)
	{
		return imagecreatefromwbmp($path);
	}

	static public function write($resource, $filepath)
	{
	
	}
}

class bP_image_png
{
	static public function read($path)
	{
		return imagecreatefrompng($path);
	}

	static public function write($resource, $filepath)
	{
	
	}
}
