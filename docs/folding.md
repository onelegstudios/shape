# Folding

Blaze pre-renders a component during Blade compilation and embeds the resulting
HTML directly in the parent template. A folded component costs essentially
nothing to render, no matter how many times it appears on the page.

Shape annotates every component with the strategy it can safely use. What Shape
cannot decide on your behalf is how you call them.

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

## Icons

`<x-shape::icon.check />` folds and memoizes. `<x-shape::icon name="check" />`
resolves the component at runtime and cannot fold — reach for the direct form in
loops and tables.
