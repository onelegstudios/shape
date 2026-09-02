<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Icons;

use Illuminate\Filesystem\Filesystem;
use Phar;
use PharData;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

/**
 * A set fetched as one archive, then cached and read locally.
 *
 * This is what makes the generator's promise true. The point of reading a
 * directory rather than shipping a Composer package per icon set is that the set
 * a consumer actually wants is within reach — and it was not, while reaching it
 * meant cloning something first.
 *
 * Everything here is what is the same whoever is serving the bytes: the cache
 * laid out the way the archive lays itself out, the unpacking with its refusal
 * to write outside itself, the flattening of a set filed under something its
 * names do not say, and the refusal to open an archive PHP has no room for. What
 * a subclass supplies is where the archive comes from and what a revision of it
 * is called — a commit for a repository, a version for a published package.
 *
 * Nothing here runs at render time, or indeed anywhere but this command. A
 * generated component is bytes on disk with the licence notice and the resolved
 * revision written into its header; the set it came from is a compile-time input
 * and never a runtime one.
 */
abstract class ArchiveSource implements IconSource
{
    /**
     * @var array{repo?: string, ref?: string, commit?: string, complete?: bool}|null
     */
    protected ?array $meta = null;

    private ?DirectorySource $directory = null;

    /**
     * @param  string  $set  The set's name in `shape.icon_sets`, for anything this has to say out loud.
     * @param  string  $reference  The revision asked for: a branch, tag or commit for a repository, a version for a package.
     * @param  string  $path  The subdirectory of the archive the set lives in, if any.
     * @param  string  $base  Where fetched sets are cached, usually under `storage/framework`.
     * @param  bool  $offline  Refuse to fetch, and answer from the cache or not at all.
     * @param  bool  $whole  Whether this run wants the entire set, which is one request rather than one per drawing.
     * @param  bool  $flatten  Whether the set's own subdirectories under `path` are collapsed into one on the way in.
     * @param  bool  $archive  Whether the set can be pulled whole, or is too large to be read that way.
     */
    public function __construct(
        protected readonly Filesystem $files,
        protected readonly string $set,
        protected readonly string $reference,
        protected readonly string $path,
        protected readonly string $base,
        protected readonly bool $offline = false,
        protected readonly bool $whole = false,
        protected readonly bool $flatten = false,
        protected readonly bool $archive = true,
    ) {
        // Checked here rather than at the first read, because failing loudly is
        // the entire point of the flag. A run that quietly generated nothing
        // because the cache happened to be cold would be the failure `--offline`
        // exists to make impossible.
        if ($this->offline && ! $this->cached()) {
            throw new RuntimeException("No cached copy of [{$this->set}] at [{$this->reference}] to work from, and --offline forbids fetching one. Drop --offline, or pass --from with a local checkout.");
        }
    }

    /**
     * Fetch the archive to a path, and say what revision it turned out to be.
     *
     * The one thing that differs between a repository and a published package,
     * along with what that revision is called afterwards.
     */
    abstract protected function download(string $archive): ?string;

    /**
     * Fetch one drawing on its own, where that is possible at all.
     *
     * A repository serves raw files, so `shape:icon bell` need not pull the
     * whole set. A registry serves packages and nothing smaller, so it says so
     * by keeping this answer.
     */
    protected function fetchOne(string $path): bool
    {
        return false;
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
        // A set whose repository cannot be pulled whole has no listing at all,
        // and the caller is expected to have said so before asking. Refused
        // rather than attempted, because attempting it is the fatal this flag
        // exists to prevent.
        if (! $this->archive) {
            throw new RuntimeException("Icon set [{$this->set}] is read one drawing at a time, so there is nothing to list. Name the icons you want, or pass --from with a local checkout.");
        }

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
    protected function cached(): bool
    {
        return ($this->meta()['complete'] ?? false) === true;
    }

    /**
     * Fetch and unpack the whole set, unless that has already happened.
     */
    protected function fetchSet(): void
    {
        if ($this->cached()) {
            return;
        }

        $archive = $this->root().'.tar.gz';

        $revision = $this->download($archive);

        $this->unpack($archive);

        // Not `unlink`. PHP keeps every archive it has opened in memory, keyed
        // by the filename, for the life of the process — so deleting the file
        // and fetching a second set to the same path unpacks the first one
        // again. This is the call that forgets it as well as removes it.
        Phar::unlinkArchive($archive);

        $this->remember($revision, complete: true);
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

        $this->affordable($archive);

        try {
            $iterator = new RecursiveIteratorIterator(new PharData($archive));
        } catch (Throwable $e) {
            throw new RuntimeException("The archive for [{$this->set}] at [{$this->reference}] could not be read: {$e->getMessage()}");
        }

        $root = $this->root();
        $written = 0;
        $from = [];

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

            $target = $this->target($within);

            // Two drawings landing on one filename is the one way flattening can
            // lose a drawing, and it would lose it silently — the second write
            // wins and the set is quietly one icon short. Which two files
            // collided is the whole of what a reader needs to fix it.
            if (isset($from[$target]) && $from[$target] !== $within) {
                throw new RuntimeException("Flattening [{$this->set}] would put [{$within}] and [{$from[$target]}] in the same place. The set's filenames are not unique across its directories, so it cannot be read flat.");
            }

            $from[$target] = $within;

            $this->files->ensureDirectoryExists(dirname($root.'/'.$target));
            $this->files->put($root.'/'.$target, (string) $file->getContent());

            $written++;
        }

        if ($written === 0) {
            throw new RuntimeException("The archive for [{$this->set}] at [{$this->reference}] held nothing under [{$this->path}].");
        }
    }

    /**
     * Refuse an archive PHP has no room to open.
     *
     * `PharData` reads the whole thing into memory to build its manifest, so an
     * archive larger than what is left of `memory_limit` does not fail — the
     * process is killed mid-unpack, and what a reader gets is a stack trace
     * inside a constructor rather than a reason. This is the same fact, said
     * first and in numbers.
     *
     * Compared against the compressed size, which is the optimistic reading:
     * what has to fit is the archive expanded. So this fires only where the
     * attempt was hopeless, and stays quiet for every set that fits.
     */
    private function affordable(string $archive): void
    {
        $limit = $this->limit();

        if ($limit === null) {
            return;
        }

        $size = (int) $this->files->size($archive);
        $spare = $limit - memory_get_usage(true);

        if ($size <= $spare) {
            return;
        }

        $megabytes = static fn (int $bytes): string => number_format($bytes / 1048576, 0).'MB';

        throw new RuntimeException("The archive for [{$this->set}] is {$megabytes($size)} and PHP has about {$megabytes(max($spare, 0))} left of its memory_limit to unpack it in, which is not enough — unpacking reads the whole archive into memory. Name the icons you want with shape:icon instead of running shape:icon:all, raise memory_limit, or pass --from with a local checkout.");
    }

    /**
     * What is left of `memory_limit`, or null where there is no limit.
     */
    private function limit(): ?int
    {
        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '' || $limit === '-1') {
            return null;
        }

        $units = ['k' => 1024, 'm' => 1048576, 'g' => 1073741824];
        $suffix = strtolower(substr($limit, -1));

        return (int) $limit * ($units[$suffix] ?? 1);
    }

    /**
     * Where one entry of the archive is written, relative to the cache root.
     *
     * The repository's own layout, ordinarily. A flattening set drops whatever
     * directories it files its drawings under and keeps the filename, so that
     * `icons/System/close-line.svg` lands as `icons/close-line.svg` and the set
     * on disk is one `{name}-line.svg` deep — which is a layout a pattern can
     * express, where nesting by category is not.
     *
     * Only under the set's own `path`. A licence sits at the root of the
     * repository rather than among the drawings, and moving it in beside them
     * would file it as though it were one.
     */
    private function target(string $within): string
    {
        return $this->flatten && $this->path !== '' && str_starts_with($within, $this->path.'/')
            ? $this->path.'/'.basename($within)
            : $within;
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
        // At the root of the archive, and in whatever case it is written in:
        // Remix Icon's is `License`, and a check that only knew `LICENSE`
        // dropped the one file that states the terms the drawings arrive under.
        //
        // The root is what makes it a licence rather than a name. Tabler draws
        // `license.svg` and `license-off.svg` — a document with a stamp on it —
        // and matching on the name alone kept two drawings from outside the set
        // as though they were its terms. A licence inside the set's own path is
        // kept by the rule below, like anything else there.
        if (! str_contains($within, '/') && str_starts_with(strtoupper($within), 'LICENSE')) {
            return true;
        }

        return $this->path === '' || str_starts_with($within, $this->path.'/');
    }

    /**
     * Record what the cache now holds, so a later run knows without looking.
     */
    protected function remember(?string $revision, bool $complete): void
    {
        $this->ignore();

        $meta = array_filter([
            ...$this->origin(),
            'commit' => $revision ?? $this->revision(),
            'complete' => $complete,
        ], fn (mixed $value): bool => $value !== null);

        $this->files->ensureDirectoryExists(dirname($this->root()));
        $this->files->put($this->root().'.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        /** @var array{repo?: string, ref?: string, commit?: string, complete?: bool} $meta */
        $this->meta = $meta;
    }

    /**
     * Keep the cache out of the consumer's history.
     *
     * This is a copy of somebody else's repository that exists to save fetching
     * it twice, and a set is thousands of files nobody wrote — none of it is a
     * change to their application. Laravel ignores its own storage directories
     * this way; the pattern covers the file itself as well, so there is nothing
     * here to commit at all rather than one stray `.gitignore` to explain.
     */
    protected function ignore(): void
    {
        $path = rtrim($this->base, '/').'/.gitignore';

        if ($this->files->exists($path)) {
            return;
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, "*\n");
    }

    /**
     * @return array{repo?: string, ref?: string, commit?: string, complete?: bool}
     */
    protected function meta(): array
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
     * What the cache should record about where these bytes came from.
     *
     * @return array<string, string>
     */
    abstract protected function origin(): array;

    /**
     * Where this set at this revision is cached.
     *
     * A branch name can hold a slash and so can a scoped package name, so the
     * reference is flattened rather than trusted to be one path segment.
     */
    protected function root(): string
    {
        $reference = (string) preg_replace('/[^A-Za-z0-9._-]+/', '-', $this->reference);

        return rtrim($this->base, '/')."/{$this->set}/{$reference}";
    }
}
