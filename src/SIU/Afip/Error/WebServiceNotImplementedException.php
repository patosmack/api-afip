<?php

namespace SIU\Afip\Error;

class WebServiceNotImplementedException extends CustomException
{
    public function __construct($title, $message, $code = 550, \Exception $previous = null)
    {
        parent::__construct($title, $message, $code, $previous);
    }
}
