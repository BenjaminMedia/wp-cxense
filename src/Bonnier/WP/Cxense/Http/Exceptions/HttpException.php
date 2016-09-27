<?php

namespace Bonnier\WP\Cxense\Http\Exceptions;

use Exception;

class HttpException extends Exception
{
    private $originalRequest;

    public function __construct($message, $code = 500, $request = null)
    {
        $this->originalRequest = $request;
        parent::__construct($message, $code, null);
    }

    public function getRequest()
    {
        return $this->originalRequest;
    }
}