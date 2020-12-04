<?php

namespace patosmack\Afip\Error;

class ConfigurationErrorException extends CustomException
{
    public function __construct($title, $message, $code = 540, \Exception $previous = null)
    {
        parent::__construct($title, $message, $code, $previous);
    }
}
