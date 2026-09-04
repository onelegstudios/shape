# Icon

Every icon is a component of its own, generated from an icon set. Shape ships
fourteen, and they are the fourteen its own components draw — a checkbox's tick,
a pager's chevrons, the glyph a `danger` tone reaches for. Every other icon your
interface needs, you generate with `shape:icon`, from
[Heroicons](https://heroicons.com) or from whatever set you already use.

@docs('preview', name: 'icon')

## Size and style

An icon has two axes, and they are the two words the rest of this library uses,
on the same scale [text](text.md), [button](button.md) and [avatar](avatar.md)
use: `size` is how big it is, `variant` is which style it is drawn in.

@docs('preview', name: 'icon-sizes')

| Size | Drawn at |
| --- | --- |
| `xs` | 16px |
| `sm` | 20px |
| `base` | 24px — the default |

@docs('preview', name: 'icon-styles')

| Style | |
| --- | --- |
| `outline` | stroked |
| `solid` | filled |

Most call sites name only a size. The style follows from it: `xs` and `sm` are
drawn solid, because a 1.5px stroke does not read at 16px — which is also why
Heroicons draws no outline below 24px. Name a style to override that.

```blade
<x-shape::icon.shape-checked size="sm" />                     {{-- 20px, solid --}}
<x-shape::icon.shape-checked variant="outline" size="sm" />   {{-- 20px, stroked --}}
```

Each cell that a set draws is a separate drawing at its own size, so nothing is
scaled. Where a set draws no glyph of its own — outline below 24px — the largest
one it has is used and sized down; never sized up. Overriding the size with a
utility works, but prefer the size the drawing was made at:

```blade
<x-shape::icon.shape-checked size="sm" />      {{-- drawn at 20px --}}
<x-shape::icon.shape-checked class="size-5" />   {{-- 24px drawing squeezed into 20px --}}
```

Sizes and styles are whatever the icon set declares — see
[Tooling](../tooling.md#shapeicon). A set with a single style, like Lucide,
still takes `size`, and ignores `variant` rather than rendering it onto the
`<svg>`.

## Colour

Icons paint in `currentColor`, so they take the colour of whatever they sit in —
or a utility class of your own:

@docs('preview', name: 'icon-color')

Inside a [button](button.md), [badge](badge.md) or [alert](alert.md) that
happens on its own, and the icon picks up the tone.

## Resolving by name

When the name is not known until runtime, `<x-shape::icon>` takes it as a prop:

```blade
<x-shape::icon :name="$status === 'done' ? 'shape-success' : 'shape-warning'" />
```

This form resolves the component at runtime and cannot fold or memoize. Inside a
loop or a table, use the direct form.

## Accessibility

Icons render `aria-hidden="true"`, on the assumption that they sit beside a
label. When an icon carries meaning on its own, expose it and give it a name:

```blade
<x-shape::icon.shape-success aria-hidden="false" role="img" aria-label="Paid" />
```

## What Shape draws for you

Fourteen icons are resolved by Shape's own components. These are the **slots** —
named for the role they play rather than for whatever the set that drew them
calls it — and they are the list that matters when you swap sets: the list
`shape:icon:replace` generates and `shape:doctor` checks.

| Slot | Resolved by |
| --- | --- |
| `shape-checked` | the [checkbox](checkbox.md) tick |
| `shape-indeterminate` | the checkbox dash |
| `shape-prev` | the [pager](pagination.md) going back |
| `shape-next` | the pager going forward |
| `shape-expand` | the [select](select.md) arrow |
| `shape-close` | dismiss, on [alert](alert.md), [toast](../feedback.md) and overlays |
| `shape-success` | the `success` tone |
| `shape-danger` | the `danger` tone |
| `shape-warning` | the `warning` tone |
| `shape-info` | the `accent` tone |
| `shape-trend-up` | a [stat](stat.md) that rose |
| `shape-trend-down` | a stat that fell |
| `shape-trend-flat` | a stat that did not move |
| `shape-loading` | nothing — a spinner for you to use; see below |

`shape-loading` is the odd one twice over. No component resolves it; it is there
to be used by your application. And Shape draws it rather than sourcing it from a
set, because Heroicons' nearest drawing is `arrow-path` — a circular arrow, not a
loader. Your set may name something better and shadow it; Heroicons deliberately
does not.

## Overriding one

The whole list at once is
[`shape:icon:replace`](../tooling.md#slots-and-replacing-shapes-own-icons). For
one slot, publish the config and change what fills it:

```bash
php artisan vendor:publish --tag=laravel-shape-config
```

```php
'lucide' => [
    'slots' => [
        'shape-warning' => 'octagon-alert',
        // …
    ],
],
```

```bash
php artisan shape:icon shape-warning --set=lucide --force
```

Declarative, and it survives the next `shape:icon:replace` — which a one-off on
the command line would not.

To draw one yourself, write `resources/views/shape/icon/shape-warning.blade.php`
into `components_path`. That path resolves first, everywhere, including inside
Shape's own components, and the generator reports `exists, kept` rather than
overwriting it without `--force`. Copy the front matter and props off a
generated file: a hand-written slot missing `@blaze(fold: true, memo: true)`
stops folding and says nothing about it.

## Every other icon is yours

The fourteen are not a catalogue to pick from. They are what Shape keeps level
with your set, and nothing else in your interface should come from them. Where a
preview on this site names one — a `shape-plus` on a button, a `shape-trash` in a
dropdown — it is because the site has to render for a reader who has generated
nothing, not because that is what a call site of yours would say.

Icons of your own are generated the same way, from a set Shape fetches for you or
from any directory of SVGs in any set's layout:

```bash
php artisan shape:icon bell trash
php artisan shape:icon bell trash --from=resources/icons
php artisan shape:icon:all --set=lucide
```

The last of those is the set entire, which is the same generator asked for every
name it draws rather than the ones you typed.

They are then `<x-shape::icon.bell />` and `<x-shape::icon.trash />`, in your set,
under the names your set uses. Nothing already occupies those names — the three
drawings this package ships for its own README and previews are prefixed
`shape-arrow-right`, `shape-plus` and `shape-trash` precisely so that the bare
ones stay free. `shape:icon:replace` does not touch what you generate, so
regenerate it yourself when you swap sets; `shape:doctor` lists what it finds
outside the slots for exactly that reason.

An icon from a second set is written under that set's name, and can be asked for
under it too — the command takes the name the way you would write it in a
template:

```bash
php artisan shape:icon lucide.bell
```

```blade
<x-shape::icon.lucide.bell />
```

That is `shape:icon bell --set=lucide` with the set said on the name rather than
on the run, which is also what lets one run ask two sets for something:

```bash
php artisan shape:icon lucide.bell tabler.compass trash
```

A whole second set at once is `shape:icon:all --set=lucide`, and where it lands
is the set's to declare:

```php
'lucide' => [
    'namespace' => 'lucide',
    // …
],
```

See [Tooling](../tooling.md#shapeicon).

## Using a different set

An icon written into `shape.components_path` replaces the packaged one
everywhere, including inside Shape's own components — so swapping the set the
whole library draws in is one command:

```bash
php artisan shape:icon:replace --set=lucide
```

That generates the fourteen slots, under their own names, from whatever the set
calls them. Nothing at a call site changes: `<x-shape::icon.shape-close />` is
still `shape-close`, and Lucide's `x.svg` is what is behind it now.

Then say which set is yours, once, so no later run has to:

```php
'icon_set' => 'lucide',
```

Every `shape:icon` without `--set` reads that one. A flag is remembered for the
run it is typed on, and the run that forgets it writes a Heroicon into a
directory of Lucide drawings — under a slot name, which is a role and says
nothing about who drew it.

Each set says which of its drawings fills each slot, once, in `slots` — see
[Tooling](../tooling.md#slots-and-replacing-shapes-own-icons). Cover thirteen of
the fourteen and the fourteenth quietly keeps its Heroicon, which is why
`shape:doctor` counts them.

Both steps are what [`shape:install`](../tooling.md#which-set-the-library-is-drawn-in)
asks its one question to do for you, in that order, on the day you install. It is
the same pair of commands; doing it later is doing it by hand.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base` |
| `variant` | chosen by `size` | `outline`, `solid` |

`<x-shape::icon>` — the by-name form — takes `name` as well.

## Folding

Tier B — `@blaze(fold: true, memo: true)` on every named icon.

`<x-shape::icon name="…">` is `@blaze(memo: false)`: it resolves a different
component per call, which is the opposite of what memoization is for. See
[Folding](../folding.md).
