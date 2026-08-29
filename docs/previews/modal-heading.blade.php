<x-shape::overlay.trigger for="both">Heading and description</x-shape::overlay.trigger>
<x-shape::overlay.trigger for="heading-only">Heading only</x-shape::overlay.trigger>
<x-shape::overlay.trigger for="bare">Neither</x-shape::overlay.trigger>

<x-shape::modal name="both" heading="Delete project" description="This cannot be undone.">
    <x-shape::text size="sm">Every invoice attached to it goes too.</x-shape::text>
</x-shape::modal>

<x-shape::modal name="heading-only" heading="Delete project">
    <x-shape::text size="sm">Every invoice attached to it goes too.</x-shape::text>
</x-shape::modal>

<x-shape::modal name="bare" aria-label="Delete project">
    <x-shape::text size="sm">A bare panel, for content that brings its own header.</x-shape::text>
</x-shape::modal>
