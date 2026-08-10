<?php

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Achievements')] class extends Component {
    /** @return Collection<int, Achievement> */
    public function achievements(): Collection
    {
        return Achievement::query()
            ->with(['userAchievements' => fn ($query) => $query->whereBelongsTo(Auth::user())])
            ->orderBy('category')
            ->orderBy('threshold')
            ->orderBy('id')
            ->get();
    }
};
?>

<section class="w-full">
    @php($achievements = $this->achievements())
    @php($unlockedCount = $achievements->filter(fn (Achievement $achievement) => $achievement->userAchievements->isNotEmpty())->count())

    <div class="flex flex-col gap-8">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <flux:heading size="xl">Achievements</flux:heading>
                <flux:text class="mt-2">Every workout entry moves you closer to your next milestone.</flux:text>
            </div>
            <flux:badge color="amber" variant="solid" icon="trophy" class="shrink-0">
                {{ $unlockedCount }} of {{ count($achievements) }} unlocked
            </flux:badge>
        </div>

        <div class="grid grid-cols-[repeat(auto-fill,4.6875rem)] gap-3 sm:grid-cols-[repeat(auto-fill,6.25rem)]" aria-label="Achievement milestones">
            @foreach ($achievements as $achievement)
                @php($unlockedOn = $achievement->userAchievements->first()?->unlocked_at)
                <flux:tooltip wire:key="achievement-{{ $achievement->key }}">
                    <button
                        type="button"
                        @class([
                            'flex size-[75px] items-center justify-center rounded-xl border transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:size-[100px] dark:focus:ring-offset-zinc-800',
                            'border-amber-500 bg-amber-400 text-amber-950 shadow-sm shadow-amber-400/40' => $unlockedOn,
                            'border-zinc-300 bg-zinc-100 text-zinc-400 hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-500 dark:hover:border-zinc-600' => ! $unlockedOn,
                        ])
                        aria-label="{{ $achievement->is_secret && ! $unlockedOn ? 'Locked mystery achievement' : $achievement->name }}"
                    >
                        @if ($achievement->thumbnail_path)
                            <img src="{{ asset($achievement->thumbnail_path) }}" alt="" @class([
                                'size-full rounded-[0.6875rem] object-cover',
                                'grayscale opacity-50' => ! $unlockedOn,
                            ])>
                        @elseif ($unlockedOn)
                            <flux:icon.check variant="micro" />
                        @elseif ($achievement->is_secret)
                            <flux:icon.question-mark-circle variant="micro" />
                        @else
                            <flux:icon.lock-closed variant="micro" />
                        @endif
                    </button>

                    <flux:tooltip.content>
                        @if ($achievement->is_secret && ! $unlockedOn)
                            <div class="flex gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    <flux:icon.lock-closed class="size-5" />
                                </div>
                                <div>
                                    <flux:heading size="sm">Mystery achievement</flux:heading>
                                    <flux:text size="sm" class="mt-1">Keep training to discover this milestone.</flux:text>
                                </div>
                            </div>
                        @else
                            <div class="flex gap-3">
                                <div>
                                    <flux:heading size="sm" class="text-white">{{ $achievement->name }}</flux:heading>
                                    <flux:text size="sm" class="mt-1">{{ $achievement->description }}</flux:text>
                                    @if ($unlockedOn)
                                        <flux:text size="sm" class="mt-3 font-medium text-amber-700 dark:text-amber-300">Unlocked {{ $unlockedOn->format('M j, Y') }}</flux:text>
                                    @else
                                        <flux:text size="sm" class="mt-3">Goal: {{ $achievement->threshold }} workout entries</flux:text>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </flux:tooltip.content>
                </flux:tooltip>
            @endforeach
        </div>
    </div>

</section>
