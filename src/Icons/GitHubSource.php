<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Icons;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * A set fetched from the repository that draws it.
 *
 * Two ways in, because the two shapes of run want different things:
 *
 * - **The tarball**, `codeload.github.com/{repo}/tar.gz/{ref}`, is one request
 *   for the whole set. It is the only form that can answer `shape:icon:all`,
 *   because a raw file fetch has no directory listing, and it is the one that
 *   keeps `shape:icon:replace` off the rate limiter — twelve names over six
 *   cells is seventy-two requests the other way.
 * - **Raw files**, `raw.githubusercontent.com/{repo}/{ref}/{path}`, for the few
 *   names typed on the command line. `shape:icon bell` wants one drawing, and
 *   downloading a repository to find it is the wrong trade. A 404 there maps
 *   cleanly onto "this style has no drawing at this size", which is the same
 *   answer the matrix already models.
 *
 * A repository is a whole project and a set is one directory of it, which is
 * what the archive is filtered down to on the way in. Where that filtering is
 * not enough — a repository shaped for a font rather than for its drawings — a
 * published package is the better source, and `NpmSource` is that.
 */
final class GitHubSource extends ArchiveSource
{
    /**
     * Paths that came back 404, so a second cell resolving to the same missing
     * drawing does not ask again.
     *
     * @var array<string, true>
     */
    private array $missing = [];

    /**
     * @param  string  $set  The set's name in `shape.icon_sets`, for anything this has to say out loud.
     * @param  string  $repo  `owner/name` on GitHub.
     * @param  string  $ref  A branch, a tag, or a commit.
     * @param  string  $path  The subdirectory of the repository the set lives in, if any.
     * @param  string  $base  Where fetched sets are cached, usually under `storage/framework`.
     * @param  bool  $offline  Refuse to fetch, and answer from the cache or not at all.
     * @param  bool  $whole  Whether this run wants the entire set, which is one request rather than one per drawing.
     * @param  bool  $flatten  Whether the set's own subdirectories under `path` are collapsed into one on the way in.
     * @param  bool  $archive  Whether the repository can be pulled whole, or is too large to be read that way.
     */
    public function __construct(
        Filesystem $files,
        string $set,
        private readonly string $repo,
        string $ref,
        string $path,
        string $base,
        bool $offline = false,
        bool $whole = false,
        bool $flatten = false,
        bool $archive = true,
    ) {
        parent::__construct($files, $set, $ref, $path, $base, $offline, $whole, $flatten, $archive);
    }

    /**
     * Pull the repository's own archive, and read the commit out of it.
     */
    protected function download(string $archive): ?string
    {
        $response = Http::withHeaders(['User-Agent' => 'laravel-shape'])
            ->timeout(120)
            ->retry(3, 200, throw: false)
            ->get("https://codeload.github.com/{$this->repo}/tar.gz/{$this->reference}");

        if (! $response->successful()) {
            throw new RuntimeException("Could not fetch [{$this->set}] from [{$this->repo}] at [{$this->reference}]: GitHub answered {$response->status()}.");
        }

        $this->files->ensureDirectoryExists(dirname($archive));
        $this->files->put($archive, $response->body());

        return $this->commit($archive);
    }

    /**
     * Fetch one drawing, and say whether there was one to fetch.
     *
     * No retry, deliberately: a 404 here is an answer rather than a failure —
     * it is how a style with no drawing at this size reports itself — and
     * retrying it three times would turn a name this set simply does not have
     * into a dozen requests.
     */
    protected function fetchOne(string $path): bool
    {
        if (isset($this->missing[$path])) {
            return false;
        }

        $within = rtrim($this->path.'/'.$path, '/');

        $response = Http::withHeaders(['User-Agent' => 'laravel-shape'])
            ->timeout(30)
            ->get("https://raw.githubusercontent.com/{$this->repo}/{$this->reference}/{$within}");

        if ($response->status() === 404) {
            $this->missing[$path] = true;

            return false;
        }

        if (! $response->successful()) {
            throw new RuntimeException("Could not fetch [{$within}] from [{$this->repo}] at [{$this->reference}]: GitHub answered {$response->status()}.");
        }

        $target = $this->root().'/'.$within;

        $this->files->ensureDirectoryExists(dirname($target));
        $this->files->put($target, $response->body());

        $this->remember($this->resolve(), complete: false);

        return true;
    }

    /**
     * @return array<string, string>
     */
    protected function origin(): array
    {
        return ['repo' => $this->repo, 'ref' => $this->reference];
    }

    /**
     * The commit an archive was cut from.
     *
     * `git archive` writes it into the pax global header as a comment, which
     * GitHub's tarballs carry, so the resolved SHA is already in the bytes and
     * costs no second request to a rate-limited API.
     *
     * It is in the first block of them, which is why this reads the file as a
     * stream and stops: a set is a repository, a repository can be fifty
     * megabytes unpacked, and inflating all of it to look at two kilobytes is
     * how this exhausted a default `memory_limit` on Lucide.
     */
    private function commit(string $archive): ?string
    {
        $handle = @gzopen($archive, 'rb');

        if ($handle === false) {
            return null;
        }

        $header = (string) gzread($handle, 2048);

        gzclose($handle);

        return preg_match('/comment=([0-9a-f]{40})/', $header, $matches) === 1
            ? $matches[1]
            : null;
    }

    /**
     * The commit behind a ref, for a run that never pulled an archive.
     *
     * One request, and a cheap one — GitHub answers this media type with the
     * bare SHA and nothing else. Failure is tolerated rather than fatal: an
     * unauthenticated caller who has run out of API requests should still get
     * their icon, with a header that names the ref instead of the commit.
     */
    private function resolve(): ?string
    {
        $known = $this->revision();

        if ($known !== null) {
            return $known;
        }

        if (preg_match('/^[0-9a-f]{40}$/', $this->reference) === 1) {
            return $this->reference;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'laravel-shape',
                'Accept' => 'application/vnd.github.sha',
            ])->timeout(15)->get("https://api.github.com/repos/{$this->repo}/commits/{$this->reference}");
        } catch (Throwable) {
            return null;
        }

        $sha = trim($response->body());

        return $response->successful() && preg_match('/^[0-9a-f]{40}$/', $sha) === 1 ? $sha : null;
    }
}
