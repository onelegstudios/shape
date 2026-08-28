# Confirm

```blade
{{-- Once, in your layout. --}}
<x-shape::confirm />
```

```php
Shape::confirm('Every invoice attached to it goes too.')
    ->heading('Delete project?')
    ->accept('Delete')
    ->color('danger')
    ->then('deleteProject', [$project->id])
    ->send();
```

One dialog in the layout answers every confirmation in the application. Every
prop on the component is a placeholder that the payload overwrites before it
opens.

| Prop | Default |
| --- | --- |
| `name` | `shape-confirm` |
| `heading` | `Are you sure?` |
| `message` | — |
| `accept` | `Confirm` |
| `cancel` | `Cancel` |

## `then()` names a window event

When someone accepts, `shape.js` dispatches an event by that name and closes the
dialog. It never mentions Livewire, and neither does anything else in the script:

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

## It is the modal

Which means the focus trap, the top layer, Escape, the scrim, the inertness
behind it and focus returning to whatever opened it are all the platform's. See
[Modal](modal.md).

Cancel is an ordinary `overlay.close`, so it uses the platform's `command`
attribute. Accept is deliberately not one — it has to dispatch first.

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

## Translating

Defaults are literal English rather than `__()`, because a folded component
resolves a translation once at compile time and serves that locale to everybody.
Translate at the call site — or send the strings with the payload, where they are
evaluated per request on the server:

```php
Shape::confirm(__('This cannot be undone.'))->accept(__('Delete'))->send();
```

## Folding

Tier A — `@blaze(fold: true, safe: ['name', 'heading', 'message', 'accept'])`.

`cancel` is not safe, and the reason is the composition case worth knowing.
Safety is a claim about what *this* component does with a value. Confirm only
interpolates `cancel` — but it hands it to `overlay.close`, which branches on
`label` to choose between an icon button and a text one. A dynamic `:cancel`
therefore has to reach a runtime decision, and claiming otherwise here would fold
a branch that has not been taken yet.
