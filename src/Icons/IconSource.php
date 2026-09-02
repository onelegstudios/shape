<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Icons;

/**
 * Where the icon commands read drawings from.
 *
 * The generator used to take a directory and read it, which made "Regenerate;
 * don't hand-edit" an instruction nobody could follow: Heroicons is not a
 * Composer dependency of this package, so the header asked for a checkout that
 * was never mentioned anywhere. The fifteen shipped icons were last regenerated
 * by extracting the SVGs back out of the components, which worked, and which is
 * a trick rather than a workflow.
 *
 * So the commands stop knowing where bytes come from. They walk the matrix, ask
 * for cells by their path inside the set, and something else decides whether
 * that means a directory on disk or a tarball from GitHub.
 *
 * Every path here is relative to the root of the set — `24/solid/check.svg`,
 * never `/home/…` and never a URL. What a set's root *is* belongs to the
 * implementation: a `--from` directory is one, and a subdirectory of a fetched
 * repository is another.
 */
interface IconSource
{
    /**
     * Whether this source holds a drawing at that path.
     *
     * A source that has to fetch to find out should keep what it fetched, since
     * `get()` is the very next call for every path that answers true.
     */
    public function has(string $path): bool;

    /**
     * The bytes of one drawing, for a path `has()` has already answered for.
     */
    public function get(string $path): string;

    /**
     * The names of the SVGs in one directory of the set, without extensions.
     *
     * These are the *set's* names, not Shape's — `shape:icon:all` reads each one
     * back through the set's own patterns before anything is written. An empty
     * string asks about the root, which is what a flat set has instead of
     * directories.
     *
     * @return list<string>
     */
    public function names(string $directory): array;

    /**
     * What upstream revision these drawings came from, if that means anything.
     *
     * A commit SHA for a fetched set, and null for a directory — nobody can say
     * what a folder of SVGs is a revision *of*. It is written into the header of
     * every generated component beside the licence notice, so that a file can be
     * traced back to the drawing it came from without a lockfile in hand.
     */
    public function revision(): ?string;
}
