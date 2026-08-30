# Tasks

Planned work on `shape:icon`, in dependency order. Each file states what is true
now, what changes, what is already settled, and how it is tested.

| | Task | Depends on |
| --- | --- | --- |
| 01 | [Hoist the size scale out of the sets](01-hoist-size-scale.md) | — |
| 02 | [Alias map, so a set can replace Shape's own icons](02-alias-map.md) | 01 |
| 03 | [Namespaced sets in subdirectories](03-namespaced-sets.md) | — |
| 04 | [Source icons from GitHub](04-github-sourcing.md) | 01, and 02 to be useful |

01 first: it changes the manifest's shape, and 02 and 04 both add keys to it.
03 is independent and can be done at any point.

## What is already settled

Established by probe during design, and true of the code as it stands:

- **Both props fold.** `size` and `variant` are static at almost every call
  site, so a `switch` on `$variant.':'.$size` compiles away to one `<svg>`. A
  dynamic `size` drops to memo, which hits, because there are only three sizes.
- **Blaze does not care where a generated icon lives.** Package path or ejected
  path, top level or nested, folding and memoization are identical — each
  generated file carries its own `@blaze(fold: true, memo: true)`, and Blaze
  reads front matter before path config
  (`Folder.php:88`, `Memoizer.php:86`, `BlazeManager.php:122`).
- **The set is a compile-time input, never a runtime one.** Everything a set
  decides is written into the component as a literal. Nothing added by these
  tasks may read `shape.icon_sets` at render time.
