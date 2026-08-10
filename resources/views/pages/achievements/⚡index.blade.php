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

        <div class="grid grid-cols-[repeat(auto-fill,6.25rem)] gap-3" aria-label="Achievement milestones">
            @foreach ($achievements as $achievement)
                @php($unlockedOn = $achievement->userAchievements->first()?->unlocked_at)
                <x-achievements.tooltip :achievement="$achievement" :unlocked-on="$unlockedOn" toggleable class="md:hidden" wire:key="achievement-{{ $achievement->key }}-mobile" />
                <x-achievements.tooltip :achievement="$achievement" :unlocked-on="$unlockedOn" class="hidden md:block" wire:key="achievement-{{ $achievement->key }}-desktop" />
            @endforeach
        </div>
    </div>

</section>
