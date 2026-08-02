<?php

namespace DanHarrin\LivewireRateLimiting\Tests;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Livewire\Attributes\Json;
use Livewire\Livewire;
use Livewire\Volt\Volt;

class RateLimitingTest extends TestCase
{
    public function test_rate_limit_throws_exception_with_status_429()
    {
        $component = Livewire::test(Component::class);

        $component
            ->call('fetchJson')
            ->assertOk()
            ->call('fetchJson')
            ->assertOk();

        $returnsMeta = $component->effects['returnsMeta'] ?? [];

        $this->assertArrayHasKey(0, $returnsMeta);
        $this->assertArrayHasKey('status', $returnsMeta[0]);
        $this->assertSame(429, $returnsMeta[0]['status']);
    }

    public function test_can_rate_limit()
    {
        $component = Livewire::test(Component::class);

        $component
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertNotSet('secondsUntilAvailable', 0);

        sleep(1);

        $component
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0);
    }

    public function test_can_hit_and_clear_rate_limiter()
    {
        Livewire::test(Component::class)
            ->call('hit')
            ->call('hit')
            ->call('hit')
            ->call('limit')
            ->assertNotSet('secondsUntilAvailable', 0)
            ->call('clear')
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0);
    }

    public function test_can_rate_limit_volt()
    {
        $this->mountVolt();
        $component = Volt::test('volt-component');

        $component
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertNotSet('secondsUntilAvailable', 0);

        sleep(1);

        $component
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0);
    }

    public function test_can_hit_and_clear_rate_limiter_volt()
    {
        $this->mountVolt();
        Volt::test('volt-component')
            ->call('hit')
            ->call('hit')
            ->call('hit')
            ->call('limit')
            ->assertNotSet('secondsUntilAvailable', 0)
            ->call('clear')
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0);
    }

    protected function mountVolt()
    {
        Volt::mount([
            __DIR__ . '/views',
        ]);
    }
}

class Component extends \Livewire\Component
{
    use \DanHarrin\LivewireRateLimiting\WithRateLimiting;

    public $secondsUntilAvailable;

    public function clear()
    {
        $this->clearRateLimiter('limit');
    }

    public function hit()
    {
        $this->hitRateLimiter('limit', 1);
    }

    public function limit()
    {
        try {
            $this->rateLimit(3, 1);
        } catch (TooManyRequestsException $exception) {
            return $this->secondsUntilAvailable = $exception->secondsUntilAvailable;
        }

        $this->secondsUntilAvailable = 0;
    }

    #[Json]
    public function fetchJson()
    {
        $this->rateLimit(1);
    }

    public function render()
    {
        return view('component');
    }
}
