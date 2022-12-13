<?php

namespace App\Controllers\Crm\Avito;
use Classes\Avito\Token;

class TokenController extends \Core\Controllers\Controller 
{
    public function __construct()
    {
        
    }

    public function test()
    {
        $Token = new Token;
        $Token->fetchAccessToken();
    }
}