<flux:modal name="achievement-unlocked-preview" class="md:w-96">
    <div class="flex flex-col items-center text-center">
        <div class="flex size-28 items-center justify-center rounded-3xl bg-amber-400 text-3xl font-bold text-amber-950 shadow-lg shadow-amber-400/30" aria-hidden="true">
            5
        </div>

        <flux:badge color="amber" variant="solid" class="mt-6">Achievement unlocked</flux:badge>
        <flux:heading size="lg" class="mt-3">Five Strong</flux:heading>
        <flux:text class="mt-2">Complete 5 workout entries.</flux:text>
    </div>

    <x-slot name="footer" class="flex justify-center">
        <flux:modal.close>
            <flux:button variant="primary">Keep training</flux:button>
        </flux:modal.close>
    </x-slot>
</flux:modal>
