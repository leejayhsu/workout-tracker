<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Create program')] class extends Component {
    public string $name = '';

    /** @var array<int, string> */
    public array $workouts = ['', '', '', '', '', '', ''];

    public function createProgram(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'workouts' => ['required', 'array', 'max:7'],
            'workouts.*' => ['nullable', 'string', 'max:255'],
        ]);

        $workouts = array_values(array_filter($validated['workouts'], static fn (?string $name): bool => filled($name)));

        if ($workouts === []) {
            $this->addError('workouts', 'Add at least one workout to your program.');

            return;
        }

        DB::transaction(function () use ($validated, $workouts): void {
            $program = Auth::user()->programs()->create(['name' => $validated['name']]);
            $labels = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

            foreach ($workouts as $position => $workoutName) {
                $program->workouts()->create([
                    'label' => $labels[$position],
                    'name' => $workoutName,
                    'position' => $position,
                ]);
            }
        });

        $this->redirect(route('programs.index', absolute: false), navigate: true);
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-8">
        <div>
            <flux:heading size="xl">Create a program</flux:heading>
            <flux:text class="mt-2">Set up one to seven repeating workouts. The sequence is a reminder, not a schedule.</flux:text>
        </div>

        <form wire:submit="createProgram" class="flex flex-col gap-6">
            <flux:input wire:model="name" label="Program name" placeholder="StrongLifts 5x5" required />

            <div class="flex flex-col gap-4">
                <div>
                    <flux:heading size="lg">Workouts</flux:heading>
                    <flux:text class="mt-1">Name each workout in the order you intend to use them.</flux:text>
                </div>
                @foreach ($workouts as $position => $workout)
                    <flux:input wire:model="workouts.{{ $position }}" label="Workout {{ chr(65 + $position) }}" placeholder="{{ $position === 0 ? 'Squat, bench press, row' : 'Optional workout name' }}" />
                @endforeach
            </div>

            @error('workouts')
                <flux:callout variant="danger">{{ $message }}</flux:callout>
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('programs.index')" wire:navigate>Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create program</flux:button>
            </div>
        </form>
    </div>
</section>
