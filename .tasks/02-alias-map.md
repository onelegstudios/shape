# Alias map, so a set can replace Shape's own icons

## Why

Shape draws icons in twelve places, and all twelve name a Heroicons spelling:

| Name | Drawn by |
| --- | --- |
| `check`, `minus` | `checkbox.blade.php:70-71` |
| `chevron-left`, `chevron-right` | `pagination.blade.php:80-105` |
| `chevron-down` | `select/index.blade.php:90` |
| `x-mark` | `overlay/close.blade.php:35` |
| `check-circle`, `x-circle`, `exclamation-triangle`, `information-circle` | the tone maps in `alert`, `badge`, `toast` |
| `arrow-trending-up`, `arrow-trending-down` | `stat.blade.php:42` |

Swapping the icon set already works as a *mechanism* — `components_path`
resolves ahead of the package (`ShapeServiceProvider.php:84-88`), so a file at
`resources/views/shape/icon/check.blade.php` replaces the packaged one
everywhere, including inside Shape's own components, with no config and no cost
to folding.

What blocks it is names. Lucide has `x` not `x-mark`, `info` not
`information-circle`, `circle-check`, `circle-x`, `triangle-alert`,
`trending-up`. The matrix model generalised; the vocabulary did not.

## The change

A per-set alias map from Shape's canonical name to the set's own:

```php
'lucide' => [
    'notice' => '…',
    'styles' => […],
    'aliases' => [
        'x-mark' => 'x',
        'check-circle' => 'circle-check',
        'x-circle' => 'circle-x',
        'exclamation-triangle' => 'triangle-alert',
        'information-circle' => 'info',
        'arrow-trending-up' => 'trending-up',
        'arrow-trending-down' => 'trending-down',
        // check, minus, chevron-* carry over unchanged
    ],
],
```

`shape:icon x-mark --set=lucide` then reads `x.svg` and writes
`x-mark.blade.php`. The component keeps Shape's name; only the source file
differs. Aliases apply in one direction only.

Then `--replace`:

```bash
php artisan shape:icon --replace --set=lucide --from=…
```

which generates exactly the twelve names above into `components_path`, flat. On
a fresh app that directory is empty, so no `--force` and nothing else to eject.

The twelve names should be **derived, not typed**: grep the package's own
components for `<x-shape::icon.NAME` and `'name' => 'NAME'` match arms rather
than hardcoding a list that will drift the next time a component gains an icon.

## Files

- `config/shape.php` — `aliases` per set.
- `src/IconSet.php` — `sourceName(string $name): string`, defaulting to identity.
- `src/Console/Commands/IconCommand.php:120` — `matrix()` substitutes the source
  name into the pattern; the target filename stays the canonical one.
- `src/Console/Commands/IconCommand.php:373` — `names()` gains the `--replace`
  branch. Under `--all`, discovered names are the *set's* names and need
  reversing through the alias map.
- New: the list of names the package itself depends on. `src/IconSet.php` is the
  wrong home — this is a fact about the component library, not about a set.
  Consider `src/Registry.php`, which already knows about components.

## Risks

- **Verify every alias against the current Lucide release before committing
  them.** Several were renamed around v0.4xx (`alert-triangle` → `triangle-alert`,
  `check-circle` → `circle-check`) with the old names kept as aliases, so both
  may resolve and only one is canonical. Written from memory these will be
  subtly wrong.
- `--all` plus aliases is ambiguous: a set name with no canonical counterpart
  has no Shape name to be written under. Generate it under its own name.
- Partial coverage is silent. Eleven of twelve generated means the twelfth
  resolves to the packaged Heroicon and renders fine — a mixed set, no warning.
  See the doctor check below; it is the real safety net for this feature.

## Also worth doing here

`shape:doctor` gains a coverage check, since partial coverage has no symptom:

```
icon set ............................ lucide, 11 of 12 names
  information-circle ....... missing — falls back to Heroicons
```

The package can derive both halves: the names it draws, and what exists in
`components_path`. This is exactly doctor's stated remit — "the mistake that has
no symptom".

## Tests

- An alias resolves the source file and writes the canonical filename.
- `--replace` writes exactly the derived set of names.
- The derived list matches the twelve above (guards against a component gaining
  an icon without anyone noticing).
- Doctor reports a missing name, and exits non-zero.
- A set with no `aliases` key behaves exactly as today.
