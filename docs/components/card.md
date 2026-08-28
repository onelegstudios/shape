# Card

@docs('preview', name: 'card')

## No border, on purpose

A card separates itself from the page with a surface shift and a resting
shadow. It draws no border, because a border is the least effective of the three
and the easiest to overuse.

`border` is there for the case the other two can't cover — a card sitting on a
surface too close to its own to read against:

```blade
<x-shape::card border>…</x-shape::card>
```

| Prop | Default | Values |
| --- | --- | --- |
| `padding` | `base` | `none`, `sm`, `base`, `lg` |
| `border` | `false` | adds a hairline border |

Padding and the gap between children move together — `sm` is a tighter card
*and* tighter stacking. Letting them be set apart is how cards end up looking
cramped at one size and loose at another.

## The card owns the space between its children

Nothing inside a card sets its own outer margin. The gap belongs to the parent,
which is what stops "which element does this space belong to" from ever becoming
a question you have to answer.

## Header and footer are components, not slots

```blade
<x-shape::card>
    <x-shape::card.header>
        <x-shape::heading size="lg">Acme Corp</x-shape::heading>
        <x-shape::text size="sm" variant="muted">Invoice #1042</x-shape::text>
    </x-shape::card.header>

    <x-shape::separator />

    <x-shape::text>Thirty day terms.</x-shape::text>

    <x-shape::card.footer>
        <x-shape::button variant="primary">Send receipt</x-shape::button>
        <x-shape::button variant="ghost">Void</x-shape::button>
    </x-shape::card.footer>
</x-shape::card>
```

Named slots would read a little better at the call site, but deciding whether a
slot has content is a question only answerable at render time — and asking it
would take the card off the fold path entirely. Separate components ask nothing.

Neither region draws a rule of its own. If you want one, compose a
[separator](separator.md); most of the time the gap is enough.

## Elevation

Cards use `shadow-sm`, the "raised" step. See [Elevation](../elevation.md) for
the full mapping, and for why Shape doesn't own a shadow scale.

## Folding

Tier A — `@blaze(fold: true)` on all three files.

See [Folding](../folding.md).
