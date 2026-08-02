<?php

namespace DanHarrin\LivewireRateLimiting\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TooManyRequestsException extends HttpException
{
    public $minutesUntilAvailable;

    public function __construct(
        public $component,
        public $method,
        public $ip,
        public $secondsUntilAvailable,
    ) {
        $this->minutesUntilAvailable = ceil($secondsUntilAvailable / 60);

        parent::__construct(
            429,
            sprintf(
                'Too many requests from [%s] to method [%s] on component: [%s]. Retry in %d seconds.',
                $ip,
                $method,
                $component,
                $secondsUntilAvailable,
            )
        );
    }
}