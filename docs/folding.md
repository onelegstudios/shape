# Folding

Blaze pre-renders a component during Blade compilation and embeds the resulting
HTML directly in the parent template. A folded component costs essentially
nothing to render, no matter how many times it appears on the page.

Shape annotates every component with the strategy it can safely use. What Shape
cannot decide on your behalf is how you call them.

Every component in the library folds, overlays included. Nothing here inspects a
slot, and open state belongs to `<dialog>` and the `popover` attribute rather
than to a variable a template has to read — so there is no runtime question left
for a modal or a menu to ask.

## The rule

**A prop that drives a `match` inside a component has to be static at the call
site for that component to fold.**

```blade
{{-- Folds. --}}
<x-shape::button variant="primary">Save</x-shape::button>

{{-- Does not fold — `variant` selects a match arm, so its value has to be
     known at compile time. Renders correctly, just through the compiled path. --}}
<x-shape::button :variant="$isPrimary ? 'primary' : 'outline'">Save</x-shape::button>
```

This is not a failure mode. Blaze detects the dynamic binding and falls back to
its function compiler, which still removes most of Blade's rendering overhead.
It is worth knowing about, because it means fold coverage is a property of your
templates and not only of this package.

## Props that stay dynamic

Some props never drive a branch — they are interpolated and nothing more. Those
are declared `safe`, and they fold even when bound dynamically:

```blade
{{-- Folds. `color` only ever reaches `data-shape-tone`. --}}
<x-shape::button variant="primary" :color="$destructive ? 'danger' : null">
    Delete
</x-shape::button>
```

This is why `variant` and `color` are separate props on the button rather than
one combined appearance prop: hierarchy has to branch, semantics doesn't.

`safe` is declared per component, not per prop name. The badge branches on
`color` to resolve its state icon, so `color` is *not* safe there — the same
prop name, a different answer, for a reason the component's own page explains.
Check the page rather than assuming.

## Fold and memo are alternatives, not a stack

A component annotated `@blaze(fold: true, memo: true)` does not do both at once.
Folding wins where it applies: the component is inlined into the parent and
there is nothing left to cache. Memoization is the safety net underneath — it
catches the call sites where folding gave up, caching the rendered output per
prop set.

```blade
{{-- Folds. The span and its SVG are inlined into the parent template. --}}
<x-shape::badge label="Paid" color="success" />

{{-- Doesn't fold. Memoizes instead: a table of two hundred rows with five
     distinct states renders five badges and reuses them. --}}
@foreach ($invoices as $invoice)
    <x-shape::badge :label="$invoice->state" :color="$invoice->tone" />
@endforeach
```

Memo is decided per **call site**, not per file: Blaze memoizes a component
written self-closing, whatever else the file can do. That is why `badge` takes
`label` as a prop rather than as children — the slot would cost it the safety net
on exactly the call sites that need one — and why `table.cell` can take a `value`
prop *and* a slot, memoizing on the self-closing form a table is mostly made of.

The net is not always worth much. `avatar` is annotated `memo: true` and does
fold on its initials, but an avatar list built from per-row URLs produces one
cache entry per URL and no hits — the same shape as the badge example above,
without the five distinct states that made it pay.
[Avatar](components/avatar.md) says so on its own page rather than leaving the
annotation to imply otherwise.

## Icons

`<x-shape::icon.check />` folds and memoizes. `<x-shape::icon name="check" />`
resolves the component at runtime and cannot fold — reach for the direct form in
loops and tables.

Icons nested inside a component that folds are baked in with it, so
`<x-shape::button icon="check">` and `<x-shape::badge color="success">` both
end up as literal SVG in the compiled template.

## Inherited props are unsafe

A component that reads a value from its parent with `@aware` folds only while the
parent passed that value statically. Blaze treats every `@aware` prop as unsafe,
and the check looks at the *parent's* attribute:

```blade
{{-- Folds. Label, description, control and error are all inlined. --}}
<x-shape::field field-name="email"> … </x-shape::field>

{{-- Does not fold — and not just the one component that reads the name.
     Every child of this field drops to the compiled path. --}}
<x-shape::field :field-name="$field->name"> … </x-shape::field>
```

That is the cost of stating a field's name once instead of four times. Field
names are literals in almost every real form, so it is rarely the case you are
in; when you are, you lose a fold, not correctness.

## Translations bake too

`__()` is not on Blaze's list of things a folded component must not touch, and it
belongs there. A folded component is pre-rendered once, at compile time, so a
translation inside one resolves once — and every visitor afterwards is served
whichever locale happened to compile the view:

```blade
{{-- Compiles to the literal string. Switching locale does nothing. --}}
@blaze(fold: true)
<span>{{ __('Close') }}</span>
```

No Shape component calls a translation helper, and a test fails the build if one
starts. Translate at the call site instead, where the value is still resolved per
request and reaches the component as an attribute:

```blade
<x-shape::overlay.close for="terms" :label="__('Cancel')" />
```

The same reasoning covers anything else resolved once and used everywhere: a
formatted date, a currency symbol taken from config, a URL built from the current
route.

## Two components are compiled and not folded

`toaster` and `pagination` carry a plain `@blaze` — compiled, which removes most
of Blade's overhead, and nothing more. The rule they share is short: **a
component that loops data the server produced has nothing to bake.** A folded
pager would hold one visitor's page of links in the compiled template forever.

It is worth stating because the reflex — annotate everything `fold: true` and let
Blaze abort where it must — gives you a component that folds successfully and is
wrong. Blaze aborts on a prop it can't resolve at compile time; it has no opinion
about a collection you hand it.

## Cutting a hole for request state

Validation messages, the authenticated user, the CSRF token and the current URL
all change per request. Folding bakes markup in at compile time, so a component
that reads any of them cannot fold — unless it isolates the part that does.

Shape's `error` component is the worked example, and the pattern generalises:

```blade
@blaze(fold: true)

@unblaze(scope: ['name' => $target, 'bag' => $bag, 'class' => $classes])
    {{-- runs per render; everything outside this block is still inlined --}}
@endunblaze
```

Three rules come with it:

- **Nothing crosses the boundary implicitly.** Variables have to be listed in
  `scope` and read back off `$scope`. This is the most common mistake when
  retrofitting fold onto an existing component.
- **`$attributes` cannot cross it at all**, and it fails asymmetrically: outside
  a fold the block compiles inline and the bag resolves, inside one it is
  extracted and compiled on its own, where it does not. Build what the block
  needs outside it and hand it in through `scope`.
- **`scope` is written out with `var_export`**, so its values must be plain
  scalars and must be known at compile time.

One more thing worth knowing, because it costs an afternoon otherwise: Blaze
validates the whole component file for request-scoped patterns *after* stripping
the block, and the strip is a non-greedy match on the directive names. Naming
those directives in a doc comment above the code moves where the strip begins,
and the component fails to fold for a sentence.
