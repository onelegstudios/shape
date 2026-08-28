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

    /**
     * The two events this channel ever carries — named so
     * `RescueFeedbackFromNavigate` can pick them out of Livewire's own
     * dispatches without guessing at a prefix.
     */
    public const string EVENT_TOAST = 'shape:toast';

    public const string EVENT_CONFIRM = 'shape:confirm';

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
            //
            // This is the right transport for an action that renders in
            // place. It is the wrong one for an action that also redirects
            // with `navigate: true` — Livewire's frontend processes that
            // redirect before this dispatch, so the page is already being
            // swapped out from under the event. `RescueFeedbackFromNavigate`
            // catches that case after the fact and moves the event to
            // `flash()` below, because `send()` cannot see a `redirect()`
            // call that has not happened yet.
            $livewire->dispatch($event, ...$payload);

            return;
        }

        $this->flash($event, $payload);
    }

    /**
     * Queue an event to outlive a redirect.
     *
     * `<x-shape::toaster>` renders whatever is here as JSON on the next
     * page, and shape.js replays each entry as the same browser event
     * Livewire would have dispatched.
     *
     * Read-append-flash rather than push: two toasts sent in one request
     * both have to arrive, and flashing twice would keep only the second.
     *
     * @param  array<string, mixed>  $payload
     */
    public function flash(string $event, array $payload): void
    {
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
     * The mounted Livewire component handling the current request, if any.
     *
     * `dispatch()` lives on the component (via `HandlesEvents`), not on the
     * manager `app('livewire')` resolves to — the manager only exposes
     * `current()`, the top of the component stack `HandleComponents` pushes
     * onto around mount/update. Outside a Livewire request — a full page
     * load in an application that happens to have Livewire installed —
     * dispatching would queue an event onto a response nobody is listening
     * to, so the session route is the correct one there.
     */
    private function livewire(): ?object
    {
        if (! $this->app->bound('livewire')) {
            return null;
        }

        $manager = $this->app->make('livewire');

        if (! is_object($manager) || ! method_exists($manager, 'isLivewireRequest') || $manager->isLivewireRequest() !== true) {
            return null;
        }

        if (! method_exists($manager, 'current')) {
            return null;
        }

        $component = $manager->current();

        return is_object($component) ? $component : null;
    }
}
