<x-shape::alert tone="danger" variant="solid" heading="Card declined" dismissible>
    {{-- The close control keeps its colour, and so does this line. Neither was
         told what variant it is inside. --}}
    <x-shape::text size="sm" variant="muted">Update the card on file.</x-shape::text>
</x-shape::alert>
