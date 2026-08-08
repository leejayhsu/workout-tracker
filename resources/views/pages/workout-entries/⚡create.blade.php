<?php

use App\Models\Workout;
use App\Exercise;
use App\WeightUnit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Log workout')] class extends Component {
    public Workout $workout;
    public string $performedOn = '';
    public string $notes = '';
    public ?string $exerciseToAdd = null;

    /** @var array<int, array<string, mixed>> */
    public array $exercises = [];

    public function plateOptions(string $unit): array
    {
        return $unit === WeightUnit::Lbs->value
            ? ['45' => '45', '35' => '35', '25' => '25', '10' => '10', '5' => '5', '2_5' => '2.5', '1_25' => '1.25']
            : ['25' => '25', '20' => '20', '15' => '15', '10' => '10', '5' => '5', '2_5' => '2.5', '1_25' => '1.25', '0_5' => '0.5'];
    }

    public function plateColor(string $plate): string
    {
        return match ($plate) {
            '45' => 'bg-rose-500 hover:bg-rose-400',
            '35', '20' => 'bg-blue-500 hover:bg-blue-400',
            '25', '15' => 'bg-amber-400 text-zinc-950 hover:bg-amber-300',
            '10' => 'bg-emerald-500 hover:bg-emerald-400',
            '5' => 'bg-zinc-300 text-zinc-900 hover:bg-zinc-200 dark:bg-zinc-400 dark:text-zinc-950 dark:hover:bg-zinc-300',
            '2.5' => 'bg-zinc-500 text-white hover:bg-zinc-400 dark:bg-zinc-500 dark:text-white dark:hover:bg-zinc-400',
            default => 'bg-zinc-700 text-white hover:bg-zinc-600 dark:bg-zinc-700 dark:text-white dark:hover:bg-zinc-600',
        };
    }

    public function plateThickness(string $plate): string
    {
        return match ($plate) {
            '45' => 'w-8',
            '35' => 'w-7',
            '25' => 'w-6',
            '10' => 'w-4',
            default => 'w-3',
        };
    }

    public function plateHeight(string $plate): string
    {
        return match ($plate) {
            '45', '35', '25', '20', '15', '10' => 'h-28',
            '5' => 'h-20',
            '2.5' => 'h-16',
            default => 'h-12',
        };
    }

    public function defaultBarWeight(string $unit): string
    {
        return $unit === WeightUnit::Lbs->value ? '45' : '20';
    }

    public function updatedExercises(mixed $value, string $key): void
    {
        $position = (int) (string) str($key)->before('.');

        if (str_ends_with($key, '.weight_mode') && $value === 'plates' && blank($this->exercises[$position]['bar_weight'] ?? null)) {
            $this->exercises[$position]['bar_weight'] = $this->defaultBarWeight((string) ($this->exercises[$position]['weight_unit'] ?? WeightUnit::Lbs->value));
        }

        if (! str_ends_with($key, '.weight_unit')) {
            return;
        }

        $this->exercises[$position]['bar_weight'] = $this->defaultBarWeight((string) $value);
    }

    public function incrementPlate(int $position, string $plate): void
    {
        $this->exercises[$position]['plate_counts'][$plate] = (int) ($this->exercises[$position]['plate_counts'][$plate] ?? 0) + 1;
    }

    public function decrementPlate(int $position, string $plate): void
    {
        $count = (int) ($this->exercises[$position]['plate_counts'][$plate] ?? 0);
        $this->exercises[$position]['plate_counts'][$plate] = max(0, $count - 1);
    }

    public function calculatedPlateWeight(array $exercise): string
    {
        $plateWeight = collect($exercise['plate_counts'] ?? [])
            ->sum(fn (int|string $count, string $plate): float => (float) str_replace('_', '.', $plate) * $count);

        return number_format(((float) ($exercise['bar_weight'] ?? 0)) + ($plateWeight * 2), 2);
    }

    public function mount(Workout $workout): void
    {
        abort_unless($workout->program->user_id === Auth::id(), 404);

        $this->workout = $workout;
        $this->performedOn = now(Auth::user()->timezone)->toDateString();

        $previousEntry = $workout->entries()
            ->whereDate('performed_on', '<=', $this->performedOn)
            ->latest('performed_on')
            ->latest('id')
            ->with('exercises')
            ->first();

        $this->exercises = $previousEntry?->exercises->map(function ($exercise): array {
            return [...$exercise->only([
                'exercise_key', 'exercise_name', 'position', 'sets', 'reps', 'weight', 'weight_unit',
                'weight_mode', 'bar_weight', 'plate_counts',
            ]),
                'weight_unit' => $exercise->weight_unit?->value,
                'weight_mode' => $exercise->weight_mode ?: 'total',
                'bar_weight' => $exercise->bar_weight,
                'plate_counts' => $exercise->plate_counts ?: [],
            ];
        })->all() ?? [];
    }

    public function addExercise(string $exerciseKey): void
    {
        $exercise = \App\Exercise::tryFrom($exerciseKey);

        if (! $exercise) {
            return;
        }

        $this->exercises[] = [
            'exercise_key' => $exercise->value,
            'exercise_name' => $exercise->label(),
            'position' => count($this->exercises),
            'sets' => 0,
            'reps' => 0,
            'weight' => null,
            'weight_unit' => WeightUnit::Kg->value,
            'weight_mode' => 'total',
            'bar_weight' => $this->defaultBarWeight(WeightUnit::Kg->value),
            'plate_counts' => [],
        ];
    }

    public function updatedExerciseToAdd(?string $exerciseKey): void
    {
        if ($exerciseKey === null) {
            return;
        }

        $this->addExercise($exerciseKey);
        $this->exerciseToAdd = null;
    }

    public function removeExercise(int $position): void
    {
        unset($this->exercises[$position]);
        $this->exercises = array_values($this->exercises);
    }

    public function createEntry(): void
    {
        $validated = $this->validate([
            'performedOn' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'exercises' => ['array'],
            'exercises.*.exercise_key' => ['required', 'string'],
            'exercises.*.exercise_name' => ['required', 'string'],
            'exercises.*.sets' => ['required', 'integer', 'min:0'],
            'exercises.*.reps' => ['required', 'integer', 'min:0'],
            'exercises.*.weight' => ['nullable', 'numeric', 'gt:0'],
            'exercises.*.weight_unit' => ['nullable', 'in:kg,lbs'],
            'exercises.*.weight_mode' => ['required', 'in:total,plates'],
            'exercises.*.bar_weight' => ['nullable', 'numeric', 'gt:0'],
            'exercises.*.plate_counts' => ['array'],
            'exercises.*.plate_counts.*' => ['integer', 'min:0'],
        ]);

        foreach ($validated['exercises'] as &$exercise) {
            if ($exercise['weight_mode'] !== 'plates') {
                continue;
            }

            $plateWeight = collect($exercise['plate_counts'] ?? [])
                ->sum(fn (int|string $count, string $plate): float => (float) str_replace('_', '.', $plate) * $count);
            $exercise['weight'] = round((float) $exercise['bar_weight'] + ($plateWeight * 2), 2);
        }
        unset($exercise);

        $entry = DB::transaction(function () use ($validated): \App\Models\WorkoutEntry {
            $entry = Auth::user()->workoutEntries()->create([
                'workout_id' => $this->workout->id,
                'performed_on' => $validated['performedOn'],
                'notes' => $validated['notes'] ?: null,
            ]);

            foreach ($validated['exercises'] as $position => $exercise) {
                $entry->exercises()->create([...$exercise, 'position' => $position]);
            }

            return $entry;
        });

        $this->redirect(route('workout-entries.edit', $entry, absolute: false), navigate: true);
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-8">
        <div>
            <flux:button variant="ghost" :href="route('workouts.index', $workout->program)" wire:navigate>Back to workouts</flux:button>
            <flux:heading class="mt-4" size="xl">Log workout {{ $workout->label }}</flux:heading>
            <flux:text class="mt-2">Add exercises as you train. The latest previous entry is copied for you.</flux:text>
        </div>

        <form wire:submit="createEntry" class="flex flex-col gap-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:date-picker wire:model="performedOn" label="Date" max="today" with-inputs="custom" />
                <flux:input wire:model="notes" label="Notes" placeholder="How did it feel?" />
            </div>

            @foreach ($exercises as $position => $exercise)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-4">
                        <flux:heading>{{ $exercise['exercise_name'] }}</flux:heading>
                        <flux:button type="button" variant="ghost" wire:click="removeExercise({{ $position }})">Remove</flux:button>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-4">
                        <flux:input wire:model="exercises.{{ $position }}.sets" type="number" min="0" label="Sets" />
                        <flux:input wire:model="exercises.{{ $position }}.reps" type="number" min="0" label="Reps" />
                        <flux:select wire:model.live="exercises.{{ $position }}.weight_mode" label="Enter weight as">
                            <flux:select.option value="total">Total weight</flux:select.option>
                            <flux:select.option value="plates">Plates per side</flux:select.option>
                        </flux:select>
                        <flux:select wire:model="exercises.{{ $position }}.weight_unit" label="Unit">
                            @foreach (WeightUnit::cases() as $unit)
                                <flux:select.option value="{{ $unit->value }}">{{ strtoupper($unit->value) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    @if ($exercise['weight_mode'] === 'plates')
                        <div class="mt-4 rounded-lg bg-zinc-100 p-4 dark:bg-zinc-800">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <flux:input wire:model="exercises.{{ $position }}.bar_weight" type="number" min="0.01" step="0.01" label="Bar weight" />
                                <flux:input value="{{ $this->calculatedPlateWeight($exercise) }}" readonly label="Total weight" />
                            </div>
                            <flux:text class="mt-3">Tap a plate to add it to one side of the bar.</flux:text>
                            <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                                @php $hasPlates = collect($exercise['plate_counts'] ?? [])->sum() > 0; @endphp
                                <div class="relative flex h-36 items-center justify-center overflow-hidden bg-zinc-100 px-4 dark:bg-zinc-950">
                                    <div class="absolute h-3 w-1/2 rounded-full bg-zinc-600 shadow-inner dark:bg-zinc-800"></div>
                                    <div class="relative flex h-28 items-center gap-1">
                                        @if ($hasPlates)
                                            @foreach ($this->plateOptions($exercise['weight_unit']) as $plateKey => $plate)
                                                @php $count = (int) ($exercise['plate_counts'][$plateKey] ?? 0); @endphp
                                                @for ($plateIndex = 0; $plateIndex < min(8, $count); $plateIndex++)
                                                    <span class="{{ $this->plateHeight($plate) }} {{ $this->plateThickness($plate) }} rounded-lg {{ $this->plateColor($plate) }} shadow-sm" title="{{ $plate }} {{ strtoupper($exercise['weight_unit']) }}"></span>
                                                @endfor
                                            @endforeach
                                        @else
                                            @for ($plateIndex = 0; $plateIndex < 3; $plateIndex++)
                                                <span class="h-24 w-3 rounded-md border-2 border-dashed border-indigo-400 opacity-50"></span>
                                            @endfor
                                        @endif
                                    </div>
                                </div>
                                <div class="-mx-px flex snap-x gap-2 overflow-x-auto border-t border-zinc-200 p-3 sm:justify-center dark:border-zinc-700">
                                    @foreach ($this->plateOptions($exercise['weight_unit']) as $plateKey => $plate)
                                        @php $count = (int) ($exercise['plate_counts'][$plateKey] ?? 0); @endphp
                                        <div wire:key="plate-{{ $position }}-{{ $plateKey }}" class="relative flex shrink-0 snap-start flex-col items-center gap-1">
                                            <button type="button" wire:click="incrementPlate({{ $position }}, '{{ $plateKey }}')" class="{{ $this->plateColor($plate) }} relative flex h-20 w-16 flex-col items-center justify-end rounded-xl pb-2 font-semibold shadow-sm transition active:scale-95" aria-label="Add {{ $plate }} {{ strtoupper($exercise['weight_unit']) }} plate">
                                                <span class="absolute left-1/2 top-2 flex size-7 -translate-x-1/2 items-center justify-center rounded-full bg-black/20 text-sm text-white">+</span>
                                                <span class="text-lg leading-none">{{ $plate }}</span>
                                                <span class="text-[10px] uppercase opacity-75">{{ $exercise['weight_unit'] }}</span>
                                            </button>
                                            <flux:button type="button" size="sm" square icon="minus" variant="ghost" wire:click="decrementPlate({{ $position }}, '{{ $plateKey }}')" aria-label="Remove {{ $plate }} {{ strtoupper($exercise['weight_unit']) }} plate" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-4">
                            <flux:input wire:model="exercises.{{ $position }}.weight" type="number" min="0.01" step="0.01" label="Total weight" />
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="rounded-xl border border-dashed border-zinc-300 p-4 dark:border-zinc-600">
                <flux:heading size="sm">Add exercise</flux:heading>
                <div class="mt-3">
                    <flux:select wire:model="exerciseToAdd" variant="listbox" searchable placeholder="Search exercises..." empty="No exercises found.">
                        @foreach (Exercise::cases() as $exercise)
                            <flux:select.option wire:key="exercise-{{ $exercise->value }}" value="{{ $exercise->value }}">{{ $exercise->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('workouts.index', $workout->program)" wire:navigate>Cancel</flux:button>
                <flux:button variant="primary" type="submit">Create entry</flux:button>
            </div>
        </form>
    </div>
</section>
