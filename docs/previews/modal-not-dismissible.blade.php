<x-shape::overlay.trigger for="terms">Accept the terms</x-shape::overlay.trigger>

<x-shape::modal name="terms" heading="Accept the terms" :dismissible="false">
    <x-shape::text size="sm">There is no close button, and Escape is held off.</x-shape::text>

    <x-shape::overlay.footer>
        <x-shape::overlay.close for="terms" label="Decline" />
        <x-shape::button variant="primary" command="close" commandfor="terms">Accept</x-shape::button>
    </x-shape::overlay.footer>
</x-shape::modal>
