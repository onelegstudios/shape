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
        'lg' => '[:where(&)]:size-8',
        'xl' => '[:where(&)]:size-10',
        default => '[:where(&)]:size-6',
    });
@endphp

<?php switch ($variant.':'.$size): case ('solid:xs'): ?>
<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
    <path d="M3.75 7.25a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5h-8.5Z"/>
</svg>
<?php break; case ('solid:sm'): ?>
<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
    <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z"/>
</svg>
<?php break; case ('solid:base'): case ('solid:lg'): case ('solid:xl'): ?>
<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
    <path fill-rule="evenodd" d="M3.75 12a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>
</svg>
<?php break; default: ?>
<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
</svg>
<?php endswitch; ?>
