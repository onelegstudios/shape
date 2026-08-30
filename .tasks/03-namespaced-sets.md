# Namespaced sets in subdirectories

## Why

Two sets generated into `icon/` share one namespace. Non-colliding names coexist
fine — that is the normal supplementary case, and it already works:

```
check .. 4 drawing(s)      # heroicons
bell .. 1 drawing(s)       # a second set
check .. exists, kept      # collision, refused
```

Refusing is the right default, but there is no way to keep both sides. A
subdirectory gives each set its own namespace:

```
resources/views/shape/icon/lucide/bell.blade.php
→ <x-shape::icon.lucide.bell />
→ <x-shape::icon name="lucide.bell" />
→ <x-shape::button icon="lucide.bell">
```

## Already settled by probe

All three call forms work today with a hand-placed file, and **Blaze behaviour
is identical to a top-level icon**. Measured across package vs ejected path,
top-level vs nested, static vs dynamic size:

```
package, top level, static     fold:YES  memo:NO      ← memo unused; it is inlined
package, nested,    static     fold:YES  memo:NO
ejected, top level, static     fold:YES  memo:NO
ejected, nested,    static     fold:YES  memo:NO
package, top level, dynamic    fold:NO   memo:YES
package, nested,    dynamic    fold:NO   memo:YES
ejected, top level, dynamic    fold:NO   memo:YES
ejected, nested,    dynamic    fold:NO   memo:YES
```

`icon="lucide.bell"` on a button folds, with the SVG baked into the button's own
compiled body. `shape:doctor` already recurses (`DoctorCommand.php:154` uses
`Finder::in()`). `shape:eject` already writes nested paths
(`EjectCommand.php:133`). Tailwind's `@source "../views"` covers it.

So this task is ergonomics and a flag. There is no folding work in it.

## The change

`--namespace=lucide`, which writes to `{destination}/{namespace}/` instead of
making the caller hand-write `--to=resources/views/shape/icon/lucide`.

Only that. Do **not** namespace by default: the primary set stays flat so that
`<x-shape::icon.check />` keeps working and call sites keep one spelling.

## Files

- `src/Console/Commands/IconCommand.php:35` — the signature.
- `src/Console/Commands/IconCommand.php:429` — `destination()` appends it.
- Validate the namespace is a single kebab-case segment. `--namespace=../..`
  writing outside the components path is the one way this flag can do damage.

## Known rough edge

A bare set name throws rather than rendering nothing:

```
<x-shape::icon name="lucide" />
→ ViewException: Unable to locate a class or view for component [shape::icon.lucide]
```

Blade resolves a directory to `index.blade.php`. The dispatcher's `filled($name)`
guard does not help — the name *is* filled. Today a null name renders nothing,
so this is a new shape of failure: a 500 rather than a blank, if a set name ever
reaches that prop from data.

Two options, neither obviously right:

1. Leave it. It is a programming error and Blade's message names the component.
2. Guard it in `resources/views/shape/icon/index.blade.php` by checking the
   resolved view exists. That costs a `View::exists()` per call on the
   already-unfoldable path, and buys a silent blank instead of a loud error —
   which is arguably worse.

Decide when implementing; option 1 unless there is a real call site that can
produce a bare set name.

## Recommendation

Build the flag, keep flat as the default, and reach for namespacing only when
two sets genuinely collide. For the Heroicons-plus-gaps case, flat is better:
one spelling everywhere, and no collisions by construction.

## Tests

- `--namespace` writes to the subdirectory and the three call forms resolve.
- A nested icon folds, and folds when nested inside a button's fold.
- `--namespace=../escape` is refused.
- A collision between a flat name and a namespaced one is not a collision.
