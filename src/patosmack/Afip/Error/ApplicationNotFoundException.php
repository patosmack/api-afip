<?php

namespace patosmack\Afip\Error;

class ApplicationNotFoundException extends CustomException
{
    public function __construct($title, $message, $code = 520, \Exception $previous = null)
    {
        parent::__construct($title, $message, $code, $previous);
    }
}
