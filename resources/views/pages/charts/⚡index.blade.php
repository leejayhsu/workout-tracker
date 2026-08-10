<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Charts')] class extends Component {
    /**
     * @return Collection<int, array{exerciseName: string, unit: string, entries: int, highestWeight: float, data: array<int, array{date: string, highestWeight: float}>}>
     */
    #[Computed]
    public function charts(): Collection
    {
        $sets = DB::table('workout_entry_exercise_sets as sets')
            ->join('workout_entry_exercises as exercises', 'exercises.id', '=', 'sets.workout_entry_exercise_id')
            ->join('workout_entries as entries', 'entries.id', '=', 'exercises.workout_entry_id')
            ->where('entries.user_id', Auth::id())
            ->where('sets.reps', '>=', 1)
            ->where('sets.weight', '>', 0)
            ->select([
                'entries.id as entry_id',
                'entries.performed_on',
                'exercises.exercise_key',
                'exercises.exercise_name',
                'exercises.weight_unit',
                'sets.weight',
            ])
            ->get();

        return $sets
            ->groupBy(fn (object $set): string => implode('|', [
                $set->exercise_key,
                $set->exercise_name,
                $set->weight_unit,
            ]))
            ->map(function (Collection $exerciseSets): array {
                $data = $exerciseSets
                    ->groupBy('entry_id')
                    ->map(function (Collection $entrySets): array {
                        $firstSet = $entrySets->first();

                        return [
                            'date' => Carbon::parse($firstSet->performed_on)->format('Y-m-d\T00:00:00\Z'),
                            'highestWeight' => (float) $entrySets->max('weight'),
                        ];
                    })
                    ->sortBy('date')
                    ->values()
                    ->all();

                $firstSet = $exerciseSets->first();

                return [
                    'exerciseName' => $firstSet->exercise_name,
                    'unit' => (string) $firstSet->weight_unit,
                    'entries' => count($data),
                    'highestWeight' => max(array_column($data, 'highestWeight')),
                    'data' => $data,
                ];
            })
            ->filter(fn (array $chart): bool => $chart['entries'] >= 3)
            ->sortBy(['exerciseName', 'unit'])
            ->values();
    }
};
?>

<section class="w-full">
    <div class="flex flex-col gap-8">
        <div>
            <flux:heading size="xl">Progress charts</flux:heading>
            <flux:text class="mt-2">Your highest recorded weight for every exercise with at least three logged entries.</flux:text>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            @forelse ($this->charts as $chart)
                <div wire:key="chart-{{ Str::slug($chart['exerciseName']) }}-{{ $chart['unit'] }}" class="rounded-2xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading size="lg">{{ $chart['exerciseName'] }}</flux:heading>
                            <flux:text size="sm" class="mt-1">{{ $chart['entries'] }} workout entries</flux:text>
                        </div>
                        <flux:badge color="blue" variant="solid" class="shrink-0">
                            {{ Number::format($chart['highestWeight']) }} {{ Str::upper($chart['unit']) }}
                        </flux:badge>
                    </div>

                    <flux:chart :value="$chart['data']" class="mt-6 aspect-[3/1]">
                        <flux:chart.svg>
                            <flux:chart.line field="highestWeight" class="text-blue-500 dark:text-blue-400" />
                            <flux:chart.point field="highestWeight" class="text-blue-500 dark:text-blue-400" />
                            <flux:chart.axis axis="x" field="date" :format="['month' => 'short', 'day' => 'numeric']">
                                <flux:chart.axis.line />
                                <flux:chart.axis.tick />
                            </flux:chart.axis>
                            <flux:chart.axis axis="y">
                                <flux:chart.axis.grid />
                                <flux:chart.axis.tick />
                            </flux:chart.axis>
                            <flux:chart.cursor />
                        </flux:chart.svg>
                        <flux:chart.tooltip>
                            <flux:chart.tooltip.heading field="date" />
                            <flux:chart.tooltip.value field="highestWeight" label="Highest weight" />
                        </flux:chart.tooltip>
                    </flux:chart>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 p-8 text-center xl:col-span-2 dark:border-zinc-600">
                    <flux:heading size="lg">No charts yet</flux:heading>
                    <flux:text class="mt-2">Log an exercise with at least one rep in three workout entries to see its progress here.</flux:text>
                </div>
            @endforelse
        </div>
    </div>
</section>
