@props(['achievement', 'unlockedOn', 'toggleable' => false])

<flux:tooltip :toggleable="$toggleable" {{ $attributes }}>
    <button
        type="button"
        @class([
            'flex size-[100px] items-center justify-center rounded-xl border transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800',
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
