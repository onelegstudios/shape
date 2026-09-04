<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

/**
 * A toast, being built.
 *
 * `Shape::toast()` returns one of these and nothing has happened yet — the
 * message only leaves the server on `send()`. That is deliberate: a toast has
 * five optional parts, and a builder is the shape that lets a caller set one of
 * them without naming the other four.
 *
 * There is no `render()` here and no markup anywhere in this class. What crosses
 * the wire is the payload below; the markup is `resources/views/shape/toast/toast.blade.php`,
 * cloned in the browser from a template the toaster already rendered. Keeping
 * the two apart is what stops a design change having to touch PHP.
 */
final class PendingToast
{
    private ?string $description = null;

    private ?string $color = null;

    /**
     * How long the toast stays, in milliseconds. Zero means until dismissed.
     */
    private int $duration = 5000;

    public function __construct(
        private readonly FeedbackChannel $channel,
        private ?string $heading = null,
    ) {}

    public function heading(?string $heading): self
    {
        $this->heading = $heading;

        return $this;
    }

    public function description(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * The tone: `accent`, `success`, `warning`, `danger`, or null for neutral.
     *
     * It selects which of the toaster's templates gets cloned, so it also
     * decides the glyph — and there is deliberately no way to send a different
     * one. The script clones markup; it cannot resolve an SVG it was not already
     * given, and five templates is where that stops being worth it. The rule it
     * enforces by accident is the right one: the glyph and the colour say the
     * same thing, and neither can be set without the other.
     */
    public function color(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function duration(int $milliseconds): self
    {
        $this->duration = $milliseconds;

        return $this;
    }

    /**
     * Stay until dismissed.
     */
    public function sticky(): self
    {
        return $this->duration(0);
    }

    public function accent(?string $heading = null): self
    {
        return $this->tone('accent', $heading);
    }

    public function success(?string $heading = null): self
    {
        return $this->tone('success', $heading);
    }

    public function warning(?string $heading = null): self
    {
        return $this->tone('warning', $heading);
    }

    /**
     * A failure. It is the one tone announced assertively, which shape.js
     * decides by putting it in the toaster's assertive live region.
     */
    public function danger(?string $heading = null): self
    {
        return $this->tone('danger', $heading);
    }

    /**
     * Send it, by whichever route the request has.
     */
    public function send(): void
    {
        $this->channel->send(FeedbackChannel::EVENT_TOAST, ['toast' => $this->toArray()]);
    }

    /**
     * @return array{heading: string|null, description: string|null, color: string|null, duration: int}
     */
    public function toArray(): array
    {
        return [
            'heading' => $this->heading,
            'description' => $this->description,
            'color' => $this->color,
            'duration' => $this->duration,
        ];
    }

    private function tone(string $color, ?string $heading): self
    {
        $this->color = $color;

        if ($heading !== null) {
            $this->heading = $heading;
        }

        return $this;
    }
}
