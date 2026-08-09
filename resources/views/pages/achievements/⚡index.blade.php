<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Achievements')] class extends Component {
    /**
     * @return array<int, array{threshold: int, name: string, description: string, unlocked_on: ?string, is_secret: bool}>
     */
    public function achievements(): array
    {
        return [
            ['threshold' => 1, 'name' => 'First Step', 'description' => 'Complete your first workout entry.', 'unlocked_on' => 'Aug 1, 2026', 'is_secret' => false],
            ['threshold' => 3, 'name' => 'Finding Your Rhythm', 'description' => 'Complete 3 workout entries.', 'unlocked_on' => 'Aug 4, 2026', 'is_secret' => false],
            ['threshold' => 5, 'name' => 'Five Strong', 'description' => 'Complete 5 workout entries.', 'unlocked_on' => 'Aug 7, 2026', 'is_secret' => false],
            ['threshold' => 10, 'name' => 'Double Digits', 'description' => 'Complete 10 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 25, 'name' => 'Built to Last', 'description' => 'Complete 25 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 50, 'name' => 'Half Century', 'description' => 'Complete 50 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 75, 'name' => 'Steady Work', 'description' => 'Complete 75 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 100, 'name' => 'Centurion', 'description' => 'Complete 100 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 125, 'name' => 'Iron Resolve', 'description' => 'Complete 125 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 150, 'name' => 'Work Ethic', 'description' => 'Complete 150 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 175, 'name' => 'Committed', 'description' => 'Complete 175 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 200, 'name' => 'Two Hundred Club', 'description' => 'Complete 200 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 225, 'name' => 'Unstoppable', 'description' => 'Complete 225 workout entries.', 'unlocked_on' => null, 'is_secret' => false],
            ['threshold' => 250, 'name' => 'Quarter Thousand', 'description' => 'Complete 250 workout entries.', 'unlocked_on' => null, 'is_secret' => true],
        ];
    }
};
?>

<section class="w-full">
    @php($achievements = $this->achievements())
    @php($unlockedCount = collect($achievements)->whereNotNull('unlocked_on')->count())

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

        <div class="grid grid-cols-[repeat(auto-fill,3.125rem)] gap-3" aria-label="Achievement milestones">
            @foreach ($achievements as $achievement)
                <flux:tooltip wire:key="achievement-{{ $achievement['threshold'] }}">
                    <button
                        type="button"
                        @class([
                            'flex size-[50px] items-center justify-center rounded-xl border transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800',
                            'border-amber-500 bg-amber-400 text-amber-950 shadow-sm shadow-amber-400/40' => $achievement['unlocked_on'],
                            'border-zinc-300 bg-zinc-100 text-zinc-400 hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-500 dark:hover:border-zinc-600' => ! $achievement['unlocked_on'],
                        ])
                        aria-label="{{ $achievement['is_secret'] && ! $achievement['unlocked_on'] ? 'Locked mystery achievement' : $achievement['name'] }}"
                    >
                        @if ($achievement['unlocked_on'])
                            <flux:icon.check variant="micro" />
                        @elseif ($achievement['is_secret'])
                            <flux:icon.question-mark-circle variant="micro" />
                        @else
                            <flux:icon.lock-closed variant="micro" />
                        @endif
                    </button>

                    <flux:tooltip.content class="w-64">
                        @if ($achievement['is_secret'] && ! $achievement['unlocked_on'])
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
                                <div @class([
                                    'flex size-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold',
                                    'bg-amber-400 text-amber-950' => $achievement['unlocked_on'],
                                    'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' => ! $achievement['unlocked_on'],
                                ])>{{ $achievement['threshold'] }}</div>
                                <div>
                                    <flux:heading size="sm">{{ $achievement['name'] }}</flux:heading>
                                    <flux:text size="sm" class="mt-1">{{ $achievement['description'] }}</flux:text>
                                    @if ($achievement['unlocked_on'])
                                        <flux:text size="sm" class="mt-3 font-medium text-amber-700 dark:text-amber-300">Unlocked {{ $achievement['unlocked_on'] }}</flux:text>
                                    @else
                                        <flux:text size="sm" class="mt-3">Goal: {{ $achievement['threshold'] }} workout entries</flux:text>
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
