# Shape

Shape the interface. Predictable Blade UI components for Livewire applications.
Components are anonymous Blade files, styled with Tailwind, and annotated so that
[Blaze](https://github.com/livewire/blaze) can fold them into their parent templates
at compile time.

## Installation

```bash
composer require onelegstudios/shape
```

Import the design tokens after Tailwind in your application stylesheet:

```css
@import "tailwindcss";
@import "../../vendor/onelegstudios/shape/resources/css/shape.css";
```

The token file declares `@source "../views"`, so your Tailwind build scans the
package's components without any further configuration.

## Performance

Shape is designed to be folded. Install Blaze and the components are pre-rendered
into their parent templates during compilation:

```bash
composer require livewire/blaze
```

Nothing else is required — Shape registers its own views with Blaze, and every
component already declares the strategy it is safe to use. Without Blaze
installed the components render normally; the `@blaze` annotations compile away.

Folding is not free of obligations at the call site. See
[Folding](folding.md) for the rules that matter.

## Components

| Component                                | Tier        |                                                                                |
| ---------------------------------------- | ----------- | ------------------------------------------------------------------------------ |
| [`button`](components/button.md)         | fold        | An action, with `variant` for hierarchy and `tone` for meaning                 |
| [`icon`](components/icon.md)             | fold + memo | One component per icon, generated from your set, at three sizes and two styles |
| [`heading`](components/heading.md)       | fold        | `level` picks the element, `size` picks the type                               |
| [`text`](components/text.md)             | fold        | A paragraph, muted or strong                                                   |
| [`card`](components/card.md)             | fold        | A surface, with a header and footer if you want them                           |
| [`separator`](components/separator.md)   | fold + memo | A rule, with an optional label in it                                           |
| [`badge`](components/badge.md)           | fold + memo | A piece of state attached to something else                                    |
| [`empty`](components/empty.md)           | fold        | The screen before there is any data                                            |
| [`field`](components/field.md)           | fold        | Label, description, control and error, assembled                               |
| [`input`](components/input.md)           | fold        | A text input; give it a `label` and it writes the field                        |
| [`textarea`](components/textarea.md)     | fold        | The input's chrome, resizing vertically only                                   |
| [`select`](components/select.md)         | fold        | A native select, restyled; options are children                                |
| [`checkbox`](components/checkbox.md)     | fold        | A checkbox inside its own label                                                |
| [`radio`](components/radio.md)           | fold        | Grouped by a real fieldset                                                     |
| [`switch`](components/switch.md)         | fold        | A setting that applies the moment it moves                                     |
| [`modal`](components/modal.md)           | fold        | A `<dialog>`, so the focus trap is the platform's                              |
| [`drawer`](components/drawer.md)         | fold        | The modal, pinned to an edge                                                   |
| [`dropdown`](components/dropdown.md)     | fold        | A menu of actions, anchored to its trigger                                     |
| [`popover`](components/popover.md)       | fold        | Anchored content in the top layer                                              |
| [`tooltip`](components/tooltip.md)       | fold        | A label for an icon button                                                     |
| [`alert`](components/alert.md)           | fold        | A message that stays on the page                                               |
| [`toast`](components/toast.md)           | fold        | A message about something that just happened                                   |
| [`toaster`](components/toast.md)         | compile     | Where toasts appear; one per layout                                            |
| [`confirm`](components/confirm.md)       | fold        | One dialog for every confirmation in the app                                   |
| [`progress`](components/progress.md)     | fold        | A native `<progress>`, restyled                                                |
| [`table`](components/table.md)           | fold        | Rows and columns, with the empty state built in                                |
| [`list`](components/list.md)             | fold        | The table's answer without the columns                                         |
| [`pagination`](components/pagination.md) | compile     | Hand it the paginator you already have                                         |
| [`stat`](components/stat.md)             | fold + memo | A number, its label and its trend                                              |
| [`avatar`](components/avatar.md)         | fold + memo | A person, as a picture or as initials                                          |
| [`tabs`](components/tabs.md)             | fold        | Links when they're links, tabs when they're not                                |

Every component page opens with a rendered example of each prop that changes
what you see, and closes with a Theming section, a prop reference and the
component's Blaze tier. The Theming section is what that component paints from
and how to move it — the classes it yields to, the ones it only ties with, and
the rule that reaches every instance at once.

The guides cover what several components share:

| Guide                   |                                                                                                                                |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| [Theming](theming.md)   | Changing the colours step by step, the tokens, the one-colour seed, the surface contract, tones, a second accent and dark mode |
| [Forms](forms.md)       | The shorthand, groups, name resolution and validation messages                                                                 |
| [Overlays](overlays.md) | What the five overlays share: the script, the naming convention, what the platform supplies                                    |
| [Feedback](feedback.md) | Server to browser, and why that needs no Livewire component                                                                    |
| [Data display](data.md) | What the table, the list and the pager share, and what a component costs per row                                               |
| [Tooling](tooling.md)   | The nine commands and the manifests they read                                                                                  |
| [Folding](folding.md)   | What Blaze does, and the call sites that keep a component on the fold path                                                     |

## JavaScript

One file, for the overlays, the feedback channel, and the keyboard behaviour of
a tab strip:

```js
import shape from "../../vendor/onelegstudios/shape/resources/js/shape.js";

shape(); // or, if you use Alpine: Alpine.plugin(shape)
```

It imports nothing and depends on nothing — not Alpine, and not Livewire.
Everything else in the library — including switches, checkboxes and the select —
is markup and CSS.

Overlays render and open without it, with three small exceptions listed in
[Overlays](overlays.md#the-script). [Tabs](components/tabs.md) are the one
component outside the overlays that uses it, and only in their panel-switching
mode: without the script every panel is visible and every tab is focusable, and
a strip of navigation links needs no script at all. Toasts are the one thing
that does not work without it, and
[Feedback](feedback.md#toasts-need-the-script) says why that trade was taken.

## Customising

Customisation escalates in three steps:

1. **Tokens.** Redeclare Shape's `@theme` values in your own stylesheet, or
   derive the whole palette from a single colour. [Theming](theming.md) covers
   the token layer, the surface contract, the tones and dark mode.
2. **Utilities.** Pass any Tailwind class to any component; Shape's own defaults
   carry zero specificity and yield to it.
3. **Eject.** Copy a component into your application and own it outright:

```bash
php artisan shape:eject modal
```

A modal composes a heading, a text and a close button, so all four arrive
together — ejecting the shell without the parts you wanted to change is the
worst of both arrangements. `shape:eject:all` and
`vendor:publish --tag="shape-components"` each take the whole library at
once.

Ejected components resolve ahead of the packaged ones. The location is
configurable with `shape.components_path`.

## Tooling

Nine commands in four families, none of them required: `shape:install` writes
the import and the script registration for you, `shape:eject` hands you a
component and everything it composes, `shape:doctor` checks an ejected component
for the global state that would cost it its fold, and `shape:icon` fetches an
icon set and generates components from it. Ejecting and generating each have
siblings for the whole of something — `shape:eject:all`, `shape:icon:all`,
`shape:icon:replace` — and a `:status` apiece for what has moved since. See
[Tooling](tooling.md), which also covers the documentation site you are probably
reading this on.
