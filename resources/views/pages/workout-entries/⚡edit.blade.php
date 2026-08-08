<?php

use App\Exercise;
use App\Models\WorkoutEntry;
use App\WeightUnit;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit workout entry')] class extends Component {
    public WorkoutEntry $entry;
    public string $performedOn = '';
    public string $notes = '';
    public ?string $exerciseToAdd = null;
    public ?int $plateCalculatorPosition = null;
    public ?int $plateCalculatorSetPosition = null;
    public string $plateCalculatorBarWeight = '';

    /** @var array<string, int> */
    public array $plateCalculatorCounts = [];

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

    /**
     * @return array<string, int>
     */
    public function defaultPlateCounts(string $unit, float|int|string|null $weight): array
    {
        $barWeight = (float) $this->defaultBarWeight($unit);
        $weight = (float) $weight;
        $remainingPerSide = (int) round(($weight - $barWeight) * 100 / 2);
        $counts = [];

        if ($remainingPerSide <= 0) {
            return $counts;
        }

        foreach ($this->plateOptions($unit) as $plateKey => $plate) {
            $plateWeight = (int) round((float) $plate * 100);
            $count = intdiv($remainingPerSide, $plateWeight);

            if ($count > 0) {
                $counts[$plateKey] = $count;
                $remainingPerSide -= $plateWeight * $count;
            }
        }

        return $counts;
    }

    public function openPlateCalculator(int $position, int $setPosition): void
    {
        $this->plateCalculatorPosition = $position;
        $this->plateCalculatorSetPosition = $setPosition;
        $unit = (string) ($this->exercises[$position]['weight_unit'] ?? WeightUnit::Kg->value);
        $weight = $this->exercises[$position]['sets'][$setPosition]['weight'] ?? null;
        $this->plateCalculatorBarWeight = $this->defaultBarWeight($unit);
        $this->plateCalculatorCounts = $this->defaultPlateCounts($unit, $weight);
        $this->modal('plate-calculator')->show();
    }

    public function incrementPlate(string $plate): void
    {
        $this->plateCalculatorCounts[$plate] = (int) ($this->plateCalculatorCounts[$plate] ?? 0) + 1;
    }

    public function decrementPlate(string $plate): void
    {
        $count = (int) ($this->plateCalculatorCounts[$plate] ?? 0);
        $this->plateCalculatorCounts[$plate] = max(0, $count - 1);
    }

    public function calculatedPlateWeight(): string
    {
        $plateWeight = collect($this->plateCalculatorCounts)
            ->sum(fn (int|string $count, string $plate): float => (float) str_replace('_', '.', $plate) * $count);

        return number_format(((float) $this->plateCalculatorBarWeight) + ($plateWeight * 2), 2);
    }

    public function applyPlateWeight(): void
    {
        $this->validate([
            'plateCalculatorBarWeight' => ['required', 'numeric', 'gt:0'],
            'plateCalculatorCounts.*' => ['integer', 'min:0'],
        ]);

        if ($this->plateCalculatorPosition === null || $this->plateCalculatorSetPosition === null) {
            return;
        }

        $this->exercises[$this->plateCalculatorPosition]['sets'][$this->plateCalculatorSetPosition]['weight'] = $this->calculatedPlateWeight();
        $this->modal('plate-calculator')->close();
    }

    public function mount(WorkoutEntry $workoutEntry): void
    {
        abort_unless($workoutEntry->user_id === Auth::id(), 404);

        $this->entry = $workoutEntry->load('workout.program', 'exercises.sets');
        $this->performedOn = $workoutEntry->performed_on->toDateString();
        $this->notes = $workoutEntry->notes ?? '';
        $this->exercises = $workoutEntry->exercises->map(function ($exercise): array {
            return [...$exercise->only([
                'exercise_key', 'exercise_name', 'position', 'weight_unit',
            ]),
                'sets' => $exercise->sets->map(fn ($set): array => [
                    'reps' => $set->reps,
                    'weight' => $set->weight,
                ])->all(),
                'weight_unit' => $exercise->weight_unit?->value,
            ];
        })->all();
    }

    public function addExercise(string $exerciseKey): void
    {
        $exercise = Exercise::tryFrom($exerciseKey);

        if (! $exercise) {
            return;
        }

        $this->exercises[] = [
            'exercise_key' => $exercise->value,
            'exercise_name' => $exercise->label(),
            'position' => count($this->exercises),
            'sets' => [],
            'weight_unit' => WeightUnit::Kg->value,
        ];
    }

    public function addSet(int $position): void
    {
        $sets = $this->exercises[$position]['sets'];
        $previousSet = $sets[array_key_last($sets)] ?? ['reps' => 0, 'weight' => null];

        $this->exercises[$position]['sets'][] = $previousSet;
    }

    public function removeSet(int $position, int $setPosition): void
    {
        unset($this->exercises[$position]['sets'][$setPosition]);
        $this->exercises[$position]['sets'] = array_values($this->exercises[$position]['sets']);
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

    public function saveEntry(): void
    {
        $validated = $this->validate([
            'performedOn' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'exercises' => ['array'],
            'exercises.*.exercise_key' => ['required', 'string'],
            'exercises.*.exercise_name' => ['required', 'string'],
            'exercises.*.sets' => ['array'],
            'exercises.*.sets.*.reps' => ['required', 'integer', 'min:0'],
            'exercises.*.sets.*.weight' => ['nullable', 'numeric', 'gt:0'],
            'exercises.*.weight_unit' => ['nullable', 'in:kg,lbs'],
        ]);

        $this->entry->update([
            'performed_on' => $validated['performedOn'],
            'notes' => $validated['notes'] ?: null,
        ]);
        $this->entry->exercises()->delete();

        foreach ($validated['exercises'] as $position => $exercise) {
            $sets = $exercise['sets'];
            unset($exercise['sets']);
            $entryExercise = $this->entry->exercises()->create([...$exercise, 'position' => $position]);
            $entryExercise->sets()->createMany(array_map(
                fn (array $set, int $setPosition): array => [...$set, 'position' => $setPosition],
                $sets,
                array_keys($sets),
            ));
        }

        $this->redirect(route('workouts.index', [$this->entry->workout->program, 'workoutId' => $this->entry->workout_id], absolute: false), navigate: true);
    }

    public function deleteEntry(): void
    {
        $program = $this->entry->workout->program;
        $this->entry->delete();

        $this->redirect(route('workouts.index', $program, absolute: false), navigate: true);
    }
}; ?>

<div>
<section class="w-full">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-8">
        <div>
            <flux:button variant="ghost" :href="route('workouts.index', [$entry->workout->program, 'workoutId' => $entry->workout_id])" wire:navigate>Back to workouts</flux:button>
            <flux:heading class="mt-4" size="xl">Edit workout {{ $entry->workout->label }}</flux:heading>
        </div>

        <form wire:submit="saveEntry" class="flex flex-col gap-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:date-picker wire:model="performedOn" label="Date" max="today" with-inputs="custom" />
                <flux:input wire:model="notes" label="Notes" />
            </div>

            @foreach ($exercises as $position => $exercise)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-4">
                        <flux:heading>{{ $exercise['exercise_name'] }}</flux:heading>
                        <flux:button type="button" variant="ghost" square icon="x-mark" class="text-red-600 dark:text-red-400" wire:click="removeExercise({{ $position }})" aria-label="Remove exercise" />
                    </div>
                    <div class="mt-4 flex flex-col gap-3">
                        @foreach ($exercise['sets'] as $setPosition => $set)
                            <div wire:key="set-{{ $position }}-{{ $setPosition }}" class="grid min-w-0 grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto] items-end gap-3">
                                <flux:input wire:model="exercises.{{ $position }}.sets.{{ $setPosition }}.reps" type="number" min="0" label="Set {{ $setPosition + 1 }} reps" />
                                <flux:input wire:model="exercises.{{ $position }}.sets.{{ $setPosition }}.weight" type="number" min="0.01" step="0.01" label="Weight" />
                                <flux:modal.trigger name="plate-calculator">
                                    <flux:button type="button" variant="ghost" square wire:click="openPlateCalculator({{ $position }}, {{ $setPosition }})" aria-label="Calculate plates">
                                        <flux:icon.circle-stack />
                                    </flux:button>
                                </flux:modal.trigger>
                                <flux:button type="button" variant="ghost" square icon="x-mark" class="text-red-600 dark:text-red-400" wire:click="removeSet({{ $position }}, {{ $setPosition }})" aria-label="Remove set" />
                            </div>
                        @endforeach
                        <flux:button type="button" variant="ghost" wire:click="addSet({{ $position }})">Add set</flux:button>
                    </div>
                    <div class="mt-4 max-w-xs">
                        <flux:select wire:model="exercises.{{ $position }}.weight_unit" label="Unit">
                            @foreach (WeightUnit::cases() as $unit)
                                <flux:select.option value="{{ $unit->value }}">{{ strtoupper($unit->value) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
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

            <div class="flex justify-between gap-3">
                <flux:button type="button" variant="danger" wire:click="deleteEntry">Delete entry</flux:button>
                <flux:button variant="primary" type="submit">Save entry</flux:button>
            </div>
        </form>
    </div>
</section>

<flux:modal name="plate-calculator" flyout position="bottom" variant="floating" class="max-w-xl">
    <div class="flex flex-col gap-6">
        <div>
            <flux:heading size="lg">Calculate plate weight</flux:heading>
        </div>
        @php $hasPlates = collect($plateCalculatorCounts)->sum() > 0; @endphp
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="relative flex h-36 items-center justify-center overflow-hidden bg-zinc-100 px-4 dark:bg-zinc-950">
                <div class="absolute h-3 w-1/2 rounded-full bg-zinc-600 shadow-inner dark:bg-zinc-800"></div>
                <div class="relative flex h-28 items-center gap-1">
                    @if ($hasPlates)
                        @foreach ($this->plateOptions($plateCalculatorPosition !== null ? ($exercises[$plateCalculatorPosition]['weight_unit'] ?? WeightUnit::Kg->value) : WeightUnit::Kg->value) as $plateKey => $plate)
                            @php $count = (int) ($plateCalculatorCounts[$plateKey] ?? 0); @endphp
                            @for ($plateIndex = 0; $plateIndex < min(8, $count); $plateIndex++)
                                <span class="{{ $this->plateHeight($plate) }} {{ $this->plateThickness($plate) }} rounded-lg {{ $this->plateColor($plate) }} shadow-sm" title="{{ $plate }} plate"></span>
                            @endfor
                        @endforeach
                    @else
                        @for ($plateIndex = 0; $plateIndex < 3; $plateIndex++)
                            <span class="h-24 w-3 rounded-md border-2 border-dashed border-indigo-400 opacity-50"></span>
                        @endfor
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-wrap justify-center gap-2">
            @foreach ($this->plateOptions($plateCalculatorPosition !== null ? ($exercises[$plateCalculatorPosition]['weight_unit'] ?? WeightUnit::Kg->value) : WeightUnit::Kg->value) as $plateKey => $plate)
                <div wire:key="calculator-plate-{{ $plateKey }}" class="flex flex-col items-center gap-1">
                    <button type="button" wire:click="incrementPlate('{{ $plateKey }}')" class="{{ $this->plateColor($plate) }} relative flex h-20 w-16 flex-col items-center justify-end rounded-xl pb-2 font-semibold shadow-sm transition active:scale-95" aria-label="Add {{ $plate }} plate">
                        <span class="absolute left-1/2 top-2 flex size-7 -translate-x-1/2 items-center justify-center rounded-full bg-black/20 text-sm text-white">+</span>
                        <span class="text-lg leading-none">{{ $plate }}</span>
                    </button>
                    <flux:button type="button" size="sm" square icon="minus" variant="ghost" wire:click="decrementPlate('{{ $plateKey }}')" aria-label="Remove {{ $plate }} plate" />
                </div>
            @endforeach
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="plateCalculatorBarWeight" type="number" min="0.01" step="0.01" label="Bar weight" />
            <flux:input value="{{ $this->calculatedPlateWeight() }}" readonly label="Total weight" />
        </div>
        <div class="flex justify-end gap-2">
            <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
            <flux:button type="button" variant="primary" wire:click="applyPlateWeight">Use this weight</flux:button>
        </div>
    </div>
</flux:modal>
</div>
