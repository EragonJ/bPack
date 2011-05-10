<?php

class bPack_RPC_Client
{
    protected $service_url = '';
    protected $api_key = '';

    public function __construct($service_url, $server_key)
    {
        $this->service_url = $service_url;

        $connection_result = $this->exec('');

        if(!(($connection_result['status_code'] == 100) && ($connection_result['server_key'] == $server_key)))
        {
            throw new Exception('Unable to connect server, or serverkey dismatch');
        }
    }

    public function setAPIKey($key = '')
    {
        $this->api_key = $key;

        return true;
    }

    public function auth($data)
    {
        $result = $this->exec('Auth.retrieveToken', $data);

        if ($result['status_code'] == 200)
        {
            $this->token = $result['token'];
            $this->token_expired_time = $result['expired'];

            return true;
        }
        else
        {
            return false;
        }
    }

    public function exec($method_name, $data = null)
    {
        if(is_null($data))
        {
            $data = array();
        }

        if(isset($this->api_key))
        {
            $data = array_merge($data, array('apikey'=>$this->api_key));
        }

        if(isset($this->token))
        {
            $data = array_merge($data, array('token'=>$this->token));
        }


        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->service_url . '?method='. $method_name);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        if(!is_null($data))
        {
            $post = array();
            foreach($data as $k=>$v)
            {
                $post[] = "$k=".urlencode($v);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode("&",$post));
        }
        curl_setopt($ch, CURLOPT_POST, 1);

        $response = curl_exec($ch);

        $response_data = curl_getinfo($ch);

        curl_close($ch);

        $data = json_decode($response, true); 

        if(!is_array($data))
        {
            if(defined('bPack_Debug'))
            {
                echo $response;
            }
            throw new Exception('Return null at action ' . $method_name);
        }

        return $data;
    }
}
