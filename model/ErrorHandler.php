<?php
/**
 * Error handling classes
 *
 * @author bu <bu@hax4.in>
 * @package bPack
 * @subpackpage ErrorHandler
 */
class bPack_ErrorHandler
{
    /**
     * Setup the bPack ErrorHandler functions
     * 
     * @param void
     * @return void
     */
    final public static function setup()
    {
        set_exception_handler(array( 'bPack_ErrorHandler', 'exception_handler'));
        set_error_handler(array('bPack_ErrorHandler', 'error_handler'), E_ALL);
    }

    /**
     * "Error" Handler function
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * 
     * @return void
     * 
     */
    final public static function error_handler($errno, $errstr, $errfile, $errline)
    {
            throw new bPack_ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * "Exception" Handler Function 
     *
     * @param Expection $e
     * @return void
     */
    final public static function exception_handler($e)
    {
        self::_log($e->getMessage(),$e->getFile(),'EXCEPTION',$e->getTraceAsString());
        self::_display($e->getMessage(),$e->getFile(),'EXCEPTION',$e->getTraceAsString(),$e->getCode());
        exit;
    }

    /**
     * Logging method
     * 
     * @param string $msg
     * @param string $file
     * @param int $type
     * @param string $detail
     *
     * @return bool the result of logging
     */
    protected static function _log($msg,$file,$type ,$detail = '')
    {
        return true;
    }

    /**
     * Error Display method
     * 
     * @param string $msg
     * @param string $file
     * @param int $type
     * @param string $detail
     */
    protected static function _display($msg,$file,$type ,$detail = '')
    {
        if(!defined('bPack_CLI_MODE'))
        {
            echo '<p style="color:red;">'.$type.'</p><p style="color:darkred;"><b>'.str_replace(':',':</b>',$msg) . '</p><p style="color:darkblue;">' . $file.'</p><p style="color:green;">'.nl2br($detail).'</p>';
        }
        else
        {
            echo $type."\r\n".$msg."\r\n".$file ."\r\n\r\n".$detail;
        }
    }
}

/*
	bPack Application Exception
*/
class bPack_Application_Exception extends Exception {}

/*
	bPack Expection, and should only used in bPack Modules
*/
class bPack_Exception extends Exception {}

/*
	Generic exception for error
*/
class bPack_ErrorException extends ErrorException {}

/**
 * bPack NullArgument Exception
 */
class bPack_NullArgumentException extends bPack_Exception {}
