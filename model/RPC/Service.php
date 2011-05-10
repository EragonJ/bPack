<?php

class bPack_RPC_Service
{
    final public function __call($func, $agruments = null)
    {
        throw new Exception('Method not found.');
    }

    final public static function response($status_code = 200, $data = null)
    {
        $return_data = array('status_code'=> $status_code);

        if(!is_null($data))
        {
            $return_data = array_merge($return_data, $data);
        }

        echo json_encode($return_data);

        exit;
    }

    public function checkToken()
    {
        $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

        if(is_null($token))
        {
            $this->response(401,array('error_msg'=>'no token'));
        }
        
        try
        {
            $token_info = include_once bPack_Application_Directory . 'tokens/'.$token.'.tkn';

            if($token_info['expired'] > time())
            {
                $this->token = $token_info['token'];
                return true;
            }
            else
            {
                $this->response(401, array('error_msg'=>'token expired'));
            }
        }
        catch(Exception $e)
        {
            $this->response(401, array('error_msg'=>'not logged'));
        }
    }
}


