<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Onelegstudios\Shape\FeedbackChannel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rescues a toast or confirm from a `wire:navigate` redirect.
 *
 * `Shape::toast()->send()` called from a Livewire action dispatches through
 * Livewire's own browser event — `FeedbackChannel`'s transport for "during a
 * Livewire request." That is correct for an action that renders in place.
 * It is wrong for an action that also calls
 * `$this->redirectRoute(..., navigate: true)`: Livewire's frontend processes
 * the redirect effect before the dispatch effect, so the browser starts
 * swapping in the new page before anything can hear the event. It fires on
 * a page that is already being torn down.
 *
 * `FeedbackChannel::send()` cannot see that coming — the redirect is
 * typically called *after* the toast in the same action, and both still
 * land in the same response regardless of which came first in the PHP. This
 * is the one place that sees the whole response: after Livewire has built
 * it, before it reaches the browser. When it carries
 * `redirectUsingNavigate`, any Shape event riding along in `dispatches` is
 * moved to the session instead, where the next page's toaster picks it up
 * exactly as it would after a plain (non-Livewire) redirect.
 *
 * Duck-typed against Livewire's response shape rather than its classes, for
 * the same reason `FeedbackChannel` reaches Livewire through the container
 * binding: nothing here runs in an application that has never installed
 * Livewire, because `ShapeServiceProvider` only registers this middleware
 * when `app()->bound('livewire')`.
 */
class RescueFeedbackFromNavigate
{
    public function __construct(private readonly FeedbackChannel $channel) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasHeader('X-Livewire')) {
            return $next($request);
        }

        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }

        $payload = $response->getData(true);

        if (! is_array($payload) || ! is_array($payload['components'] ?? null)) {
            return $response;
        }

        $payload['components'] = array_map(
            fn (mixed $component): mixed => $this->rescueComponent($component),
            $payload['components'],
        );

        return $response->setData($payload);
    }

    /**
     * Move this component's Shape dispatches to the session, if it is
     * about to redirect with `navigate: true`. Anything that is not a
     * Shape event — the application's own dispatches — is left exactly
     * where Livewire put it.
     */
    private function rescueComponent(mixed $component): mixed
    {
        if (! is_array($component) || ! is_array($component['effects'] ?? null)) {
            return $component;
        }

        if (($component['effects']['redirectUsingNavigate'] ?? false) !== true) {
            return $component;
        }

        $dispatches = $component['effects']['dispatches'] ?? null;

        if (! is_array($dispatches) || $dispatches === []) {
            return $component;
        }

        $component['effects']['dispatches'] = array_values(array_filter(
            $dispatches,
            function (mixed $dispatch): bool {
                $name = is_array($dispatch) ? ($dispatch['name'] ?? null) : null;

                if (! in_array($name, [FeedbackChannel::EVENT_TOAST, FeedbackChannel::EVENT_CONFIRM], true)) {
                    return true;
                }

                $params = is_array($dispatch['params'] ?? null) ? $dispatch['params'] : [];

                $this->channel->flash($name, $params);

                return false;
            },
        ));

        return $component;
    }
}
