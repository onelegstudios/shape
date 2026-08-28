<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Onelegstudios\Shape\FeedbackChannel;
use Onelegstudios\Shape\Http\Middleware\RescueFeedbackFromNavigate;

/**
 * Shapes a fake Livewire "update" response the same way the real one comes
 * back — one component payload with `effects.dispatches`, and, only when
 * the action redirected with `navigate: true`, `effects.redirectUsingNavigate`.
 *
 * @param  list<array{name: string, params: array<string, mixed>}>  $dispatches
 */
function livewireUpdateResponse(array $dispatches, bool $navigating = false): JsonResponse
{
    return new JsonResponse([
        'components' => [
            [
                'snapshot' => '{}',
                'effects' => array_filter([
                    'dispatches' => $dispatches,
                    'redirectUsingNavigate' => $navigating ?: null,
                    'redirect' => $navigating ? '/somewhere' : null,
                ], fn (mixed $value): bool => $value !== null),
            ],
        ],
    ]);
}

function livewireRequest(): Request
{
    return Request::create('/livewire/update', 'POST', server: ['HTTP_X_LIVEWIRE' => 'true']);
}

it('moves a toast into the session when the action redirects with navigate', function () {
    $response = app(RescueFeedbackFromNavigate::class)->handle(
        livewireRequest(),
        fn () => livewireUpdateResponse([
            ['name' => 'shape:toast', 'params' => ['toast' => ['heading' => 'Task created']]],
        ], navigating: true),
    );

    $effects = $response->getData(true)['components'][0]['effects'];

    expect($effects['dispatches'])->toBe([])
        ->and(Session::get(FeedbackChannel::SESSION_KEY))->toBe([
            ['event' => 'shape:toast', 'payload' => ['toast' => ['heading' => 'Task created']]],
        ]);
});

it('leaves dispatches on the wire when the action does not redirect', function () {
    $response = app(RescueFeedbackFromNavigate::class)->handle(
        livewireRequest(),
        fn () => livewireUpdateResponse([
            ['name' => 'shape:toast', 'params' => ['toast' => ['heading' => 'Task created']]],
        ]),
    );

    $effects = $response->getData(true)['components'][0]['effects'];

    expect($effects['dispatches'])->toHaveCount(1)
        ->and(Session::get(FeedbackChannel::SESSION_KEY))->toBeNull();
});

it("only rescues Shape's own events, leaving the application's dispatches on the wire", function () {
    $response = app(RescueFeedbackFromNavigate::class)->handle(
        livewireRequest(),
        fn () => livewireUpdateResponse([
            ['name' => 'shape:toast', 'params' => ['toast' => ['heading' => 'Task created']]],
            ['name' => 'task-created', 'params' => ['id' => 1]],
        ], navigating: true),
    );

    $effects = $response->getData(true)['components'][0]['effects'];

    expect($effects['dispatches'])->toBe([
        ['name' => 'task-created', 'params' => ['id' => 1]],
    ]);
});

it('rescues a confirm the same way as a toast', function () {
    $response = app(RescueFeedbackFromNavigate::class)->handle(
        livewireRequest(),
        fn () => livewireUpdateResponse([
            ['name' => 'shape:confirm', 'params' => ['confirm' => ['message' => 'Delete project?']]],
        ], navigating: true),
    );

    expect(Session::get(FeedbackChannel::SESSION_KEY))->toBe([
        ['event' => 'shape:confirm', 'payload' => ['confirm' => ['message' => 'Delete project?']]],
    ]);
});

it('passes a non-Livewire request through untouched', function () {
    $response = app(RescueFeedbackFromNavigate::class)->handle(
        Request::create('/some/page'),
        fn () => response('<html></html>'),
    );

    expect($response->getContent())->toBe('<html></html>');
});

it('passes a Livewire response with nothing to rescue through untouched', function () {
    $response = app(RescueFeedbackFromNavigate::class)->handle(
        livewireRequest(),
        fn () => livewireUpdateResponse([], navigating: true),
    );

    expect(Session::get(FeedbackChannel::SESSION_KEY))->toBeNull();
});
