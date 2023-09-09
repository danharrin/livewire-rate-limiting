<?php

namespace DanHarrin\LivewireRateLimiting;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Illuminate\Support\Facades\RateLimiter;

trait WithRateLimiting
{
    protected function clearRateLimiter($method = null, $class = null)
    {
        if (! $method) $method = debug_backtrace()[1]['function'];

        if (! $class) $class = static::class;

        $key = $this->getRateLimitKey($method, $class);

        RateLimiter::clear($key);
    }

    protected function getRateLimitKey($method, $class)
    {
        if (! $method) $method = debug_backtrace()[1]['function'];

        if (! $class) $class = static::class;

        return sha1($class.'|'.$method.'|'.request()->ip());
    }

    protected function hitRateLimiter($method = null, $decaySeconds = 60, $class = null)
    {
        if (! $method) $method = debug_backtrace()[1]['function'];

        if (! $class) $class = static::class;

        $key = $this->getRateLimitKey($method, $class);

        RateLimiter::hit($key, $decaySeconds);
    }

    protected function rateLimit($maxAttempts, $decaySeconds = 60, $method = null, $class = null)
    {
        if (! $method) $method = debug_backtrace()[1]['function'];

        if (! $class) $class = static::class;

        $key = $this->getRateLimitKey($method, $class);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $component = static::class;
            $ip = request()->ip();
            $secondsUntilAvailable = RateLimiter::availableIn($key);

            throw new TooManyRequestsException($component, $method, $ip, $secondsUntilAvailable, $class);
        }

        $this->hitRateLimiter($method, $decaySeconds, $class);
    }
}
