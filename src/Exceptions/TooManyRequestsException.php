<?php

namespace DanHarrin\LivewireRateLimiting\Exceptions;

use Exception;

class TooManyRequestsException extends Exception
{
    public $component;

    public $ip;

    public $method;

    public $secondsUntilAvailable;

    public function __construct($component, $method, $ip, $secondsUntilAvailable)
    {
        $this->component = $component;
        $this->ip = $ip;
        $this->method = $method;
        $this->secondsUntilAvailable = $secondsUntilAvailable;

        parent::__construct("Too many requests from [$this->ip] to method [$this->method] on component: [$this->component]. Retry in $this->secondsUntilAvailable seconds.");
    }
}