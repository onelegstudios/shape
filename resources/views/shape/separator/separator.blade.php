@blaze(fold: true, memo: true)

{{--
    A rule, optionally with a label sitting in a gap in it.

    Always self-closing and slotless, which is what makes it memoizable: Blaze
    only memoizes self-closing calls, and any slot at all rules memo out.

    `label` picks between two quite different pieces of markup, so it is not
    declared safe. The unlabelled form is decorative and hidden from assistive
    technology; the labelled form is a real separator with an accessible name.

    `size` moves the rule's weight, the label's type and the gap the label sits
    in. The weight is the half of it that has a floor: a hairline is one device
    pixel and there is nothing under it, so the three small steps all draw one
    and it is the two large ones that thicken. That is not a scale with dead
    steps in it — `xs` and `sm` are still doing the other half of the job — but
    it is worth knowing before reaching for `size="xs"` on a bare rule and
    watching nothing happen.
--}}

@props([
    'orientation' => 'horizontal',
    'label' => null,
    'size' => 'base',
])

@php
// The rule's weight along each axis, the gap the label sits in, and the type it
// is set in. Both axes are named because Tailwind reads these class names out of
// this file as text, so a composed `'h-'.$weight` would generate nothing.
[$across, $down, $gap, $type] = match ($size) {
    'xs' => ['h-px', 'w-px', '[:where(&)]:gap-2', 'text-2xs'],
    'sm' => ['h-px', 'w-px', '[:where(&)]:gap-2.5', 'text-xs'],
    'lg' => ['h-0.5', 'w-0.5', '[:where(&)]:gap-4', 'text-sm'],
    'xl' => ['h-1', 'w-1', '[:where(&)]:gap-5', 'text-base'],
    default => ['h-px', 'w-px', '[:where(&)]:gap-3', 'text-xs'],
};

// The bare form's rule is the caller's to recolour, so it carries the
// zero-specificity prefix. The labelled form's two rules are interior elements
// that no caller class can reach, so they don't need it.
$rule = 'bg-shape-200 dark:bg-shape-800';
$ruleRoot = '[:where(&)]:bg-shape-200 dark:[:where(&)]:bg-shape-800';
@endphp

@if (filled($label))
    <div
        {{ $attributes->class(Shape::classes('flex items-center')->add($gap)) }}
        data-shape-separator
        data-shape-orientation="horizontal"
        data-shape-size="{{ $size }}"
        role="separator"
        aria-orientation="horizontal"
    >
        <span class="{{ $across }} flex-1 {{ $rule }}" aria-hidden="true"></span>
        <span class="{{ $type }} font-medium text-[color:var(--shape-fg-muted)]">{{ $label }}</span>
        <span class="{{ $across }} flex-1 {{ $rule }}" aria-hidden="true"></span>
    </div>
@else
    {{-- A bare rule carries no information a screen reader needs; the spacing
         and the heading around it already say what it says. --}}
    <div
        {{ $attributes->class(Shape::classes($ruleRoot)->add($orientation === 'vertical' ? $down.' self-stretch' : $across.' w-full')) }}
        data-shape-separator
        data-shape-orientation="{{ $orientation }}"
        data-shape-size="{{ $size }}"
        role="separator"
        aria-orientation="{{ $orientation }}"
        aria-hidden="true"
    ></div>
@endif
