<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

/**
 * A confirmation, being asked from the server.
 *
 * `then()` is the whole of the integration, and it names a *browser event*
 * rather than a method. When the dialog is accepted, shape.js dispatches an
 * event by that name on `window` and does nothing else — so the script stays
 * free of Livewire, and a Livewire component receives it with the listener it
 * already has:
 *
 *     Shape::confirm('Delete project?')->then('deleteProject')->send();
 *
 *     #[On('deleteProject')]
 *     public function deleteProject(): void { … }
 *
 * `#[On]` listens for window events, so nothing extra is wired up. Anything else
 * that can hear a window event — Alpine, a plain listener — works identically.
 */
final class PendingConfirm
{
    private ?string $heading = null;

    private ?string $accept = null;

    private ?string $cancel = null;

    private ?string $tone = null;

    private ?string $then = null;

    /**
     * @var array<array-key, mixed>
     */
    private array $params = [];

    /**
     * Which dialog to fill. The default matches `<x-shape::confirm />`'s own,
     * so one in the layout answers every call.
     */
    private string $name = 'shape-confirm';

    public function __construct(
        private readonly FeedbackChannel $channel,
        private ?string $message = null,
    ) {}

    public function message(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function heading(?string $heading): self
    {
        $this->heading = $heading;

        return $this;
    }

    public function accept(?string $label): self
    {
        $this->accept = $label;

        return $this;
    }

    public function cancel(?string $label): self
    {
        $this->cancel = $label;

        return $this;
    }

    /**
     * The tone of the accept button — `danger` for the destructive case.
     */
    public function tone(?string $tone): self
    {
        $this->tone = $tone;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * The window event dispatched when someone accepts.
     *
     * @param  array<array-key, mixed>  $params
     */
    public function then(string $event, array $params = []): self
    {
        $this->then = $event;
        $this->params = $params;

        return $this;
    }

    public function send(): void
    {
        $this->channel->send(FeedbackChannel::EVENT_CONFIRM, ['confirm' => $this->toArray()]);
    }

    /**
     * @return array{name: string, heading: string|null, message: string|null, accept: string|null, cancel: string|null, tone: string|null, then: string|null, params: array<array-key, mixed>}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'heading' => $this->heading,
            'message' => $this->message,
            'accept' => $this->accept,
            'cancel' => $this->cancel,
            'tone' => $this->tone,
            'then' => $this->then,
            'params' => $this->params,
        ];
    }
}
