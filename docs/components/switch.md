# Switch

A setting that applies the moment it moves.

@docs('preview', name: 'switch', layout: 'stack')

## Description

@docs('preview', name: 'switch-description', layout: 'stack')

## Tones

`tone` defaults to `brand` here rather than `neutral`, which is the one place
in the library that differs — a switch is a live setting, and reading as "on" is
the whole point:

@docs('preview', name: 'switch-tones')

## Disabled

@docs('preview', name: 'switch-disabled', layout: 'stack')

## Switch or checkbox?

A switch applies immediately. A [checkbox](checkbox.md) waits for a submit.
Inside a form with a save button, reach for a checkbox.

## It is a real checkbox

`role="switch"` on a native `<input type="checkbox">` is the whole accessibility
story: a screen reader announces on and off instead of checked and unchecked,
and every native keyboard and form behaviour is kept.

The knob moves on `:checked`, which the browser handles — so this component
works before the script exists and keeps working if it never loads. It also
holds still for anyone who asked for reduced motion.

## Theming

The track is `shape-300` — `shape-700` in dark mode — until it is checked, and
then it is `--shape-tone`, which is why the default tone here is `brand` rather
than neutral. The knob is white in both modes, with `shadow-sm` under it, and
the ring is `--shape-ring`. A [retint](../theming.md) moves the on state and
leaves the off state where it is, which is the right split: off is chrome.

The bag lands on the `<input>` that draws the track, so a class reaches it:

@docs('preview', name: 'switch-override', layout: 'stack')

The knob is a sibling span sized and travelled in the component, so resizing the
track from a call site leaves it behind. A switch of another size is three
declarations in a rule of your own, and the travel is the track's width less the
knob and its two insets:

```css
[data-shape-switch] [data-shape-control] { height: 1.5rem; width: 2.75rem; }
[data-shape-switch] [data-shape-control] + span {
    width: 1.25rem;
    height: 1.25rem;
}
[data-shape-switch] [data-shape-control]:checked + span { translate: 1.25rem 0; }
```

The knob transition sits behind `motion-reduce:`, so anything written here
should hold still for the same readers.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the switch |
| `description` | — | a second line under the label |
| `value` | — | the submitted value |
| `tone` | `brand` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `id` | the resolved name | the element id |

`checked`, `disabled` and `wire:model` pass through to the `<input>`.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
