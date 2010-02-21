<?php
class bPack_ErrorHandler
{
    final public static function setup()
    {
        set_exception_handler(array( 'bPack_ErrorHandler', 'exception_handler'));
        set_error_handler(array('bPack_ErrorHandler', 'error_handler'), E_ALL);
    }

    final public static function error_handler($errno, $errstr, $errfile, $errline)
    {
            throw new bPack_ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    final public static function exception_handler($e)
    {
        self::_log($e->getMessage(),$e->getFile(),'EXCEPTION',$e->getTraceAsString());
        self::_display($e->getMessage(),$e->getFile(),'EXCEPTION',$e->getTraceAsString(),$e->getCode());
        exit;
    }

    protected static function _log($msg,$file,$type ,$detail = '')
    {
        return true;
    }

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

/**
 * Standard bPack Exceptions
 */

class bPack_Exception extends Exception {}
class bPack_ErrorException extends ErrorException {}
class bPack_NullArgumentException extends bPack_Exception {}