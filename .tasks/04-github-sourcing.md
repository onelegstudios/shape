# Source icons from GitHub

## Why

`--from` requires a directory the user already has. Heroicons is not a Composer
dependency of this package, so "Regenerate; don't hand-edit" currently asks for
a checkout nobody was told to make. The 15 shipped icons were last regenerated
by extracting SVGs back out of the components — that worked, and it is a trick,
not a workflow.

The point of the generator is that the set a consumer wants is reachable. Right
now it is reachable only if they fetch it themselves.

## The change

### A source behind an interface

```php
interface IconSource
{
    public function has(string $path): bool;
    public function get(string $path): string;
    /** @return list<string> */
    public function names(string $directory): array;
}
```

- `DirectorySource` — today's `--from`, so the fast tests keep running against
  `tests/fixtures/icons` with no network.
- `GitHubSource` — fetches, caches, then answers from the cache.

`IconCommand` stops knowing where bytes come from. `matrix()`
(`IconCommand.php:120`) and `names()` (`IconCommand.php:373`) are the only two
places that touch the filesystem today, and both become source calls.

### Fetching

Default to the tarball: `https://codeload.github.com/{repo}/tar.gz/{ref}`,
extracted under `storage/framework/shape/icons/{set}/{ref}/`. One request, and
it is the only form that supports `--all`, listing, and search — a raw-file
fetch has no directory listing.

Keep raw per-file fetch as a fast path for a few named icons:
`https://raw.githubusercontent.com/{repo}/{ref}/{path}`. A 404 maps cleanly onto
"this style has no drawing at this size", which is what `pattern()` already
models.

### Manifest keys

```php
'heroicons' => [
    'repo' => 'tailwindlabs/heroicons',
    'ref' => 'master',
    'path' => 'optimized',
    'notice' => 'Heroicons (https://heroicons.com), MIT licensed.',
    'styles' => […],
],
```

`--from` still wins when given, and stays the way to generate from a local
directory or a set with no upstream.

### Pinning

Write `shape-icons.json` beside the components, mirroring what `shape:eject`
already does with `shape-eject.json` (`EjectCommand.php:278`): set, ref, resolved
commit SHA, and a digest per icon. That buys `shape:icon --status` — *upstream
redrew this glyph* — and makes `--force` safe to reason about. Put the resolved
SHA in the generated file's header next to the licence notice.

## Files

- New `src/Icons/IconSource.php`, `src/Icons/DirectorySource.php`,
  `src/Icons/GitHubSource.php`.
- `src/Console/Commands/IconCommand.php` — `--set`/`--ref`/`--offline`, and
  `source()` (`IconCommand.php:422`) returns a source rather than a string.
- `src/IconSet.php` — `repo`, `ref`, `path`.
- `composer.json` — needs an HTTP client. Laravel's `Http` facade comes from
  `illuminate/http`, which this package does not require. Adding it is a
  dependency decision, not an implementation detail — settle it before starting.

## Risks

- **CI must never fetch.** Generated components are committed, so it should not
  need to; make sure no test reaches the network, and that `--offline` fails
  loudly rather than silently producing nothing.
- **Rate limits** on unauthenticated GitHub requests. The tarball path makes
  this mostly moot (one request per set), which is another argument for it.
- **Tarball extraction is the one place this command can write outside its
  target.** Reject entries with `..` or absolute paths.
- **Licences travel with the icons.** Font Awesome Free is CC BY 4.0 and Material
  is Apache 2.0; both carry attribution requirements a consumer inherits by
  redistributing generated components. The per-set `notice` is already written
  into every file — keep that true for any set added here, and prefer fetching
  `LICENSE` from the repo over paraphrasing it.
- **Verify every set's real layout** before shipping a manifest for it. Tabler,
  Phosphor, Bootstrap and Material Symbols all have layouts worth checking
  against the actual repository rather than writing from memory. The shipped
  `lucide` entry has only ever been exercised against a stand-in fixture
  (`tests/fixtures/icons-flat`), not a real checkout — verify it here too.

## Tests

- A fake `IconSource` drives every generator test; no test touches the network.
- `DirectorySource` reproduces today's behaviour exactly — the existing
  `IconCommandTest` should pass unchanged against it.
- Tarball extraction refuses a path-traversing entry.
- `--offline` with a cold cache fails with a message naming the set and ref.
- The lockfile round-trips, and `--status` reports an icon whose upstream digest
  moved.
