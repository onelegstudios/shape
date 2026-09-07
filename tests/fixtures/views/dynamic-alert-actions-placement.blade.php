<x-shape::alert tone="info" :actions-placement="$actionsPlacement">
    Something happened.

    <x-slot:actions>
        <x-shape::button size="sm">Undo</x-shape::button>
    </x-slot:actions>
</x-shape::alert>
