<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Icons;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use PharData;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

/**
 * A set fetched from the repository that draws it, then cached and read locally.
 *
 * This is what makes the generator's promise true. The point of reading a
 * directory rather than shipping a Composer package per icon set is that the set
 * a consumer actually wants is within reach — and it was not, while reaching it
 * meant cloning something first.
 *
 * Two ways in, because the two shapes of run want different things:
 *
 * - **The tarball**, `codeload.github.com/{repo}/tar.gz/{ref}`, is one request
 *   for the whole set. It is the only form that can answer `--all`, because a
 *   raw file fetch has no directory listing, and it is the one that keeps
 *   `--replace` off the rate limiter — twelve names over six cells is seventy-two
 *   requests the other way.
 * - **Raw files**, `raw.githubusercontent.com/{repo}/{ref}/{path}`, for the few
 *   names typed on the command line. `shape:icon bell` wants one drawing, and
 *   downloading three megabytes to find it is the wrong trade. A 404 there maps
 *   cleanly onto "this style has no drawing at this size", which is the same
 *   answer `pattern()` already models.
 *
 * Either way the bytes land in the same cache, laid out exactly as the
 * repository lays them out, and every read after the first is a local one. A
 * later `--all` over a cache primed by raw fetches simply pulls the tarball and
 * overwrites it, because the tarball is a superset of anything raw put there.
 *
 * Nothing here runs at render time, or indeed anywhere but this command. A
 * generated component is bytes on disk with the licence notice and the resolved
 * commit written into its header; the set it came from is a compile-time input
 * and never a runtime one.
 */
final class GitHubSource implements IconSource
{
    /**
     * Paths that came back 404, so a second cell resolving to the same missing
     * drawing does not ask again.
     *
     * @var array<string, true>
     */
    private array $missing = [];

    /**
     * @var array{repo?: string, ref?: string, commit?: string, complete?: bool}|null
     */
    private ?array $meta = null;

    private ?DirectorySource $directory = null;

    /**
     * @param  string  $set  The set's name in `shape.icon_sets`, for anything this has to say out loud.
     * @param  string  $repo  `owner/name` on GitHub.
     * @param  string  $ref  A branch, a tag, or a commit.
     * @param  string  $path  The subdirectory of the repository the set lives in, if any.
     * @param  string  $base  Where fetched sets are cached, usually under `storage/framework`.
     * @param  bool  $offline  Refuse to fetch, and answer from the cache or not at all.
     * @param  bool  $whole  Whether this run wants the entire set, which is one request rather than one per drawing.
     */
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $set,
        private readonly string $repo,
        private readonly string $ref,
        private readonly string $path,
        private readonly string $base,
        private readonly bool $offline = false,
        private readonly bool $whole = false,
    ) {
        // Checked here rather than at the first read, because failing loudly is
        // the entire point of the flag. A run that quietly generated nothing
        // because the cache happened to be cold would be the failure `--offline`
        // exists to make impossible.
        if ($this->offline && ! $this->cached()) {
            throw new RuntimeException("No cached copy of [{$this->set}] at [{$this->ref}] to work from, and --offline forbids fetching one. Drop --offline, or pass --from with a local checkout.");
        }
    }

    public function has(string $path): bool
    {
        if ($this->whole || $this->cached()) {
            $this->fetchSet();

            return $this->directory()->has($path);
        }

        if ($this->directory()->has($path)) {
            return true;
        }

        if (isset($this->missing[$path])) {
            return false;
        }

        return $this->fetchOne($path);
    }

    public function get(string $path): string
    {
        return $this->directory()->get($path);
    }

    /**
     * @return list<string>
     */
    public function names(string $directory): array
    {
        // There is no listing a raw file fetch can answer, so this is the one
        // call that always costs the whole set.
        $this->fetchSet();

        return $this->directory()->names($directory);
    }

    /**
     * The commit these drawings were fetched at.
     *
     * Resolved rather than assumed: `master` names a moving target, and a header
     * that recorded the branch would say nothing about which drawing is in the
     * file underneath it.
     */
    public function revision(): ?string
    {
        $commit = $this->meta()['commit'] ?? null;

        return is_string($commit) && $commit !== '' ? $commit : null;
    }

    /**
     * Read the cache the way any other directory is read.
     *
     * Once the bytes are on disk there is nothing left that is GitHub's problem,
     * which is why the set's subdirectory is folded in here: a repository that
     * keeps its drawings under `optimized/` is a directory source rooted there.
     */
    private function directory(): DirectorySource
    {
        return $this->directory ??= new DirectorySource(
            $this->files,
            rtrim($this->root().'/'.$this->path, '/'),
        );
    }

    /**
     * Whether the whole set is already unpacked and can be read without asking
     * GitHub anything.
     */
    private function cached(): bool
    {
        return ($this->meta()['complete'] ?? false) === true;
    }

    /**
     * Fetch and unpack the whole set, unless that has already happened.
     */
    private function fetchSet(): void
    {
        if ($this->cached()) {
            return;
        }

        $archive = $this->root().'.tar.gz';

        $response = Http::withHeaders(['User-Agent' => 'laravel-shape'])
            ->timeout(120)
            ->retry(3, 200, throw: false)
            ->get("https://codeload.github.com/{$this->repo}/tar.gz/{$this->ref}");

        if (! $response->successful()) {
            throw new RuntimeException("Could not fetch [{$this->set}] from [{$this->repo}] at [{$this->ref}]: GitHub answered {$response->status()}.");
        }

        $this->files->ensureDirectoryExists(dirname($archive));
        $this->files->put($archive, $response->body());

        $commit = $this->commit($archive);

        $this->unpack($archive);
        $this->files->delete($archive);

        $this->remember($commit, complete: true);
    }

    /**
     * Fetch one drawing, and say whether there was one to fetch.
     *
     * No retry, deliberately: a 404 here is an answer rather than a failure —
     * it is how a style with no drawing at this size reports itself — and
     * retrying it three times would turn a name this set simply does not have
     * into a dozen requests.
     */
    private function fetchOne(string $path): bool
    {
        $within = rtrim($this->path.'/'.$path, '/');

        $response = Http::withHeaders(['User-Agent' => 'laravel-shape'])
            ->timeout(30)
            ->get("https://raw.githubusercontent.com/{$this->repo}/{$this->ref}/{$within}");

        if ($response->status() === 404) {
            $this->missing[$path] = true;

            return false;
        }

        if (! $response->successful()) {
            throw new RuntimeException("Could not fetch [{$within}] from [{$this->repo}] at [{$this->ref}]: GitHub answered {$response->status()}.");
        }

        $target = $this->root().'/'.$within;

        $this->files->ensureDirectoryExists(dirname($target));
        $this->files->put($target, $response->body());

        $this->remember($this->resolve(), complete: false);

        return true;
    }

    /**
     * Unpack the archive into the cache, keeping only the set's own subtree.
     *
     * Entries are read and written one at a time rather than handed to
     * `PharData::extractTo()`, for two reasons. The first is size: a repository
     * is a whole project and a set is one directory of it, so there is no reason
     * to spill the other ninety percent onto somebody's disk. The second is that
     * writing is the dangerous half — an archive is the one input to this
     * command that can name where it wants to be put — and it belongs in code
     * that can be read, rather than delegated.
     *
     * `PharData` will not surface a traversing entry to begin with; the suite
     * proves that against a hostile archive it cannot itself construct. The
     * check below is what makes that a property of this package rather than of
     * whichever tar implementation happens to be underneath it.
     */
    private function unpack(string $archive): void
    {
        if (! extension_loaded('phar')) {
            throw new RuntimeException("Unpacking [{$this->set}] needs PHP's phar extension, which is not loaded. Pass --from with a local checkout instead.");
        }

        try {
            $iterator = new RecursiveIteratorIterator(new PharData($archive));
        } catch (Throwable $e) {
            throw new RuntimeException("The archive for [{$this->set}] at [{$this->ref}] could not be read: {$e->getMessage()}");
        }

        $root = $this->root();
        $written = 0;

        foreach ($iterator as $file) {
            $entry = str_replace('\\', '/', $iterator->getSubPathname());

            if (str_starts_with($entry, '/') || in_array('..', explode('/', $entry), true)) {
                throw new RuntimeException("The archive for [{$this->set}] holds an entry that points outside it: [{$entry}].");
            }

            // GitHub wraps everything in one `{name}-{ref}` directory, which is
            // an artefact of how the archive was made rather than part of the
            // set's layout, so it is dropped here and nowhere else.
            $within = (string) preg_replace('#^[^/]+/#', '', $entry);

            if ($within === '' || ! $this->wanted($within)) {
                continue;
            }

            $this->files->ensureDirectoryExists(dirname($root.'/'.$within));
            $this->files->put($root.'/'.$within, (string) $file->getContent());

            $written++;
        }

        if ($written === 0) {
            throw new RuntimeException("The archive for [{$this->set}] at [{$this->ref}] held nothing under [{$this->path}].");
        }
    }

    /**
     * Whether an entry is part of this set, or the rest of the repository.
     *
     * A licence travels with the drawings it covers, so it is kept whatever the
     * set's own subdirectory is: a consumer redistributing generated components
     * inherits an attribution requirement, and the file that states it should be
     * the repository's own words rather than a paraphrase of them.
     */
    private function wanted(string $within): bool
    {
        if (str_starts_with(basename($within), 'LICENSE')) {
            return true;
        }

        return $this->path === '' || str_starts_with($within, $this->path.'/');
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

        if (preg_match('/^[0-9a-f]{40}$/', $this->ref) === 1) {
            return $this->ref;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'laravel-shape',
                'Accept' => 'application/vnd.github.sha',
            ])->timeout(15)->get("https://api.github.com/repos/{$this->repo}/commits/{$this->ref}");
        } catch (Throwable) {
            return null;
        }

        $sha = trim($response->body());

        return $response->successful() && preg_match('/^[0-9a-f]{40}$/', $sha) === 1 ? $sha : null;
    }

    /**
     * Record what the cache now holds, so a later run knows without looking.
     */
    private function remember(?string $commit, bool $complete): void
    {
        $meta = array_filter([
            'repo' => $this->repo,
            'ref' => $this->ref,
            'commit' => $commit ?? $this->revision(),
            'complete' => $complete,
        ], fn (mixed $value): bool => $value !== null);

        $this->files->ensureDirectoryExists(dirname($this->root()));
        $this->files->put($this->root().'.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        /** @var array{repo?: string, ref?: string, commit?: string, complete?: bool} $meta */
        $this->meta = $meta;
    }

    /**
     * @return array{repo?: string, ref?: string, commit?: string, complete?: bool}
     */
    private function meta(): array
    {
        if ($this->meta !== null) {
            return $this->meta;
        }

        $path = $this->root().'.json';

        if (! $this->files->exists($path)) {
            return $this->meta = [];
        }

        $decoded = json_decode($this->files->get($path), true);

        /** @var array{repo?: string, ref?: string, commit?: string, complete?: bool} $meta */
        $meta = is_array($decoded) ? $decoded : [];

        return $this->meta = $meta;
    }

    /**
     * Where this set at this ref is cached.
     *
     * The ref is a branch name, and a branch name can hold a slash, so it is
     * flattened rather than trusted to be one path segment.
     */
    private function root(): string
    {
        $ref = (string) preg_replace('/[^A-Za-z0-9._-]+/', '-', $this->ref);

        return rtrim($this->base, '/')."/{$this->set}/{$ref}";
    }
}
