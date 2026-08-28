<?php

declare(strict_types=1);

use Onelegstudios\Shape\FeedbackChannel;
use Onelegstudios\Shape\PendingToast;

function pendingToast(?string $heading = null): PendingToast
{
    return new PendingToast(app(FeedbackChannel::class), $heading);
}

it('carries every part of a toast, and defaults the rest', function () {
    expect(pendingToast('Invoice sent')->toArray())->toBe([
        'heading' => 'Invoice sent',
        'description' => null,
        'color' => null,
        'duration' => 5000,
    ]);
});

it('sets the tone and the heading in one call', function () {
    expect(pendingToast()->success('Invoice sent')->toArray())
        ->toMatchArray(['heading' => 'Invoice sent', 'color' => 'success']);
});

it('leaves a heading alone when the tone is set on its own', function () {
    expect(pendingToast('Invoice sent')->danger()->toArray())
        ->toMatchArray(['heading' => 'Invoice sent', 'color' => 'danger']);
});

it('names a tone for each of the four states', function (string $tone) {
    expect(pendingToast()->{$tone}('Message')->toArray()['color'])->toBe($tone);
})->with(['accent', 'success', 'warning', 'danger']);

it('stays until dismissed when it is sticky', function () {
    expect(pendingToast('Uploading')->sticky()->toArray()['duration'])->toBe(0);
});

it('chains, so any one part can be set without naming the others', function () {
    expect(
        pendingToast()
            ->success('Invoice sent')
            ->description('To billing@example.com')
            ->duration(1200)
            ->toArray(),
    )->toBe([
        'heading' => 'Invoice sent',
        'description' => 'To billing@example.com',
        'color' => 'success',
        'duration' => 1200,
    ]);
});
