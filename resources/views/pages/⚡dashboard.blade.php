<?php

use App\Models\WorkoutEntry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    #[Computed]
    public function hasPrograms(): bool
    {
        return Auth::user()->programs()->exists();
    }

    #[Computed]
    public function month(): CarbonImmutable
    {
        return today(Auth::user()->timezone)->startOfMonth();
    }

    /** @return Collection<int, WorkoutEntry> */
    #[Computed]
    public function workoutEntries(): Collection
    {
        return Auth::user()->workoutEntries()
            ->whereBetween('performed_on', [
                $this->month()->toDateString(),
                $this->month()->copy()->endOfMonth()->toDateString(),
            ])
            ->latest('performed_on')
            ->latest('id')
            ->get();
    }

    /** @return array<int, string> */
    #[Computed]
    public function workoutDates(): array
    {
        return $this->workoutEntries
            ->pluck('performed_on')
            ->map(fn (CarbonImmutable $date): string => $date->toDateString())
            ->unique()
            ->values()
            ->all();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-8">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <flux:heading size="xl">Your training</flux:heading>
                <flux:text class="mt-2">See your month at a glance and jump back into any workout.</flux:text>
            </div>
            @if (! $this->hasPrograms)
                <flux:button variant="primary" :href="route('programs.create')" wire:navigate>Create program</flux:button>
            @endif
        </div>

        <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="min-w-0 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <div>
                        <flux:heading size="lg">{{ $this->month->format('F Y') }}</flux:heading>
                        <flux:text class="mt-1">A marked day means you trained.</flux:text>
                    </div>
                    <flux:badge color="blue" variant="solid" class="shrink-0">{{ $this->workoutEntries->count() }} {{ Str::plural('workout', $this->workoutEntries->count()) }}</flux:badge>
                </div>

                <div class="mt-6 min-w-0 overflow-x-auto">
                    <div class="sm:hidden">
                        <flux:calendar static multiple size="lg" start-day="1" :value="$this->workoutDates" aria-label="Workout calendar" />
                    </div>
                    <div class="hidden sm:block">
                        <flux:calendar static multiple size="2xl" start-day="1" :value="$this->workoutDates" aria-label="Workout calendar" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="flex items-baseline justify-between gap-4">
                    <flux:heading size="lg">This month</flux:heading>
                    <flux:text size="sm">{{ $this->workoutEntries->count() }} entries</flux:text>
                </div>

                <div class="mt-5 flex flex-col gap-3">
                    @forelse ($this->workoutEntries as $entry)
                        <a href="{{ route('workout-entries.edit', $entry) }}" wire:navigate data-workout-date="{{ $entry->performed_on->toDateString() }}" class="group flex items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white px-4 py-3 transition hover:border-blue-400 hover:bg-blue-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-blue-500 dark:hover:bg-blue-950/30">
                            <div class="flex items-center gap-3">
                                <span class="flex size-8 items-center justify-center rounded-full bg-blue-600 text-white" aria-hidden="true">
                                    <flux:icon.check class="size-4" />
                                </span>
                                <div>
                                    <flux:heading size="sm">{{ $entry->performed_on->format('D, M j') }}</flux:heading>
                                    <flux:text size="sm">Workout entry</flux:text>
                                </div>
                            </div>
                            <flux:icon.arrow-up-right class="size-4 text-zinc-400 transition group-hover:text-blue-600" />
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-600">
                            <flux:text>No workouts logged this month.</flux:text>
                            <flux:text size="sm" class="mt-1">Your completed days will show up here.</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</section>
