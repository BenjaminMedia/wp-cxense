<?php

namespace Bonnier\WP\Cxense\Http;

use Exception;
use Bonnier\WP\Cxense\Exceptions\HttpException;

class HttpResponse
{
    private $originalRequest = null;

    public function __construct($request)
    {
        if (!$request) {
            throw new Exception('Missing required param: request');
        }
        $this->originalRequest = $request;

        if (is_wp_error($request)) {
            throw new Exception($request->get_error_message());
        }

        if ($this->getStatusCode() >= 400) {
            throw new HttpException($this->getMessage(), $this->getStatusCode(), $this->originalRequest);
        }
    }

    public function getBody()
    {
        if (isset($this->originalRequest['body'])) {
            return $this->originalRequest['body'];
        }
        return false;
    }

    public function getStatusCode()
    {
        if (isset($this->originalRequest['response']['code'])) {
            return $this->originalRequest['response']['code'];
        }
        return false;
    }

    public function getMessage()
    {
        if (isset($this->originalRequest['response']['message'])) {
            return $this->originalRequest['response']['message'];
        }
        return false;
    }

}