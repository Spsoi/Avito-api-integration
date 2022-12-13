<?php

namespace Classes\Avito;
use App\Services\Avito\QueryRequest;

class Token 
{
    private $config = [
        'client_id' => 'client_id',
        'client_secret' => 'client_secret',
        'grant_type' => 'client_credentials',
    ];

    /**
     * 
     * @return string
    */
    public function checkAccessToken() : string
    {
        $array = $this->getAccessToken();
        if ($array['token_expire'] >= time()) {
            return $array['access_token'];
        }

        return $this->fetchAccessToken();
    }

    /**
     * 
     * @return array
    */
    public function getConfig() : array
    {
       return $this->config;
    }

    /**
     * @return string
    */
    public function fetchAccessToken() : string
    {
        $QueryRequest = new QueryRequest;
        $array = $this->getAccessToken();
        $accessToken = $QueryRequest->queryAssembly('https://api.avito.ru/token', 'POST', $this->config);
        $tokenSetttings = json_decode($accessToken, true);
        $tokenSetttings['created_at'] = time();
        $tokenSetttings['token_expire'] = $tokenSetttings['created_at'] + $tokenSetttings['expires_in'];
        $this->putAccessToken($tokenSetttings);
        return $tokenSetttings['access_token'];
    }

}