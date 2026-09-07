<div class="flex max-w-md flex-col gap-4">
    <x-shape::alert tone="success" heading="Saved" :icon="false">
        <x-slot:actions>
            <x-shape::button size="sm" variant="ghost" tone="success">Undo</x-shape::button>
        </x-slot:actions>
    </x-shape::alert>

    <x-shape::alert tone="success" heading="Saved" actions-placement="sm" :icon="false">
        <x-slot:actions>
            <x-shape::button size="sm" variant="ghost" tone="success">Undo</x-shape::button>
        </x-slot:actions>
    </x-shape::alert>
</div>
