<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

/**
 * The fold-safety rules, in one place because two callers need them.
 *
 * A folded component is pre-rendered while Blade compiles and the result is
 * baked into the parent template, so anything request-scoped inside it is
 * resolved once and then served to everybody: the first visitor's session, the
 * first visitor's locale, a CSRF token minted at deploy time.
 *
 * The list below is Blaze's own global-state checklist, plus the translation
 * helpers, which are not on it and belong there for exactly the same reason.
 *
 * `shape:doctor` runs this over a consumer's ejected components; the test suite
 * runs it over the components this package ships. Both read this file, so the
 * rule a consumer is held to is the rule the library holds itself to.
 */
final class FoldSafety
{
    /**
     * @var list<array{pattern: string, hint: string}>
     */
    private const array RULES = [
        ['pattern' => 'auth(', 'hint' => 'the authenticated user'],
        ['pattern' => 'Auth::', 'hint' => 'the authenticated user'],
        ['pattern' => '@auth', 'hint' => 'the authenticated user'],
        ['pattern' => '@guest', 'hint' => 'the authenticated user'],
        ['pattern' => 'session(', 'hint' => 'the session'],
        ['pattern' => 'Session::', 'hint' => 'the session'],
        ['pattern' => 'request(', 'hint' => 'the request'],
        ['pattern' => 'Request::', 'hint' => 'the request'],
        ['pattern' => '$errors', 'hint' => 'the error bag'],
        ['pattern' => '@error', 'hint' => 'the error bag'],
        ['pattern' => 'now(', 'hint' => 'the current time'],
        ['pattern' => 'today(', 'hint' => 'the current time'],
        ['pattern' => 'Carbon::', 'hint' => 'the current time'],
        ['pattern' => '@csrf', 'hint' => 'the CSRF token'],
        ['pattern' => 'csrf_token(', 'hint' => 'the CSRF token'],
        ['pattern' => 'csrf_field(', 'hint' => 'the CSRF token'],
        ['pattern' => 'config(', 'hint' => 'configuration'],
        ['pattern' => 'cache(', 'hint' => 'the cache'],
        ['pattern' => 'Cache::', 'hint' => 'the cache'],
        ['pattern' => '__(', 'hint' => 'a translation'],
        ['pattern' => 'trans(', 'hint' => 'a translation'],
        ['pattern' => 'trans_choice(', 'hint' => 'a translation'],
        ['pattern' => '@lang', 'hint' => 'a translation'],
        ['pattern' => 'Lang::', 'hint' => 'a translation'],
        ['pattern' => 'DB::', 'hint' => 'the database'],
        ['pattern' => '::where(', 'hint' => 'the database'],
        ['pattern' => '::find(', 'hint' => 'the database'],
        ['pattern' => '::first(', 'hint' => 'the database'],
        ['pattern' => '::all(', 'hint' => 'the database'],
        ['pattern' => '::count(', 'hint' => 'the database'],
        ['pattern' => 'app(', 'hint' => 'the container'],
        ['pattern' => 'resolve(', 'hint' => 'the container'],
    ];

    /**
     * The patterns a folded component may not contain.
     *
     * @return list<string>
     */
    public static function patterns(): array
    {
        return array_column(self::RULES, 'pattern');
    }

    /**
     * Every fold hazard in one component's source.
     *
     * @return list<array{pattern: string, hint: string, line: int}>
     */
    public function inspect(string $source): array
    {
        if (! $this->folds($source)) {
            return [];
        }

        $searchable = $this->searchable($source);

        $offences = [];

        foreach (self::RULES as $rule) {
            $offset = 0;

            while (($position = strpos($searchable, $rule['pattern'], $offset)) !== false) {
                $offences[] = [
                    'pattern' => $rule['pattern'],
                    'hint' => $rule['hint'],
                    'line' => substr_count($searchable, "\n", 0, $position) + 1,
                ];

                $offset = $position + 1;
            }
        }

        usort($offences, fn (array $a, array $b): int => $a['line'] <=> $b['line']);

        return $offences;
    }

    /**
     * Whether the component opens by stating the strategy it is safe to use.
     */
    public function declaresStrategy(string $source): bool
    {
        return str_starts_with(ltrim($source), '@blaze');
    }

    /**
     * Whether the component is annotated to fold, which is what makes the rules apply.
     *
     * A component that only memoizes is cached per prop set at run time, so it
     * sees the request it was rendered in and none of the above applies to it.
     */
    public function folds(string $source): bool
    {
        return str_contains($this->searchable($source), 'fold: true');
    }

    /**
     * The part of a component that actually runs, with line numbers intact.
     *
     * Two things are removed. Anything inside `@unblaze` is excluded from the
     * fold by definition — that block is the sanctioned hole, and the error
     * message is why it exists. And Blade comments never execute: every
     * component here documents the reasoning behind its own annotation, and
     * several have to name the thing they avoid in order to explain why they
     * avoid it.
     *
     * Both are replaced with the newlines they contained rather than removed, so
     * a line number reported below is the line number in the file.
     */
    private function searchable(string $source): string
    {
        $blank = static fn (array $matches): string => str_repeat("\n", substr_count($matches[0], "\n"));

        $source = preg_replace_callback('/@unblaze\b.*?@endunblaze/s', $blank, $source) ?? $source;

        return preg_replace_callback('/\{\{--.*?--\}\}/s', $blank, $source) ?? $source;
    }
}
