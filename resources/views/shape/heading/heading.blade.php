@blaze(fold: true, safe: ['level'])

{{--
    Document hierarchy and visual hierarchy are separate props.

    `level` picks the element, `size` picks the type. Nothing derives one from
    the other, because the whole reason for the pair is that they disagree: a
    page's `<h1>` is often not its largest text, and a large number in a stat
    block is often not a heading at all.

    `level` is only ever interpolated into the tag name — never branched on —
    so it is declared safe and `:level="$depth"` still folds.
--}}

@props([
    'level' => 2,
    'size' => 'base',
])

@php
$classes = Shape::classes()
    ->add('[:where(&)]:font-semibold [:where(&)]:text-balance')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')

    // Size, leading and tracking are set together and never separately. Type
    // set large needs its line-height and letter-spacing pulled in; the same
    // treatment applied at 14px would close the text up until it was unreadable.
    //
    // The library's five steps, and the same five the text component sets body
    // copy at, so a heading and a paragraph given the same word are the same
    // type size. Above `xl` there is no step, because a page title larger than
    // the scale is a decision about that page rather than about headings:
    // `class="text-3xl"` is one class, and it wins on its own.
    ->add(match ($size) {
        'xs' => '[:where(&)]:text-xs [:where(&)]:leading-5 [:where(&)]:tracking-normal',
        'sm' => '[:where(&)]:text-sm [:where(&)]:leading-6 [:where(&)]:tracking-normal',
        'lg' => '[:where(&)]:text-lg [:where(&)]:leading-7 [:where(&)]:tracking-tight',
        'xl' => '[:where(&)]:text-xl [:where(&)]:leading-7 [:where(&)]:tracking-tight',
        default => '[:where(&)]:text-base [:where(&)]:leading-6 [:where(&)]:tracking-normal',
    });
@endphp

<h{{ $level }} {{ $attributes->class($classes) }} data-shape-heading data-shape-size="{{ $size }}">{{ $slot }}</h{{ $level }}>
