<?php

namespace DanHarrin\LivewireRateLimiting;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Illuminate\Support\Facades\RateLimiter;

trait WithRateLimiting
{
    protected function clearRateLimiter($target = null, $component = null)
    {
        $target ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        $component ??= static::class;

        $key = $this->getRateLimitKey($target, $component);

        RateLimiter::clear($key);
    }

    protected function getRateLimitKey($target, $component = null)
    {
        $target ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        $component ??= static::class;

        return 'livewire-rate-limiter:'.sha1($component.'|'.$target.'|'.request()->ip());
    }

    protected function hitRateLimiter($target = null, $decaySeconds = 60, $component = null)
    {
        $target ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        $component ??= static::class;

        $key = $this->getRateLimitKey($target, $component);

        RateLimiter::hit($key, $decaySeconds);
    }

    protected function rateLimit($maxAttempts, $decaySeconds = 60, $target = null, $component = null)
    {
        $target ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        $component ??= static::class;

        $key = $this->getRateLimitKey($target, $component);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $ip = request()->ip();
            $secondsUntilAvailable = RateLimiter::availableIn($key);

            throw new TooManyRequestsException($component, $target, $ip, $secondsUntilAvailable);
        }

        $this->hitRateLimiter($target, $decaySeconds, $component);
    }
}
