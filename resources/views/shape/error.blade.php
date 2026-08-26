@blaze(fold: true)

{{--
    The one component in the forms set that touches request state.

    Validation messages are request-scoped, and a folded component is
    pre-rendered once at compile time — so baking them in would serve one
    visitor's validation failures to everybody. The `unblaze` block below cuts
    that region out of the fold and leaves it to run per request. Everything
    around it still folds.

    Three things about that block are easy to get wrong:

    1. Variables do not cross its boundary. Anything the block needs has to be
       listed in `scope` and read back off `$scope`. This is the most common
       mistake when retrofitting fold onto an existing component.

    2. `$attributes` does not cross it either — and it fails asymmetrically,
       which is worse than failing outright. Outside a fold the block compiles
       inline and the bag resolves; inside one it is extracted and compiled on
       its own, where it does not. So the class string is built out here and
       handed in through the scope, and the block references nothing else.

    3. The scope array is written into the compiled template with `var_export`,
       so its values have to be plain scalars — and `name` has to be known when
       the template compiles. It is a declared prop, so Blaze aborts the fold on
       its own when it is bound dynamically. That is correct rather than
       unfortunate: a dynamic name has no compile-time value to bake.

    One more, about this comment rather than the code: Blaze validates the whole
    file for request-scoped patterns after stripping the block, and the strip is
    a non-greedy match on the directive names. Naming them in prose up here would
    move where that strip starts and fail the component for a sentence. Hence the
    circumlocution.

    The prop is `name` rather than `for`, unlike the label and the description
    beside it. Those two point at an element; this one names a validation key,
    which is a different thing that happens to share a value. That the key can be
    a prop at all is a consequence of the field's context travelling as
    `field-name` — with both called `name`, one would quietly overwrite the other.

    No outer margin. The field owns the space between its children.
--}}

@props([
    'name' => null,
    'bag' => 'default',
])

@aware([
    'fieldName' => null,
])

@php
$target = $name ?? $fieldName;

// Interpolated into the scope, never branched on, so reading the bag here costs
// nothing. Other attributes are deliberately dropped: an error message is not a
// thing callers decorate, and pretending otherwise would mean smuggling the
// whole bag across a boundary it cannot cross intact.
$classes = (string) Shape::classes('text-sm font-medium text-[color:var(--shape-tone-ink)]')
    ->add((string) $attributes->get('class'));
@endphp

@unblaze(scope: ['name' => $target, 'bag' => $bag, 'class' => $classes])
    @php
        // The session middleware shares the error bag, so it is there on every
        // web request and absent everywhere else — a mailable, a queued render,
        // `Blade::render()` in a test. Reading it defensively is the difference
        // between "this field has no message" and a fatal in those contexts.
        //
        // Variables declared in here leak into the surrounding template, so the
        // name is prefixed rather than left as something a caller might own.
        $shapeErrorBag = ($errors ?? null)?->getBag($scope['bag']);

        // An empty key would make `has()` answer for *any* error in the bag,
        // which is how a nameless field ends up displaying a neighbour's message.
        $shapeErrorMessage = filled($scope['name']) && $shapeErrorBag?->has($scope['name'])
            ? $shapeErrorBag->first($scope['name'])
            : null;
    @endphp

    @if ($shapeErrorMessage !== null)
        {{-- `role="alert"` already implies an assertive live region; pairing it
             with `aria-live="polite"` asks for both at once, and browsers
             disagree about which one wins. --}}
        <p
            class="{{ $scope['class'] }}"
            data-shape-error
            data-shape-tone="danger"
            role="alert"
        >{{ $shapeErrorMessage }}</p>
    @endif
@endunblaze
