<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <flux:heading size="xl">Your training</flux:heading>
                <flux:text class="mt-2">Build a program, then log each workout as you lift.</flux:text>
            </div>
            <flux:button variant="primary" :href="route('programs.create')" wire:navigate>Create program</flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text size="sm">Programs</flux:text>
                <flux:heading size="xl" class="mt-2">Start with your plan</flux:heading>
                <flux:text class="mt-2">Keep multiple programs for different goals or phases.</flux:text>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text size="sm">Workout entries</flux:text>
                <flux:heading size="xl" class="mt-2">Your history stays yours</flux:heading>
                <flux:text class="mt-2">Every entry keeps the exercises and numbers recorded that day.</flux:text>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text size="sm">Units</flux:text>
                <flux:heading size="xl" class="mt-2">kg or lbs</flux:heading>
                <flux:text class="mt-2">Choose weight units independently for each exercise.</flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
