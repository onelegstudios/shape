# Hoist the size scale out of the sets

## Why

The size scale is declared per set, so mixing two sets can mean mixing scales.
A set declaring only `base` generates:

```php
$classes = Shape::classes('shrink-0')
    ->add(match ($size) {
        default => '[:where(&)]:size-6',
    });
```

So `<x-shape::icon.bell size="sm" />` renders at **24px** while
`<x-shape::icon.check size="sm" />` renders at 20px — same call site, same word,
different result depending on which set the icon came from, and no error. That
is exactly the failure a supplementary set invites, and supplementary sets are
the normal case: no set has everything.

`docs/tooling.md` already claims the opposite — "it is the library's own scale,
so a call site reads the same whichever set is behind it". The docs describe the
intent; the code does not enforce it.

## The change

One scale for the library. A set says only *which cells it draws*.

`config/shape.php`, new key beside `icon_sets`:

```php
'icon_sizes' => [
    'xs' => ['class' => 'size-4', 'prefer' => 'solid'],
    'sm' => ['class' => 'size-5', 'prefer' => 'solid'],
    'base' => ['class' => 'size-6', 'prefer' => 'outline'],
],
```

and each set loses its `sizes` key:

```php
'heroicons' => [
    'notice' => 'Heroicons (https://heroicons.com), MIT licensed.',
    'styles' => [
        'solid' => ['xs' => '16/solid/{name}.svg', 'sm' => '20/solid/{name}.svg', 'base' => '24/solid/{name}.svg'],
        'outline' => ['base' => '24/outline/{name}.svg'],
    ],
],
'lucide' => [
    'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
    'styles' => ['outline' => ['base' => '{name}.svg']],
],
```

Every generated icon then emits the identical size `match`, whichever set it
came from. A set with no drawing at a size still resolves through
`IconSet::pattern()` to its largest and is sized down by the class — which is
already the behaviour, and now the only behaviour.

## Files

- `config/shape.php:41` — add `icon_sizes`, strip `sizes` from both sets.
- `src/IconSet.php:36` — constructor takes the shared scale as a separate
  argument from the set definition.
- `src/IconSet.php:50` — `fromArray(string $name, mixed $definition, mixed $sizes)`.
  The check at `IconSet.php:80` ("draws [x] at [y], which is not one of its
  sizes") now validates against the shared scale, which makes it a better error:
  a set naming a size the library doesn't have is a typo, every time.
- `src/Console/Commands/IconCommand.php:408` — `set()` reads both config keys.
- Regenerate the 15 icons. Output should be **byte-identical** — this task
  changes where the scale is declared, not what it says.

`sizes()`, `defaultSize()`, `styleFor()`, `classFor()` keep their signatures.

## Already settled

`prefer` moves to the shared scale unchanged, and naming a style a set does not
have is already safe: `IconSet::styleFor()` (`src/IconSet.php:137`) tests
`isset($this->styles[$prefer])` and falls through. So a library-level
`prefer => 'solid'` is honoured by Heroicons and ignored by Lucide, which has
only one style. No new code needed for that.

## Risks

- **A published config is now wrong in a way that throws.** A consumer who
  published `config/shape.php` before this has `sizes` inside each set and no
  `icon_sizes`. `fromArray` should say so plainly rather than fail on a missing
  key three methods later. Pre-1.0, so no shim — just a good message.
- The set-level `sizes` key should be *rejected*, not ignored, or someone will
  set it and wonder why it does nothing.

## Tests

- `tests/Unit/IconSetTest.php` — scale comes from the shared declaration; a set
  drawing at a size the scale doesn't declare throws; a set still resolves its
  largest drawing for a cell it doesn't draw.
- New: two sets with different `styles` generate the **same** size `match`. This
  is the regression this task exists to prevent — assert the generated source of
  a one-style set contains all three arms.
- Regeneration diff is empty against the committed icons.
