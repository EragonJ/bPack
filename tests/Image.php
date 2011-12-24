<?php
require '../model/Image.php';
require '../model/ErrorHandler.php';

class ImageTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		/* as we know, this test pic is a JPEG image by width 539px and height 719px */
		$this->_image = new bPack_Image('ImageTestPic.jpg');
	}


	/**
     * @expectedException bPack_Exception
     */
	public function testNoPathGivenException()
	{
		$this->_image = new bPack_Image;
	}

	/**
     * @expectedException bPack_Exception
     */
	public function testResize_NullLimit()
	{
		$this->_image->calcResizedSize();
	}

	/**
	 * @expectedException bPack_Exception
	 */
	public function testResize_LimitPercentageWidthHeightExistAtOneTime()
	{
		$this->_image->calcResizedSize(array('percentage' => 100, 'width' => 300, 'height' => 50));
	}

	public function testResize_LimitWidth()
	{
		$targetSetting = $this->_image->calcResizedSize(array('width' => 300));

		$assertedSetting = array('height' => 400, 'width' => 300);

		$this->assertEquals($assertedSetting, $targetSetting);
	}

	public function testResize_LimitHeightWidth()
	{
		$targetSetting = $this->_image->calcResizedSize(array('width' => 100, 'height' => '100'));

		$assertedSetting = array('height' => 100, 'width' => 75);

		$this->assertEquals($assertedSetting, $targetSetting);
	}

	public function testResize_LimitPercentage()
	{
		$targetSetting = $this->_image->calcResizedSize(array('percentage' => 50));

		$assertedSetting = array('height' => 360, 'width' => 270);

		$this->assertEquals($assertedSetting, $targetSetting);
	}

	public function testResizeSaveAs()
	{
		$targetSetting = $this->_image->resize(array( 'percentage' => 50))->saveAs('./', 'testPic.test.jpg', 'image/jpeg');

		$assertedSetting = array('height' => 360, 'width' => 270);

		$actuallyProduced = getimagesize('./testPic.test.jpg');

		$actuallySetting = array('height' => $actuallyProduced[1], 'width' => $actuallyProduced[0]);

		$this->assertEquals($assertedSetting, $actuallySetting);

		unlink('./testPic.test.jpg');
	}

	public function testResizeSave()
	{
		copy('ImageTestPic.jpg', 'ImageTestPic.jpg.bak');

		$targetSetting = $this->_image->resize(array( 'percentage' => 50))->save();

		$assertedSetting = array('height' => 360, 'width' => 270);

		$actuallyProduced = getimagesize('./ImageTestPic.jpg');

		$actuallySetting = array('height' => $actuallyProduced[1], 'width' => $actuallyProduced[0]);

		$this->assertEquals($assertedSetting, $actuallySetting);

		unlink('./ImageTestPic.jpg');

		rename('ImageTestPic.jpg.bak', 'ImageTestPic.jpg');
	}

	public function testResize_IfSourceIsSmallerThanLimitOneSide()
	{
		unset($this->_image);

		$this->_image = new bPack_Image('ImageTestPic2.gif');
		$assertedSetting = array('height' => 147, 'width' => 150);

		$targetSetting = $this->_image->calcResizedSize(array( 'width' => 400));
		$this->assertEquals($assertedSetting, $targetSetting);

	}

	public function testResize_IfSourceIsSmallerThanLimitTwoSide()
	{	
		unset($this->_image);

		$this->_image = new bPack_Image('ImageTestPic2.gif');
		$assertedSetting = array('height' => 147, 'width' => 150);

		$targetSetting = $this->_image->calcResizedSize(array( 'width' => 400, 'height' => 300));
		$this->assertEquals($assertedSetting, $targetSetting);
	}

	public function tearDown()
	{
		unset($this->_image);
	}
}
