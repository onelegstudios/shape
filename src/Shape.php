<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

class Shape
{
    public function __construct(private readonly FeedbackChannel $channel) {}

    /**
     * Begin building a class string for a component view.
     *
     * @param  string|array<array-key, mixed>|null  $classes
     */
    public function classes(string|array|null $classes = null): ClassBuilder
    {
        return new ClassBuilder($classes);
    }

    /**
     * Begin building a toast.
     *
     * Nothing is sent until `send()` — see PendingToast for why that is a
     * builder rather than a single call with five optional arguments.
     */
    public function toast(?string $heading = null): PendingToast
    {
        return new PendingToast($this->channel, $heading);
    }

    /**
     * Begin building a confirmation.
     */
    public function confirm(?string $message = null): PendingConfirm
    {
        return new PendingConfirm($this->channel, $message);
    }

    /**
     * The feedback waiting in the session for the next page to render.
     *
     * `<x-shape::toaster>` is the only caller. It is on the facade rather than
     * reached for with `session()` in the template so that the session key stays
     * one class's business.
     *
     * @return list<array{event: string, payload: array<string, mixed>}>
     */
    public function flashedFeedback(): array
    {
        return $this->channel->flashed();
    }
}
