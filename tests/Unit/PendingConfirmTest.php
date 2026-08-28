<?php

declare(strict_types=1);

use Onelegstudios\Shape\FeedbackChannel;
use Onelegstudios\Shape\PendingConfirm;

function pendingConfirm(?string $message = null): PendingConfirm
{
    return new PendingConfirm(app(FeedbackChannel::class), $message);
}

it('defaults to the dialog the layout already has', function () {
    // The default matches `<x-shape::confirm />`'s own, so one in the layout
    // answers every call without anyone naming it.
    expect(pendingConfirm('Delete project?')->toArray())->toBe([
        'name' => 'shape-confirm',
        'heading' => null,
        'message' => 'Delete project?',
        'accept' => null,
        'cancel' => null,
        'color' => null,
        'then' => null,
        'params' => [],
    ]);
});

it('names the window event to dispatch, and what to send with it', function () {
    expect(pendingConfirm('Delete project?')->then('deleteProject', [42])->toArray())
        ->toMatchArray(['then' => 'deleteProject', 'params' => [42]]);
});

it('leaves anything it was not told to change unset, so the component keeps its own default', function () {
    // A null here is "the markup already says this", not "render nothing".
    expect(pendingConfirm('Delete project?')->toArray())
        ->toMatchArray(['accept' => null, 'cancel' => null, 'heading' => null]);
});

it('chains the whole dialog', function () {
    expect(
        pendingConfirm()
            ->heading('Delete project?')
            ->message('Every invoice attached to it goes too.')
            ->accept('Delete')
            ->cancel('Keep it')
            ->color('danger')
            ->name('confirm-delete')
            ->then('deleteProject')
            ->toArray(),
    )->toBe([
        'name' => 'confirm-delete',
        'heading' => 'Delete project?',
        'message' => 'Every invoice attached to it goes too.',
        'accept' => 'Delete',
        'cancel' => 'Keep it',
        'color' => 'danger',
        'then' => 'deleteProject',
        'params' => [],
    ]);
});
