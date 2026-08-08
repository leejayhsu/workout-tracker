<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Program;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Programs')] class extends Component {
    #[Computed]
    public function programs(): \Illuminate\Database\Eloquent\Collection
    {
        $today = today(Auth::user()->timezone);

        return Auth::user()->programs()
            ->with([
                'workouts',
                'workouts.entries' => fn ($query) => $query
                    ->whereDate('performed_on', '>=', $today->copy()->subDays(13)->toDateString())
                    ->whereDate('performed_on', '<=', $today->toDateString())
                    ->latest('id'),
            ])
            ->latest()
            ->get();
    }

    /** @return array<int, array{date: Carbon, entry: ?\App\Models\WorkoutEntry, label: ?string}> */
    public function activityFor(Program $program): array
    {
        $program = Auth::user()->programs()
            ->whereKey($program->getKey())
            ->with([
                'workouts.entries' => fn ($query) => $query->whereBelongsTo(Auth::user()),
            ])
            ->first();
        abort_unless($program, 404);
        $today = today(Auth::user()->timezone);
        $entries = $program->workouts->flatMap(fn ($workout) => $workout->entries->map(
            fn ($entry) => ['entry' => $entry, 'label' => $workout->label],
        ))->groupBy(fn ($activity) => $activity['entry']->performed_on->toDateString());

        return collect(range(13, 0))->map(function (int $daysAgo) use ($today, $entries): array {
            $date = $today->copy()->subDays($daysAgo);
            $activity = $entries->get($date->toDateString())?->sortByDesc(fn ($item) => $item['entry']->id)->first();

            return [
                'date' => $date,
                'entry' => $activity['entry'] ?? null,
                'label' => $activity['label'] ?? null,
            ];
        })->all();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <flux:heading size="xl">Programs</flux:heading>
                <flux:text class="mt-2">Your workout programs.</flux:text>
            </div>
            <flux:button variant="primary" :href="route('programs.create')" wire:navigate>New program</flux:button>
        </div>

        @forelse ($this->programs as $program)
            <div class="program-card rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <div class="grid grid-cols-[1fr_auto] items-center gap-5 lg:flex lg:flex-row lg:items-center lg:justify-between">
                        <div class="shrink-0">
                            <flux:heading>{{ $program->name }}</flux:heading>
                        </div>
                        @php($activity = $this->activityFor($program))
                        <div class="order-3 col-span-2 min-w-0 lg:order-none lg:flex-1">
                            <div class="flex justify-end gap-1.5 sm:gap-2" aria-label="Recent workout activity">
                                @foreach ($activity as $day)
                                    <div wire:key="activity-{{ $program->id }}-{{ $day['date']->toDateString() }}" class="program-activity-wide size-6 shrink-0">
                                        @if ($day['entry'])
                                            <flux:tooltip class="size-6" content="Workout {{ $day['label'] }} on {{ $day['date']->format('M j, Y') }}">
                                                <a href="{{ route('workout-entries.edit', $day['entry']) }}" wire:navigate class="flex size-6 shrink-0 justify-center" aria-label="Workout {{ $day['label'] }} on {{ $day['date']->format('M j, Y') }}" data-activity-date="{{ $day['date']->toDateString() }}">
                                                    <flux:badge class="flex size-full items-center justify-center px-0" color="blue" variant="solid">{{ $day['label'] }}</flux:badge>
                                                </a>
                                            </flux:tooltip>
                                        @else
                                            <flux:tooltip class="size-6" content="No workout on {{ $day['date']->format('M j, Y') }}">
                                                <span class="block size-6 shrink-0 rounded-md border border-zinc-200 bg-zinc-100/70 dark:border-zinc-700 dark:bg-zinc-800/60" aria-label="No workout on {{ $day['date']->format('M j, Y') }}"></span>
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                @endforeach
                                @foreach (array_slice($activity, -7) as $day)
                                    <div wire:key="mobile-activity-{{ $program->id }}-{{ $day['date']->toDateString() }}" class="program-activity-compact size-6 shrink-0">
                                        @if ($day['entry'])
                                            <flux:tooltip class="size-6" content="Workout {{ $day['label'] }} on {{ $day['date']->format('M j, Y') }}">
                                                <a href="{{ route('workout-entries.edit', $day['entry']) }}" wire:navigate class="flex size-6 shrink-0 justify-center" aria-label="Workout {{ $day['label'] }} on {{ $day['date']->format('M j, Y') }}" data-activity-date="{{ $day['date']->toDateString() }}">
                                                    <flux:badge class="flex size-full items-center justify-center px-0" color="blue" variant="solid">{{ $day['label'] }}</flux:badge>
                                                </a>
                                            </flux:tooltip>
                                        @else
                                            <flux:tooltip class="size-6" content="No workout on {{ $day['date']->format('M j, Y') }}">
                                                <span class="block size-6 shrink-0 rounded-md border border-zinc-200 bg-zinc-100/70 dark:border-zinc-700 dark:bg-zinc-800/60" aria-label="No workout on {{ $day['date']->format('M j, Y') }}"></span>
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="program-card-actions flex shrink-0 items-center justify-self-end gap-3 lg:gap-4">
                            <flux:text class="whitespace-nowrap font-medium">{{ $program->workouts->pluck('label')->join(' / ') }}</flux:text>
                            <div class="lg:hidden">
                                <flux:button variant="primary" :href="route('workouts.index', $program)" wire:navigate square icon="eye" aria-label="View workouts" />
                            </div>
                            <div class="hidden lg:block">
                                <flux:button variant="primary" :href="route('workouts.index', $program)" wire:navigate>View workouts</flux:button>
                            </div>
                        </div>
                    </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-600">
                <flux:heading>No programs yet</flux:heading>
                <flux:text class="mx-auto mt-2 max-w-md">Create your first plan to define the workouts you want to repeat.</flux:text>
                <flux:button class="mt-6" :href="route('programs.create')" wire:navigate>Create your first program</flux:button>
            </div>
        @endforelse
    </div>
</section>
