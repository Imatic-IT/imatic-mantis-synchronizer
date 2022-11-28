<?php

namespace Imatic\Mantis\Synchronizer;

use Http\Client\Exception;


class ImaticMantisCurl
{

    // class variable that will hold the curl request handler
    private $handler = null;

    private $handlerToken = '';

    // class variable that will hold the url
    private $url = '';

    // class variable that will hold the info of our request
    private $info = [];

    // class variable that will hold the data inputs of our request
    private $data = [];

    // class variable that will tell us what type of request method to use (defaults to get)
    private $method = 'get';

    // class variable that will hold the response of the request in string
    public $content = '';

    // class variable that will hold the response of the request in string
    public $error = [];

    // function to set data inputs to send
    public function url($url = '')
    {
        $this->url = $url;
        return $this;
    }

    // function to set data inputs to send
    public function handlerToken($handlerToken = '')
    {
        $this->handlerToken = $handlerToken;
        return $this;
    }

    // function to set data inputs to send
    public function handler($handler = '')
    {
        $this->handler = $handler;
        return $this;
    }

    // function to set data inputs to send
    public function data($data = [])
    {
        $this->data = $data;
        return $this;
    }

    // function to set request method (defaults to get)
    public function method($method = 'get')
    {
        $this->method = $method;
        return $this;
    }

    public function send()
    {

        try {
            if ($this->handler == null) {
                $this->handler = curl_init();
            }
            curl_setopt_array($this->handler, [
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $this->data,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . $this->handlerToken,
                    'Content-Type: application/json'
                ),
            ]);

            $this->content = curl_exec($this->handler);
            $this->info = curl_getinfo($this->handler);

        } catch (Exception $e) {
            die($e->getMessage());
        }

        if (curl_errno($this->handler)) {
            $this->error['curl_error'] = 'Request Error' . curl_error($this->handler);
        }

        $this->error();

    }

    private function error()
    {
        if ($this->info['http_code'] >= 400) {
            $this->error['http_code'] = 'Request Error with  status code ' . $this->info['http_code'];
        }
    }


    // function that will close the connection of the curl handler
    public function close()
    {
        curl_close($this->handler);
        $this->handler = null;
    }
}