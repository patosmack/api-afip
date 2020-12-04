<?php

namespace patosmack\Afip;

class TokenAutorization
{
    /**
     * Token de autorización y de autenticación del servicio web
     *
     * @var string
     **/
    private $token;

    /**
     * Servicio web de autorización y autenticación
     *
     * @var string
     **/
    private $sign;

    public function __construct($token, $sign)
    {
        $this->token 	= $token;
        $this->sign 	= $sign;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getSign()
    {
        return $this->sign;
    }
}
