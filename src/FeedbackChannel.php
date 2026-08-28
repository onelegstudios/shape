<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Session;

/**
 * The one place a server-side event decides how it reaches the browser.
 *
 * Everything else in this library renders and then stops caring. Feedback is
 * different: a toast is a thing that has already happened, announced to a page
 * that has already been drawn. There are exactly two ways to get there, and this
 * class is the only code that knows which one is in play.
 *
 * Both produce the *same browser event*, which is the point. A flashed toast and
 * a dispatched one arrive at `shape.js` indistinguishable from each other, so
 * there is one code path building toasts rather than two that can disagree —
 * which is the lesson the overlays learned expensively.
 *
 * Livewire is reached through the container binding rather than the class, for
 * the same reason `ShapeServiceProvider::registerBlaze()` checks `bound('blaze')`:
 * the binding is the thing this method goes on to use, and naming the class would
 * put a dependency in the package that the package does not have.
 */
class FeedbackChannel
{
    /**
     * Where feedback waits when it has to survive a redirect.
     */
    public const string SESSION_KEY = 'shape.feedback';

    public function __construct(private readonly Application $app) {}

    /**
     * Send one event to the browser, by whichever route is available.
     *
     * @param  array<string, mixed>  $payload
     */
    public function send(string $event, array $payload): void
    {
        $livewire = $this->livewire();

        if ($livewire !== null && method_exists($livewire, 'dispatch')) {
            // Livewire's own dispatcher puts this on the wire as a browser
            // event. Named arguments, because that is how a Livewire event
            // carries a payload, and how it arrives as `event.detail.toast`.
            $livewire->dispatch($event, ...$payload);

            return;
        }

        // Otherwise it has to outlive a redirect. `<x-shape::toaster>` renders
        // whatever is here as JSON on the next page, and shape.js replays each
        // entry as the same browser event Livewire would have dispatched.
        //
        // Read-append-flash rather than push: two toasts sent in one request
        // both have to arrive, and flashing twice would keep only the second.
        Session::flash(self::SESSION_KEY, [
            ...$this->flashed(),
            ['event' => $event, 'payload' => $payload],
        ]);
    }

    /**
     * The feedback waiting in the session, ready to render.
     *
     * @return list<array{event: string, payload: array<string, mixed>}>
     */
    public function flashed(): array
    {
        $flashed = Session::get(self::SESSION_KEY, []);

        return is_array($flashed) ? array_values($flashed) : [];
    }

    /**
     * Livewire's manager, but only during a Livewire request.
     *
     * Outside one — a full page load in an application that happens to have
     * Livewire installed — dispatching would queue an event onto a response
     * nobody is listening to, so the session route is the correct one there.
     */
    private function livewire(): ?object
    {
        if (! $this->app->bound('livewire')) {
            return null;
        }

        $livewire = $this->app->make('livewire');

        if (! is_object($livewire) || ! method_exists($livewire, 'isLivewireRequest')) {
            return null;
        }

        return $livewire->isLivewireRequest() === true ? $livewire : null;
    }
}
