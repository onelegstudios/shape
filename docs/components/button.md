# Button

```blade
<x-shape::button variant="primary">Save changes</x-shape::button>
```

## Hierarchy and semantics are separate props

`variant` says where an action sits in the pyramid of importance. `color` says
what it means. Keeping them apart is what lets a destructive action be quiet:

```blade
<x-shape::button variant="subtle" color="danger" icon="trash">Delete</x-shape::button>
```

Most pages have one true primary action. Reach for `variant="primary"` once.

| Prop | Default | Values |
| --- | --- | --- |
| `variant` | `outline` | `primary`, `outline`, `subtle`, `ghost` |
| `color` | `neutral` | `neutral`, `accent`, `danger`, `success` |
| `size` | `base` | `sm`, `base`, `lg` |
| `icon` | — | any icon name, rendered before the label |
| `icon-trailing` | — | any icon name, rendered after the label |
| `icon-variant` | `mini` | `micro`, `mini`, `solid`, `outline` |
| `square` | `false` | drops the horizontal padding for icon-only buttons |
| `as` | `button` | `button`, `a`, `div` |
| `type` | `button` | any button type |

## Links

```blade
<x-shape::button as="a" href="/settings" icon-trailing="arrow-right">Settings</x-shape::button>
```

`href` is passed straight through the attribute bag rather than declared as a
prop, so `:href="$url"` does not stop the button folding.

## Icon-only buttons

`square` removes the horizontal padding. Give the button an accessible name,
because there is no label to read:

```blade
<x-shape::button square icon="trash" color="danger" aria-label="Delete project" />
```

## Overriding styles

Every default Shape sets carries zero specificity, so your classes win without
`!important`:

```blade
<x-shape::button class="rounded-full w-full">Continue</x-shape::button>
```

## Livewire and Alpine

Anything Shape doesn't claim as a prop lands on the rendered element:

```blade
<x-shape::button wire:click="save" wire:loading.attr="disabled">Save</x-shape::button>
```
