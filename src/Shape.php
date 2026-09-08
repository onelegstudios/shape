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
     * The Gravatar URL for an email address, or null when there is no address.
     *
     * A Gravatar is a URL and nothing else, which is the whole reason this is a
     * method rather than a component. `<x-shape::avatar :src="...">` already
     * draws a person at four sizes, in a group, under a badge, as a control; a
     * second component would have restated every one of those decisions in
     * order to change where the bytes come from, and put a vendor's name in
     * this package's public API to do it.
     *
     * A missing address returns null rather than a URL for nobody, so it drops
     * through the avatar's ladder to `icon` and then `initials` exactly as any
     * other absent `src` does. Which makes the mixed row — some people with an
     * address, some without — one call site rather than a branch.
     *
     * `$size` is the avatar's own word, and the pixels are resolved here so that
     * no call site has to keep the scale in its head. Gravatar serves a square
     * and almost nobody is looking at a 1x display, so each word asks for twice
     * its circle: 48, 64, 80 and 96. An `int` is taken as pixels instead, for
     * the display that wants three times and for the call site that is not an
     * avatar at all.
     *
     * A word this does not know resolves to `base`, which is the answer the
     * component gives it too — an avatar told `xl` draws the base circle, and a
     * URL that disagreed would be the one place the two readings of the same
     * word came apart. The scale is written twice, here and in the component,
     * which is the price of the component being a Blade file.
     *
     * `$default` is what Gravatar sends for an address that has no avatar, and
     * it is the argument worth reading twice, because it is a second fallback
     * ladder arriving beside the one the avatar already has. `mp` and its
     * siblings always answer with a picture, so an avatar's `icon` and
     * `initials` never render for anybody with an email address — Gravatar's
     * ladder wins outright. `blank` answers with a transparent GIF instead and
     * leaves the circle showing its own paint. `404` is the one to avoid: it is
     * a broken image, which is the failure `src` is documented not to have.
     *
     * SHA-256 and not MD5. Gravatar serves both, MD5 is the older spelling of
     * the same hash of the same string, and nothing here is old enough to owe
     * it anything.
     */
    public function gravatar(?string $email, int|string $size = 'base', ?string $default = 'mp', ?string $rating = null): ?string
    {
        // Trimmed and lowercased before hashing, because Gravatar hashes the
        // address that way and a hash of anything else is a hash of a different
        // person. `strtolower` rather than its multibyte spelling for the same
        // reason: it is the one Gravatar's own implementation runs.
        $email = strtolower(trim((string) $email));

        if ($email === '') {
            return null;
        }

        $pixels = is_int($size) ? $size : match ($size) {
            'xs' => 48,
            'sm' => 64,
            'lg' => 96,
            default => 80,
        };

        $query = array_filter(
            ['s' => $pixels, 'd' => $default, 'r' => $rating],
            static fn (int|string|null $value): bool => $value !== null,
        );

        return 'https://gravatar.com/avatar/'.hash('sha256', $email).'?'.http_build_query($query);
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
