<?php

namespace App;

class Response
{
    private $statusCode;
    private $response;
    private $headers;
    private $cookies;

    /**
     * Response constructor sets the response properties.
     *
     * @param $response
     * @param $headers
     * @param $statusCode
     */
    public function __construct($response, $headers, $statusCode)
    {
        $this->response = $response;
        $this->headers = $headers;
        $this->statusCode = $statusCode;
        if (array_key_exists('set-cookie', $headers)) {
            $this->cookies = $headers['set-cookie'];
        }
    }

    /**
     * @return array|mixed
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
