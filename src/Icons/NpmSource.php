<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Icons;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * A set fetched from the npm registry rather than from the repository behind it.
 *
 * Nothing here runs npm. A registry is an HTTP endpoint that hands back a
 * gzipped tar, which is what this package already knows how to unpack — so
 * there is no `node_modules` to create, nothing to install and nothing to
 * uninstall afterwards.
 *
 * What it buys is that a package is a *published artefact* where a repository is
 * a working tree. Three things follow from that:
 *
 * - **It holds the drawings and not the project.** Material Symbols is the case
 *   that forced this: the repository is seven weights across three families plus
 *   the fonts built from them, which is gigabytes and cannot be unpacked in
 *   memory at all, while `@material-symbols/svg-400` is 1.8MB of exactly the
 *   SVGs. Tabler is the same shape less severely — 1.2MB against 32MB.
 * - **A version is immutable where a branch moves.** `0.47.0` is the same bytes
 *   next year; `main` is a promise about nothing. What gets pinned into a
 *   generated component is therefore a version, and re-reading it is exact.
 * - **The registry states an integrity hash**, which is checked here. A
 *   repository archive offers nothing to check against.
 *
 * The cost is that a registry serves packages and nothing smaller: there is no
 * per-file endpoint, so every run pulls the package. At these sizes that is
 * cheaper than the handful of raw requests it replaces, and it is why
 * `fetchOne()` is left saying no.
 */
final class NpmSource extends ArchiveSource
{
    /**
     * @param  string  $set  The set's name in `shape.icon_sets`, for anything this has to say out loud.
     * @param  string  $package  The package name, scoped or not: `@material-symbols/svg-400`.
     * @param  string  $version  An exact version, or a dist-tag such as `latest`.
     * @param  string  $path  The subdirectory of the package the set lives in, if any.
     * @param  string  $base  Where fetched sets are cached, usually under `storage/framework`.
     * @param  bool  $offline  Refuse to fetch, and answer from the cache or not at all.
     * @param  bool  $flatten  Whether the set's own subdirectories under `path` are collapsed into one on the way in.
     */
    public function __construct(
        Filesystem $files,
        string $set,
        private readonly string $package,
        string $version,
        string $path,
        string $base,
        bool $offline = false,
        bool $flatten = false,
    ) {
        // Always whole: a package is the smallest thing a registry serves, so
        // there is no run that wants less of one than all of it.
        parent::__construct($files, $set, $version, $path, $base, $offline, whole: true, flatten: $flatten);
    }

    /**
     * Resolve the version, pull the tarball, and check it against the hash the
     * registry states for it.
     */
    protected function download(string $archive): string
    {
        [$version, $url, $integrity] = $this->resolve();

        $response = Http::withHeaders(['User-Agent' => 'shape'])
            ->timeout(120)
            ->retry(3, 200, throw: false)
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("Could not fetch [{$this->set}] from [{$this->package}@{$version}]: the registry answered {$response->status()}.");
        }

        $body = $response->body();

        $this->verify($body, $version, $integrity);

        $this->files->ensureDirectoryExists(dirname($archive));
        $this->files->put($archive, $body);

        return $version;
    }

    /**
     * What the registry says the version asked for is, and where it lives.
     *
     * A dist-tag is the normal thing to declare — `latest` reads as it sounds —
     * and it is resolved to the version behind it here, so what is cached and
     * what is pinned into a generated component is the exact release rather than
     * the word that pointed at it.
     *
     * @return array{0: string, 1: string, 2: string|null}
     */
    private function resolve(): array
    {
        // A scoped name holds a slash, which is one path segment to the
        // registry and two to anything reading a URL.
        $name = str_replace('/', '%2f', $this->package);

        $response = Http::withHeaders(['User-Agent' => 'shape'])
            ->timeout(30)
            ->retry(3, 200, throw: false)
            ->get("https://registry.npmjs.org/{$name}");

        if (! $response->successful()) {
            throw new RuntimeException("Could not read [{$this->package}] from the npm registry for [{$this->set}]: it answered {$response->status()}.");
        }

        /** @var array{versions?: array<string, array{dist?: array{tarball?: string, integrity?: string}}>, dist-tags?: array<string, string>} $meta */
        $meta = (array) $response->json();

        $version = $meta['versions'][$this->reference] ?? null
            ? $this->reference
            : ($meta['dist-tags'][$this->reference] ?? null);

        if (! is_string($version) || ! isset($meta['versions'][$version])) {
            throw new RuntimeException("The npm registry has no [{$this->reference}] of [{$this->package}] for [{$this->set}]. Name an exact version, or a tag such as [latest].");
        }

        $dist = $meta['versions'][$version]['dist'] ?? [];
        $tarball = $dist['tarball'] ?? null;

        if (! is_string($tarball) || $tarball === '') {
            throw new RuntimeException("The npm registry lists no archive for [{$this->package}@{$version}], which [{$this->set}] is read from.");
        }

        $integrity = $dist['integrity'] ?? null;

        return [$version, $tarball, is_string($integrity) ? $integrity : null];
    }

    /**
     * Check the bytes against the hash the registry published for them.
     *
     * The one thing a registry offers that a repository archive does not, so it
     * is not skipped: everything downstream — unpacking, and then writing the
     * drawings into a consumer's application — treats these bytes as the set.
     *
     * An older package may state no integrity at all, which is nothing to check
     * rather than a failure.
     */
    private function verify(string $body, string $version, ?string $integrity): void
    {
        if ($integrity === null || ! str_starts_with($integrity, 'sha512-')) {
            return;
        }

        $expected = substr($integrity, 7);
        $actual = base64_encode(hash('sha512', $body, true));

        if (! hash_equals($expected, $actual)) {
            throw new RuntimeException("The archive for [{$this->package}@{$version}] does not match the integrity hash the registry states for it, so [{$this->set}] was not unpacked.");
        }
    }

    /**
     * @return array<string, string>
     */
    protected function origin(): array
    {
        return ['npm' => $this->package, 'version' => $this->reference];
    }
}
