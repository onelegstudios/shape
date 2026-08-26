# Elevation

Shape uses Tailwind's shadow scale directly rather than defining one of its own.
Tailwind's shadows are already two-part — a soft cast plus a tight contact edge —
and already step up the way an elevation system should. Duplicating them under
Shape's own names would have bought a hue tint that measures under two values out
of 255 at the opacities these shadows use.

The system is therefore a convention, not a token set: **which** shadow a
component reaches for, and the discipline of never reaching outside this list.

| Elevation | Class | Used by |
| --- | --- | --- |
| Resting | `shadow-xs` | Table rows, list items that need only the faintest lift |
| Raised | `shadow-sm` | Buttons, cards, inputs — anything sitting on the page |
| Floating | `shadow-md` | Popovers, tooltips |
| Overlay | `shadow-lg` | Dropdowns, menus, toasts |
| Modal | `shadow-xl` | Dialogs, drawers, command palette |

`shadow-2xs` and `shadow-2xl` are deliberately unused. Five options is plenty,
and leaving two on the shelf costs nothing.

## Overriding

Elevation is applied at zero specificity, so a caller can change or remove it:

```blade
<x-shape::button class="shadow-none">Flat</x-shape::button>
<x-shape::button class="shadow-lg">Lifted</x-shape::button>
```

Retheming `--shadow-sm` in your own stylesheet restyles Shape along with the rest
of your interface, which is the point of not owning the scale.

## Dark mode

Black at 10% opacity does almost nothing against a dark surface, so elevation in
dark mode reads through surface colour and a ring rather than a cast shadow.

This has to be done with a `dark:` variant on the component. Tailwind resolves
shadow values at build time rather than referencing the custom property, so
redefining `--shadow-sm` under `prefers-color-scheme: dark` has no effect on the
`shadow-sm` utility.
