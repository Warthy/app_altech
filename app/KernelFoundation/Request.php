<?php

namespace App\KernelFoundation;

class Request
{
    const METHOD_POST = "POST";
    const METHOD_GET = "GET";
    const METHOD_PUT = "PUT";

    public $parameters;
    public $uri;
    public $method;
    public $cookies;
    public $session;
    public $hostname;

    public function __construct(
        $method, $uri, $hostname,
        ParameterBag $cookies,
        ParameterBag $session,
        array $parameters
    )
    {
        $this->method = $method;
        $this->cookies = $cookies;
        $this->session = $session;
        $this->hostname = $hostname;
        $this->uri = $uri;
        $this->parameters = $parameters;
    }

    static function createFromGlobals()
    {
        session_start();

        $method = $_SERVER['REQUEST_METHOD'];
        $parameters = [
            'query' => new ParameterBag($_GET),
            'request' => new ParameterBag($_POST)
        ];

        $cookies = new ParameterBag($_COOKIE);
        $session = new ParameterBag($_SESSION);

        $uri = rtrim($_SERVER["REQUEST_URI"], '/');
        if ($uri == "") {
            $uri = "/";
        }

        return new static($method, $uri, $_SERVER["SERVER_NAME"], $cookies, $session, $parameters);
    }

    public function is(string $method): bool
    {
        return strtoupper($this->method) == strtoupper($method);
    }

}