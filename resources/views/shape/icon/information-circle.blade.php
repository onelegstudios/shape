@blaze(fold: true, memo: true)

{{-- Heroicons (https://heroicons.com), MIT licensed. Regenerate; don't hand-edit. --}}

@props([
    'variant' => null,
    'size' => 'base',
])

@php
$variant ??= match ($size) {
    'xs', 'sm' => 'solid',
    default => 'outline',
};

$classes = Shape::classes('shrink-0')
    ->add(match ($size) {
        'xs' => '[:where(&)]:size-4',
        'sm' => '[:where(&)]:size-5',
        default => '[:where(&)]:size-6',
    });
@endphp

<?php switch ($variant.':'.$size): case ('solid:xs'): ?>
<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
    <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0ZM9 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.25 7a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 9.037 13h.213a.75.75 0 0 0 0-1.5h-.213a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 7.503 7H7.25Z" clip-rule="evenodd"/>
</svg>
<?php break; case ('solid:sm'): ?>
<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/>
</svg>
<?php break; case ('solid:base'): ?>
<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/>
</svg>
<?php break; default: ?>
<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
</svg>
<?php endswitch; ?>
