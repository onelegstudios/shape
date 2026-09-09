# Confirm

One dialog in your layout answers every confirmation in the application.

@docs('preview', name: 'confirm-live', layout: 'stack')

```blade
{{-- Once, in your layout. --}}
<x-shape::confirm />
```

```php
Shape::confirm('Every invoice attached to it goes too.')
    ->heading('Delete project?')
    ->accept('Delete')
    ->tone('danger')
    ->then('deleteProject', [$project->id])
    ->send();
```

Every prop on the component is a placeholder that the payload overwrites before
the dialog opens.

## `then()` names a window event

When someone accepts, `shape.js` dispatches an event by that name and closes the
dialog. It never mentions Livewire, and neither does anything else in the
script:

```php
#[On('deleteProject')]
public function deleteProject(int $id): void
{
    Project::findOrFail($id)->delete();

    Shape::toast()->success('Project deleted')->send();
}
```

`#[On]` listens for window events, so nothing is wired up. Alpine hears the same
event with `x-on:deleteProject.window`.

The event is dispatched **before** the dialog closes. Closing returns focus and
tears the dialog down, and none of that should decide whether the thing someone
confirmed actually happened.

## More than one

```blade
<x-shape::confirm name="confirm-billing" />
```

```php
Shape::confirm('…')->name('confirm-billing')->then('cancelPlan')->send();
```

`name` having a default is the exception to the rule stated on every other
overlay, and the rule is narrower than it looks: it is about *generated* ids.
`uniqid()` inside a folded component runs once, at compile time, and bakes one
value into every instance. A literal is already one value, so baking it is the
correct result rather than a bug.

## It is the modal

Which means the focus trap, the top layer, Escape, the scrim, the inertness
behind it and focus returning to whatever opened it are all the platform's. See
[Modal](modal.md).

Cancel is an ordinary `overlay.close`, so it uses the platform's `command`
attribute. Accept is deliberately not one — it has to dispatch first.

## Translating

Defaults are literal English rather than `__()`, because a folded component
resolves a translation once at compile time and serves that locale to everybody.
Send the strings with the payload instead, where they are evaluated per request
on the server:

```php
Shape::confirm(__('This cannot be undone.'))->accept(__('Delete'))->send();
```

## Theming

Confirm is [the modal](#it-is-the-modal), so the panel, the scrim and the
placement layer are the modal's — see
[Theming there](modal.md#theming). What is confirm's own is which button carries
the tone.

`tone()` on the builder is applied to the accept button when the dialog opens:
`shape.js` writes `data-shape-tone` onto `[data-shape-confirm-accept]`, and the
button repaints from the tone variables like any other. So a destructive
confirmation is red because the tone says so, and follows a
[retint](../theming.md) with every other danger control:

```php
Shape::confirm('This cannot be undone.')->accept('Delete')->tone('danger')->send();
```

The two buttons are otherwise ordinary [buttons](button.md#theming) — accept is
`primary`, cancel is an `overlay.close` — and a rule can reach either through
the attributes they carry:

```css
[data-shape-confirm] [data-shape-confirm-accept] { min-width: 8rem; }
[data-shape-confirm] [data-shape-overlay-footer] {
    justify-content: space-between;
}
```

Because there is one of these in the layout answering every confirmation in the
application, a rule here is the whole of the treatment rather than a class
repeated at call sites — there are no call sites to repeat it at.

## Reference

| Prop | Default |
| --- | --- |
| `name` | `shape-confirm` |
| `heading` | `Are you sure?` |
| `message` | — |
| `accept` | `Confirm` |
| `cancel` | `Cancel` |

The builder takes `heading()`, `message()`, `accept()`, `cancel()`, `tone()`,
`name()`, `then()` and `send()`.

## Folding

Tier A — `@blaze(fold: true, safe: ['name', 'heading', 'message', 'accept'])`.

`cancel` is not safe, and the reason is the composition case worth knowing.
Safety is a claim about what *this* component does with a value. Confirm only
interpolates `cancel` — but it hands it to `overlay.close`, which branches on
`label` to choose between an icon button and a text one. A dynamic `:cancel`
therefore has to reach a runtime decision.

See [Folding](../folding.md) and [Feedback](../feedback.md).
