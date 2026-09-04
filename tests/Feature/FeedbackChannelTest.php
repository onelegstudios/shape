<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Onelegstudios\Shape\Facades\Shape;
use Onelegstudios\Shape\FeedbackChannel;

/**
 * Stands in for the Livewire component the channel dispatches through.
 *
 * `dispatch()` lives on the component, not on `app('livewire')` itself — the
 * manager only exposes `current()`, the top of Livewire's component stack.
 * This mirrors that shape rather than putting `dispatch()` on the manager
 * fake, which is what let a real bug (dispatching through the manager, which
 * has no such method) pass a suite whose fake didn't match Livewire's own.
 */
function fakeLivewire(bool $duringLivewireRequest = true): object
{
    $component = new class
    {
        /** @var list<array{string, array<string, mixed>}> */
        public array $dispatched = [];

        public function dispatch(string $event, mixed ...$params): void
        {
            $this->dispatched[] = [$event, $params];
        }
    };

    $manager = new class($component)
    {
        public bool $duringLivewireRequest = true;

        public function __construct(private readonly object $component) {}

        public function isLivewireRequest(): bool
        {
            return $this->duringLivewireRequest;
        }

        public function current(): object
        {
            return $this->component;
        }
    };

    $manager->duringLivewireRequest = $duringLivewireRequest;

    app()->instance('livewire', $manager);

    return $component;
}

it('flashes to the session when there is no livewire to dispatch through', function () {
    Shape::toast()->success('Invoice sent')->send();

    expect(Session::get(FeedbackChannel::SESSION_KEY))->toBe([
        [
            'event' => 'shape:toast',
            'payload' => [
                'toast' => [
                    'heading' => 'Invoice sent',
                    'description' => null,
                    'tone' => 'success',
                    'duration' => 5000,
                ],
            ],
        ],
    ]);
});

it('dispatches through livewire during a livewire request', function () {
    $livewire = fakeLivewire();

    Shape::toast()->success('Invoice sent')->send();

    expect($livewire->dispatched)->toHaveCount(1)
        ->and($livewire->dispatched[0][0])->toBe('shape:toast')
        ->and($livewire->dispatched[0][1])->toHaveKey('toast')
        // Named, because that is how a Livewire event carries a payload — and it
        // is what makes the browser event's detail match the flashed one.
        ->and($livewire->dispatched[0][1]['toast']['heading'])->toBe('Invoice sent')
        ->and(Session::get(FeedbackChannel::SESSION_KEY))->toBeNull();
});

it('falls back to the session outside a livewire request, even with livewire installed', function () {
    // A full page load in an application that happens to have Livewire. There is
    // no response for a dispatched event to ride on, so the session is the only
    // route that arrives.
    $livewire = fakeLivewire(duringLivewireRequest: false);

    Shape::toast()->success('Invoice sent')->send();

    expect($livewire->dispatched)->toBe([])
        ->and(Session::get(FeedbackChannel::SESSION_KEY))->toHaveCount(1);
});

it('keeps every message sent in one request', function () {
    // Flashing twice would keep only the second, which is how the "only the last
    // toast shows up" bug happens.
    Shape::toast()->success('Invoice sent')->send();
    Shape::toast()->danger('Card declined')->send();

    expect(Session::get(FeedbackChannel::SESSION_KEY))->toHaveCount(2);
});

it('sends a confirmation on the same channel as a toast', function () {
    Shape::confirm('Delete project?')->then('deleteProject')->send();

    $flashed = Session::get(FeedbackChannel::SESSION_KEY);

    expect($flashed[0]['event'])->toBe('shape:confirm')
        ->and($flashed[0]['payload']['confirm']['then'])->toBe('deleteProject')
        ->and($flashed[0]['payload']['confirm']['message'])->toBe('Delete project?');
});

it('renders whatever is waiting into the toaster, for the script to replay', function () {
    Shape::toast()->success('Invoice sent')->send();

    expect(Blade::render('<x-shape::toaster />'))
        ->toContain('data-shape-feedback')
        ->toContain('shape:toast')
        ->toContain('Invoice sent');
});

it('renders no payload at all when nothing is waiting', function () {
    expect(Blade::render('<x-shape::toaster />'))
        ->not->toContain('data-shape-feedback')
        ->not->toContain('<script');
});

it('escapes a message that would otherwise close the script element', function () {
    // Without the JSON_HEX_TAG family, a toast containing `</script>` ends the
    // element early and the rest of the payload is parsed as markup.
    Shape::toast()->success('</script><img src=x>')->send();

    expect(Blade::render('<x-shape::toaster />'))
        ->not->toContain('</script><img')
        ->toContain('<');
});
