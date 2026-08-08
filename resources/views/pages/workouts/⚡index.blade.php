<?php

use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Workouts')] class extends Component {
    public Program $program;

    public bool $editingProgram = false;

    public string $programName = '';

    public ?int $editingWorkoutId = null;

    public string $workoutName = '';

    #[Url]
    public ?int $workoutId = null;

    public function mount(Program $program): void
    {
        abort_unless($program->user_id === Auth::id(), 404);

        $this->program = $program->load('workouts.entries');
        $this->programName = $program->name;
        $this->workoutId ??= $program->workouts()->value('id');
    }

    public function editProgram(): void
    {
        $this->programName = $this->program->name;
        $this->editingProgram = true;
    }

    public function saveProgram(): void
    {
        $validated = $this->validate([
            'programName' => ['required', 'string', 'max:255'],
        ]);

        $this->program->update(['name' => $validated['programName']]);
        $this->program->refresh()->load('workouts.entries');
        $this->editingProgram = false;
    }

    public function editWorkout(int $workoutId): void
    {
        $workout = $this->program->workouts()->findOrFail($workoutId);

        $this->editingWorkoutId = $workout->id;
        $this->workoutName = $workout->name ?? '';
    }

    public function saveWorkout(): void
    {
        $validated = $this->validate([
            'workoutName' => ['nullable', 'string', 'max:255'],
        ]);

        $workout = $this->program->workouts()->findOrFail($this->editingWorkoutId);
        $workout->update(['name' => filled($validated['workoutName']) ? $validated['workoutName'] : null]);
        $this->program->refresh()->load('workouts.entries');
        $this->editingWorkoutId = null;
    }

}; ?>

<section class="w-full">
    <div class="flex flex-col gap-8">
        <div>
            <flux:button variant="ghost" :href="route('programs.index')" wire:navigate>Programs</flux:button>
            <div class="mt-4 flex h-10 items-center">
                @if ($editingProgram)
                    <form wire:submit="saveProgram" class="flex w-full max-w-xl items-center gap-2">
                        <flux:input size="sm" wire:model="programName" aria-label="Program name" autofocus />
                        <flux:button type="submit" variant="primary" size="sm" square class="shrink-0" icon="check" aria-label="Save program name" />
                        <flux:button type="button" variant="ghost" size="sm" square class="shrink-0" icon="x-mark" wire:click="$set('editingProgram', false)" aria-label="Cancel editing program name" />
                    </form>
                @else
                    <flux:heading class="flex items-center gap-2" size="xl">
                        {{ $program->name }}
                        <flux:tooltip content="Rename program">
                            <flux:button type="button" variant="ghost" size="xs" icon="pencil" wire:click="editProgram" aria-label="Rename program" />
                        </flux:tooltip>
                    </flux:heading>
                @endif
            </div>
            @error('programName')
                <flux:error name="programName" />
            @enderror
            <flux:text class="mt-2">Record workouts for this program.</flux:text>
        </div>

        <flux:tab.group>
            <flux:tabs wire:model="workoutId" scrollable>
                @foreach ($program->workouts as $workout)
                    <flux:tab :name="$workout->id">Workout {{ $workout->label }}</flux:tab>
                @endforeach
            </flux:tabs>

            @foreach ($program->workouts as $workout)
                <flux:tab.panel :name="$workout->id">
                    <div class="flex flex-col gap-5">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <div class="mt-1 flex h-10 items-center">
                                    @if ($editingWorkoutId === $workout->id)
                                        <form wire:submit="saveWorkout" class="flex max-w-xl items-center gap-2">
                                            <flux:input size="sm" wire:model="workoutName" aria-label="Workout name" placeholder="Workout name" autofocus />
                                            <flux:button type="submit" variant="primary" size="sm" square class="shrink-0" icon="check" aria-label="Save workout name" />
                                            <flux:button type="button" variant="ghost" size="sm" square class="shrink-0" icon="x-mark" wire:click="$set('editingWorkoutId', null)" aria-label="Cancel editing workout name" />
                                        </form>
                                    @else
                                        <div class="flex items-center gap-2">
                                            @if ($workout->name)
                                                <flux:text>{{ $workout->name }}</flux:text>
                                            @else
                                                <flux:text class="text-zinc-500">Add a workout name</flux:text>
                                            @endif
                                            <flux:tooltip content="Rename workout">
                                                <flux:button type="button" variant="ghost" size="xs" icon="pencil" wire:click="editWorkout({{ $workout->id }})" aria-label="Rename workout" />
                                            </flux:tooltip>
                                        </div>
                                    @endif
                                </div>
                                @error('workoutName')
                                    <flux:error name="workoutName" />
                                @enderror
                            </div>
                            <flux:button variant="primary" :href="route('workout-entries.create', $workout)" wire:navigate>Log workout</flux:button>
                        </div>

                        @forelse ($workout->entries as $entry)
                            <a href="{{ route('workout-entries.edit', $entry) }}" wire:navigate class="rounded-xl border border-zinc-200 p-5 transition hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500">
                                <div class="flex items-center justify-between gap-4">
                                    <flux:heading>{{ $entry->performed_on->format('M j, Y') }}</flux:heading>
                                    <flux:text>{{ $entry->exercises()->count() }} {{ Str::plural('exercise', $entry->exercises()->count()) }}</flux:text>
                                </div>
                                @if ($entry->notes)
                                    <flux:text class="mt-2">{{ $entry->notes }}</flux:text>
                                @endif
                            </a>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-600">
                                <flux:heading>No entries yet</flux:heading>
                                <flux:text class="mx-auto mt-2 max-w-md">Your first entry starts empty. Later entries will copy this workout's latest exercises.</flux:text>
                            </div>
                        @endforelse
                    </div>
                </flux:tab.panel>
            @endforeach
        </flux:tab.group>
    </div>
</section>
