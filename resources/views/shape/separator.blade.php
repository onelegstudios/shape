@blaze(fold: true, memo: true)

{{--
    A rule, optionally with a label sitting in a gap in it.

    Always self-closing and slotless, which is what makes it memoizable: Blaze
    only memoizes self-closing calls, and any slot at all rules memo out.

    `label` picks between two quite different pieces of markup, so it is not
    declared safe. The unlabelled form is decorative and hidden from assistive
    technology; the labelled form is a real separator with an accessible name.
--}}

@props([
    'orientation' => 'horizontal',
    'label' => null,
])

@php
// The bare form's rule is the caller's to recolour, so it carries the
// zero-specificity prefix. The labelled form's two rules are interior elements
// that no caller class can reach, so they don't need it.
$rule = 'bg-shape-200 dark:bg-shape-800';
$ruleRoot = '[:where(&)]:bg-shape-200 dark:[:where(&)]:bg-shape-800';
@endphp

@if (filled($label))
    <div
        {{ $attributes->class('flex items-center [:where(&)]:gap-3') }}
        data-shape-separator
        data-shape-orientation="horizontal"
        role="separator"
        aria-orientation="horizontal"
    >
        <span class="h-px flex-1 {{ $rule }}" aria-hidden="true"></span>
        <span class="text-xs font-medium text-[color:var(--shape-fg-muted)]">{{ $label }}</span>
        <span class="h-px flex-1 {{ $rule }}" aria-hidden="true"></span>
    </div>
@else
    {{-- A bare rule carries no information a screen reader needs; the spacing
         and the heading around it already say what it says. --}}
    <div
        {{ $attributes->class(Shape::classes($ruleRoot)->add($orientation === 'vertical' ? 'w-px self-stretch' : 'h-px w-full')) }}
        data-shape-separator
        data-shape-orientation="{{ $orientation }}"
        role="separator"
        aria-orientation="{{ $orientation }}"
        aria-hidden="true"
    ></div>
@endif
