<?php

use Livewire\Volt\Component;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

new class extends Component {
    use \DanHarrin\LivewireRateLimiting\WithRateLimiting;

    public $secondsUntilAvailable;

    public function clear()
    {
        $this->clearRateLimiter('limit', component: 'VoltComponent');
    }

    public function hit()
    {
        $this->hitRateLimiter('limit', 1, component: 'VoltComponent');
    }

    public function limit()
    {
        try {
            $this->rateLimit(3, 1, component: 'VoltComponent');
        } catch (TooManyRequestsException $exception) {
            return $this->secondsUntilAvailable = $exception->secondsUntilAvailable;
        }

        $this->secondsUntilAvailable = 0;
    }

}; ?>

<div>
    //
</div>
