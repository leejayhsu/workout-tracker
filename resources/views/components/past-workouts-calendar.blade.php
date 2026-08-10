@blaze

@props([
    'selectableHeader' => null,
    'weekNumbers' => null,
    'unavailable' => null,
    'withInputs' => null,
    'navigation' => null,
    'withToday' => null,
    'months' => null,
    'value' => null,
    'mode' => null,
    'size' => null,
    'name' => null,
])

@php
// We only want to show the name attribute if it has been set manually
// but not if it has been set from the `wire:model` attribute...
$showName = isset($name);

if (! isset($name)) {
    $name = $attributes->whereStartsWith('wire:model')->first();
}

// Support adding the .self modifier to the wire:model directive...
if (($wireModel = $attributes->wire('model')) && $wireModel->directive && ! $wireModel->hasModifier('self')) {
    unset($attributes[$wireModel->directive]);

    $wireModel->directive .= '.self';

    $attributes = $attributes->merge([$wireModel->directive => $wireModel->value]);
}

$months = $months ?? ($mode === 'range' ? 2 : 1);

$range = $mode === 'range';

// Mark it invalid if the property or any of it's nested attributes have errors...
$invalid ??= ($name && ($errors->has($name) || $errors->has($name . '.*')));

$class = Flux::classes()
    ->add('isolate relative')
    ;

$sizeClasses = match ($size) {
    '2xl' => $weekNumbers ? 'size-12 sm:size-16' : 'size-13 sm:size-16',
    'xl' => $weekNumbers ? 'size-11 sm:size-14' : 'size-12 sm:size-14',
    'lg' => $weekNumbers ? 'size-10 sm:size-12' : 'size-11 sm:size-12',
    default => $weekNumbers ? 'size-10 sm:size-11' : 'size-11 sm:size-11',
    'sm' => $weekNumbers ? 'size-9 sm:size-10' : 'size-11 sm:size-10',
    'xs' => $weekNumbers ? 'size-8 sm:size-9' : 'size-10 sm:size-9',
};

// Add support for `$value` being an array, if for example it's coming from
// the `old()` helper or if a user prefers to pass data in as an array...
if (is_array($value)) {
    $value = match (true) {
        $mode === 'range' => isset($value['start']) && isset($value['end']) ? $value['start'] . '/' . $value['end'] : null,
        default => collect($value)->join(','),
    };
}

if (isset($unavailable)) {
    $unavailable = collect($unavailable)->implode(',');
}
@endphp

<ui-calendar
    wire:ignore.children
    {{ $attributes->class($class) }}
    data-past-workouts-calendar
    @if ($mode) mode="{{ $mode }}" @endif
    months="{{ $months }}"
    @if (isset($unavailable) && $unavailable !== '') unavailable="{{ $unavailable }}" @endif
    @if ($showName) name="{{ $name }}" @endif
    @if (isset($value)) value="{{ $value }}" @endif
>
    <?php if ($withInputs): ?>
        <ui-calendar-inputs class="flex items-center p-2 border-b border-zinc-200 dark:border-white/10">
            <?php if ($range): ?>
                <div class="sm:px-2 flex items-center gap-4">
                    <div class="flex items-center gap-2"><span class="max-sm:hidden text-sm font-medium text-zinc-800 dark:text-white">{{ __('Start') }}</span> <flux:input type="date" class="w-[full] sm:w-[11.25rem]" /></div>
                    <div class="flex items-center gap-2"><span class="max-sm:hidden text-sm font-medium text-zinc-800 dark:text-white">{{ __('End') }}</span> <flux:input type="date" class="w-[full] sm:w-[11.25rem]" /></div>
                </div>
            <?php else: ?>
                <flux:input type="date" class="w-full sm:w-[11.25rem]" />
            <?php endif; ?>
        </ui-calendar-inputs>
    <?php endif; ?>

    <div class="relative">
        <div class="z-10 absolute top-0 inset-x-0 p-2">
            <header class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <?php if ($selectableHeader): ?>
                        <ui-calendar-month display="short" class="font-medium text-sm text-zinc-800 dark:text-white">
                            <select
                                class="h-10 py-0 border-0 text-sm sm:h-8 appearance-none rounded-lg bg-zinc-100 dark:bg-white/10 dark:[&>option]:bg-zinc-700 dark:[&>option]:text-white px-3 sm:ps-2 bg-position-[right_.25rem_center]! rtl:bg-position-[left_.25rem_center]! pe-[1.35rem] bg-[length:16px_16px] bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2016%2016%22%20fill=%22%2300000040%22%20class=%22size-4%22%3E%3Cpath%20fill-rule=%22evenodd%22%20d=%22M4.22%206.22a.75.75%200%200%201%201.06%200L8%208.94l2.72-2.72a.75.75%200%201%201%201.06%201.06l-3.25%203.25a.75.75%200%200%201-1.06%200L4.22%207.28a.75.75%200%200%201%200-1.06Z%22%20clip-rule=%22evenodd%22/%3E%3C/svg%3E')] hover:bg-[length:16px_16px] hover:bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2016%2016%22%20fill=%22%231f2937%22%20class=%22size-4%22%3E%3Cpath%20fill-rule=%22evenodd%22%20d=%22M4.22%206.22a.75.75%200%200%201%201.06%200L8%208.94l2.72-2.72a.75.75%200%201%201%201.06%201.06l-3.25%203.25a.75.75%200%200%201-1.06%200L4.22%207.28a.75.75%200%200%201%200-1.06Z%22%20clip-rule=%22evenodd%22/%3E%3C/svg%3E')] dark:bg-[length:16px_16px] dark:bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2016%2016%22%20fill=%22%23ffffff75%22%20class=%22size-4%22%3E%3Cpath%20fill-rule=%22evenodd%22%20d=%22M4.22%206.22a.75.75%200%200%201%201.06%200L8%208.94l2.72-2.72a.75.75%200%201%201%201.06%201.06l-3.25%203.25a.75.75%200%200%201-1.06%200L4.22%207.28a.75.75%200%200%201%200-1.06Z%22%20clip-rule=%22evenodd%22/%3E%3C/svg%3E')] dark:hover:bg-[length:16px_16px] dark:hover:bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2016%2016%22%20fill=%22%23ffffff%22%20class=%22size-4%22%3E%3Cpath%20fill-rule=%22evenodd%22%20d=%22M4.22%206.22a.75.75%200%200%201%201.06%200L8%208.94l2.72-2.72a.75.75%200%201%201%201.06%201.06l-3.25%203.25a.75.75%200%200%201-1.06%200L4.22%207.28a.75.75%200%200%201%200-1.06Z%22%20clip-rule=%22evenodd%22/%3E%3C/svg%3E')] bg-no-repeat"
                            >
                                <template>
                                    <option><slot></slot></option>
                                </template>
                            </select>
                        </ui-calendar-month>

                        <ui-calendar-year class="font-medium text-sm text-zinc-800 dark:text-white">
                            <select
                                class="h-10 py-0 border-0 text-sm sm:h-8 appearance-none rounded-lg bg-zinc-100 dark:bg-white/10 dark:[&>option]:bg-zinc-700 dark:[&>option]:text-white px-3 sm:ps-2 bg-position-[right_.25rem_center]! rtl:bg-position-[left_.25rem_center]! pe-[1.35rem] bg-[length:16px_16px] bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2016%2016%22%20fill=%22%2300000040%22%20class=%22size-4%22%3E%3Cpath%20fill-rule=%22evenodd%22%20d=%22M4.22%206.22a.75.75%200%200%201%201.06%200L8%208.94l2.72-2.72a.75.75%200%201%201%201.06%201.06l-3.25%203.25a.75.75%200%200%201-1.06%200L4.22%207.28a.75.75%200%200%201%200-1.06Z%22%20clip-rule=%22evenodd%22/%3E%3C/svg%3E')] hover:bg-[length:16px_16px] hover:bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2016%2016%22%20fill=%22%231f2937%22%20class=%22size-4%22%3E%3Cpath%20fill-rule=%22evenodd%22%20d=%22M4.22%206.22a.75.75%200%200%201%201.06%200L8%208.94l2.72-2.72a.75.75%200%201%201%201.06%201.06l-3.25%203.25a.75.75%200%200%201-1.06%200L4.22%207.28a.75.75%200%200%201%200-1.06Z%22%20clip-rule=%22evenodd%22/%3E%3C/svg%3E')] dark:bg-[length:16px_16px] dark:bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2016%2016%22%20fill=%22%23ffffff75%22%20class=%22size-4%22%3E%3Cpath%20fill-rule=%22evenodd%22%20d=%22M4.22%206.22a.75.75%200%200%201%201.06%200L8%208.94l2.72-2.72a.75.75%200%201%201%201.06%201.06l-3.25%203.25a.75.75%200%200%201-1.06%200L4.22%207.28a.75.75%200%200%201%200-1.06Z%22%20clip-rule=%22evenodd%22/%3E%3C/svg%3E')] dark:hover:bg-[length:16px_16px] dark:hover:bg-[url('data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2016%2016%22%20fill=%22%23ffffff%22%20class=%22size-4%22%3E%3Cpath%20fill-rule=%22evenodd%22%20d=%22M4.22%206.22a.75.75%200%200%201%201.06%200L8%208.94l2.72-2.72a.75.75%200%201%201%201.06%201.06l-3.25%203.25a.75.75%200%200%201-1.06%200L4.22%207.28a.75.75%200%200%201%200-1.06Z%22%20clip-rule=%22evenodd%22/%3E%3C/svg%3E')] bg-no-repeat"
                            >
                                <template>
                                    <option><slot></slot></option>
                                </template>
                            </select>
                        </ui-calendar-year>
                    <?php endif; ?>
                </div>

                <div class="flex items-center">
                    <?php if ($withToday): ?>
                        <ui-calendar-today class="size-10 sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-white/5 dark:hover:text-white [&[disabled]]:opacity-50 [&[disabled]]:pointer-events-none" aria-label="{{ __('Today') }}">
                            <div class="relative">
                                <template name="today">
                                    <div class="cursor-default absolute inset-0 mt-[3px] flex items-center justify-center text-[.5625rem] font-semibold"><slot></slot></div>
                                </template>

                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.75 2C5.94891 2 6.13968 2.07902 6.28033 2.21967C6.42098 2.36032 6.5 2.55109 6.5 2.75V4H13.5V2.75C13.5 2.55109 13.579 2.36032 13.7197 2.21967C13.8603 2.07902 14.0511 2 14.25 2C14.4489 2 14.6397 2.07902 14.7803 2.21967C14.921 2.36032 15 2.55109 15 2.75V4H15.25C15.9793 4 16.6788 4.28973 17.1945 4.80546C17.7103 5.32118 18 6.02065 18 6.75V15.25C18 15.9793 17.7103 16.6788 17.1945 17.1945C16.6788 17.7103 15.9793 18 15.25 18H4.75C4.02065 18 3.32118 17.7103 2.80546 17.1945C2.28973 16.6788 2 15.9793 2 15.25V6.75C2 6.02065 2.28973 5.32118 2.80546 4.80546C3.32118 4.28973 4.02065 4 4.75 4H5V2.75C5 2.55109 5.07902 2.36032 5.21967 2.21967C5.36032 2.07902 5.55109 2 5.75 2ZM4.75 6.5C4.06 6.5 3.5 7.06 3.5 7.75V15.25C3.5 15.94 4.06 16.5 4.75 16.5H15.25C15.94 16.5 16.5 15.94 16.5 15.25V7.75C16.5 7.06 15.94 6.5 15.25 6.5H4.75Z" fill="currentColor"/>
                                </svg>
                            </div>
                        </ui-calendar-today>
                    <?php endif; ?>

                    <?php if ($navigation !== false): ?>
                        <ui-calendar-previous class="size-10 sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-white/5 dark:hover:text-white [&[disabled]]:opacity-50 [&[disabled]]:pointer-events-none" aria-label="{{ __('Previous month') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 rtl:hidden"> <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /> </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 hidden rtl:block"> <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /> </svg>
                        </ui-calendar-previous>

                        <ui-calendar-next class="size-10 sm:size-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-white/5 dark:hover:text-white [&[disabled]]:opacity-50 [&[disabled]]:pointer-events-none [&[disabled]_&]:text-zinc-400" aria-label="{{ __('Next month') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 rtl:hidden"> <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /> </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 hidden rtl:block"> <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /> </svg>
                        </ui-calendar-next>
                    <?php endif; ?>
                </div>
            </header>
        </div>
    </div>

    <ui-calendar-months class="relative flex justify-center p-2 gap-4">
        <template name="month">
            <div>
                <template name="heading">
                    <div class="@if ($selectableHeader) [[data-month]:first-of-type_&]:opacity-0 @endif mb-2 px-2 h-10 sm:h-8 flex items-center">
                        <div class="font-medium text-sm text-zinc-800 dark:text-white"><slot></slot></div>
                    </div>
                </template>

                <table>
                    <thead>
                        <tr class="flex w-full">
                            <?php if ($weekNumbers): ?>
                                <th scope="col" class="{{ $sizeClasses }} text-sm font-medium text-zinc-500 dark:text-zinc-300 flex items-center"><div class="w-full">#</div></th>
                            <?php endif; ?>

                            <template name="weekday">
                                <th scope="col" class="{{ $sizeClasses }} text-sm font-medium text-zinc-500 dark:text-zinc-300 flex items-center"><div class="w-full"><slot></slot></div></th>
                            </template>
                        </tr>
                    </thead>

                    <tbody>
                        <template name="week">
                            <tr class="flex w-full not-first-of-type:mt-1 [&:first-of-type_td[data-in-range]:not([data-selected]):first-child]:rounded-s-none [&:last-of-type_td[data-in-range]:not([data-selected]):last-child]:rounded-e-none">
                                <?php if ($weekNumbers): ?>
                                    <template name="number">
                                        <td class="p-0 relative {{ $sizeClasses }} text-xs font-medium text-zinc-400 flex items-center justify-center">
                                            <slot></slot>
                                        </td>
                                    </template>
                                <?php endif; ?>
                                <template name="day">
                                    <?php if ($attributes->has('static')): ?>
                                        <td class="p-0 data-unavailable:line-through">
                                            <div class="relative isolate {{ $sizeClasses }} text-sm font-medium text-zinc-800 dark:text-white flex items-center justify-center [td[data-selected]_&[disabled]]:opacity-50 [td[disabled]_&]:text-zinc-400 [td[disabled]_&]:pointer-events-none [td[disabled]_&]:cursor-default">
                                                <svg data-past-workout-marker aria-hidden="true" viewBox="0 0 40 40" fill="none" class="pointer-events-none absolute inset-1.5 hidden size-[calc(100%-0.75rem)] overflow-visible text-red-600 dark:text-red-500 [td[data-selected]_&]:block">
                                                    <defs>
                                                        <filter id="past-workouts-marker-roughness" x="-20%" y="-20%" width="140%" height="140%">
                                                            <feTurbulence type="fractalNoise" baseFrequency="0.14" numOctaves="2" seed="31" result="noise" />
                                                            <feDisplacementMap in="SourceGraphic" in2="noise" scale="3.6" xChannelSelector="R" yChannelSelector="G" />
                                                        </filter>
                                                    </defs>
                                                    <g filter="url(#past-workouts-marker-roughness)" stroke-width="3.8">
                                                    <path class="hidden [td[data-date$='-01'][data-selected]_&]:block" d="M20 4.3C28.7 3.8 36 10.5 35.3 20.1C34.8 29.2 28.3 35.9 19.8 35.2C10.4 34.8 4.1 28.7 4.7 19.5C5.1 10.4 11.5 4.8 20 4.3Z" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-02'][data-selected]_&]:block" d="M20.2 4.8C29.9 3.9 35.7 11.4 35 20.4C34.5 29.7 27.6 35.2 19.2 35.4C10 35.3 4.6 28.1 5.2 19.3C5.7 10.2 11.3 5.5 20.2 4.8Z" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-03'][data-selected]_&]:block" d="M19.3 4.5C27.9 4 35.1 10.1 35.6 19.2C36 28.3 29 35.6 20.2 35.3C10.9 35.1 4.6 28.5 4.5 19.8C4.5 10.9 10.8 5.2 19.3 4.5Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-04'][data-selected]_&]:block" d="M20.7 4.2C29.7 4.8 35.8 11.1 35.1 20.5C34.5 29.4 28.9 35.8 19.9 35.5C10.4 35.1 4.1 28.4 4.9 19.4C5.7 10.6 11.5 3.7 20.7 4.2Z" stroke="currentColor" stroke-width="3.3" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-05'][data-selected]_&]:block" d="M19.7 4.7C28.7 3.9 35.3 10.6 35.5 19.5C35.6 28.9 29 35.3 20.1 35C11.1 34.8 4.4 28.2 4.8 19.6C5.3 10.5 10.8 5.2 19.7 4.7Z" stroke="currentColor" stroke-width="3.1" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-06'][data-selected]_&]:block" d="M20.5 4.4C29.5 4.5 35.4 11 35.2 19.9C35.1 28.8 28.7 35.6 19.4 35.4C10.5 35.2 4.2 28.4 4.6 19.3C5 10.4 11.4 4.6 20.5 4.4Z" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-07'][data-selected]_&]:block" d="M20 4.1C28.8 4.7 35.7 10.6 35.4 19.7C35.1 28.7 28.5 35.1 19.5 35.5C10.2 35.8 4.1 28.5 4.5 19.8C4.9 10.9 11 3.7 20 4.1Z" stroke="currentColor" stroke-width="2.9" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-08'][data-selected]_&]:block" d="M19.4 4.6C28.2 3.8 35.5 10.5 35.3 19.3C35.2 28.3 28.8 35.9 19.9 35.3C10.8 34.8 4.5 28.9 4.9 19.4C5.2 10.9 10.7 5.4 19.4 4.6Z" stroke="currentColor" stroke-width="3.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-09'][data-selected]_&]:block" d="M20.2 4.3C29.4 4.1 35.7 10.8 35.1 20C34.6 29.2 28.7 35.4 19.4 35.5C10.4 35.5 4.3 28.4 4.8 19.4C5.1 10.5 11.1 4.5 20.2 4.3Z" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-10'][data-selected]_&]:block" d="M19.6 4.4C28.8 4.7 35.2 11.2 35.5 20C35.9 29.1 29 35.4 20.3 35.2C11.1 35 4.2 28.7 4.6 19.8C5 10.7 10.6 4.3 19.6 4.4Z" stroke="currentColor" stroke-width="3.3" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-11'][data-selected]_&]:block" d="M20.6 4.6C29.1 4 35.5 10.8 35.2 19.6C34.8 29 28.4 35.4 19.6 35.1C10.4 34.9 4.2 28.3 4.7 19.5C5.1 10.4 11.6 5 20.6 4.6Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-12'][data-selected]_&]:block" d="M19.8 4.2C29 4.5 35.3 10.5 35.5 19.7C35.7 28.8 29 35.7 20.1 35.4C11 35.2 4.7 28.5 4.5 19.6C4.3 10.4 10.8 3.9 19.8 4.2Z" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-13'][data-selected]_&]:block" d="M20.3 4.7C29.2 4.2 35.4 10.9 35.4 19.9C35.3 29.2 28.6 35.3 19.8 35.5C10.8 35.6 4.4 28.4 4.8 19.2C5.3 10.4 11.2 5.2 20.3 4.7Z" stroke="currentColor" stroke-width="3.1" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-14'][data-selected]_&]:block" d="M19.5 4.5C28.5 3.9 35.7 10.8 35.1 19.5C34.6 28.8 28.9 35.3 19.5 35.2C10.7 35.1 4.5 28.2 4.9 19.4C5.4 10.7 10.8 4.8 19.5 4.5Z" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-15'][data-selected]_&]:block" d="M20.1 4.4C29.5 4.6 35.7 11 35.2 20.2C34.7 29.4 28.4 35.6 19.6 35.2C10.4 34.8 4.1 28.6 4.7 19.5C5.3 10.7 11.2 4.3 20.1 4.4Z" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-16'][data-selected]_&]:block" d="M19.7 4.6C28.8 4 35.3 10.7 35.6 19.8C35.9 28.8 29.1 35.6 20.2 35.3C10.8 35.1 4.6 28.5 4.4 19.5C4.3 10.7 10.7 5.1 19.7 4.6Z" stroke="currentColor" stroke-width="3.3" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-17'][data-selected]_&]:block" d="M20.4 4.3C29.2 4.5 35.5 10.6 35.3 19.7C35.1 28.9 28.5 35.3 19.4 35.5C10.3 35.7 4.3 28.4 4.6 19.4C5 10.3 11.4 4.1 20.4 4.3Z" stroke="currentColor" stroke-width="2.9" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-18'][data-selected]_&]:block" d="M19.4 4.4C28.6 4.1 35.6 10.9 35.2 19.8C34.8 29.1 28.9 35.5 19.8 35.3C10.6 35 4.4 28.6 4.8 19.5C5.3 10.2 10.7 4.7 19.4 4.4Z" stroke="currentColor" stroke-width="3.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-19'][data-selected]_&]:block" d="M20.2 4.7C29 3.9 35.5 10.5 35.4 19.6C35.3 28.9 28.8 35.7 19.9 35.2C10.8 34.8 4.5 28.5 4.7 19.5C5 10.5 11.2 5.2 20.2 4.7Z" stroke="currentColor" stroke-width="3.1" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-20'][data-selected]_&]:block" d="M19.6 4.2C28.9 4.3 35.5 10.9 35.2 19.9C34.8 29 28.5 35.5 19.6 35.4C10.5 35.2 4.1 28.5 4.6 19.6C5.1 10.3 10.6 4 19.6 4.2Z" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-21'][data-selected]_&]:block" d="M20.5 4.5C29.3 4 35.3 10.8 35.5 19.7C35.7 28.9 28.9 35.6 19.8 35.3C10.9 35.1 4.4 28.7 4.8 19.6C5.1 10.7 11.4 4.8 20.5 4.5Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-22'][data-selected]_&]:block" d="M19.5 4.6C28.6 3.8 35.6 10.8 35.2 19.7C34.8 28.8 28.8 35.3 19.6 35.5C10.5 35.7 4.3 28.3 4.7 19.4C5.1 10.4 10.6 5.1 19.5 4.6Z" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-23'][data-selected]_&]:block" d="M20.1 4.3C29.2 4.8 35.6 10.6 35.2 19.8C34.9 29 28.3 35.4 19.4 35.3C10.3 35.1 4.2 28.5 4.6 19.5C5 10.6 11.1 3.9 20.1 4.3Z" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-24'][data-selected]_&]:block" d="M19.8 4.7C28.7 4.1 35.4 10.9 35.4 19.9C35.5 29.1 29 35.5 20.1 35.1C11 34.7 4.4 28.4 4.8 19.5C5.2 10.4 11 5.1 19.8 4.7Z" stroke="currentColor" stroke-width="3.3" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-25'][data-selected]_&]:block" d="M20.4 4.5C29.2 4.2 35.5 10.6 35.3 19.6C35.1 28.8 28.7 35.6 19.5 35.4C10.6 35.2 4.2 28.5 4.5 19.4C4.8 10.4 11.3 4.7 20.4 4.5Z" stroke="currentColor" stroke-width="2.9" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-26'][data-selected]_&]:block" d="M19.3 4.4C28.5 3.9 35.4 10.7 35.5 19.5C35.6 28.9 28.9 35.4 19.9 35.2C10.7 35 4.5 28.4 4.9 19.5C5.3 10.5 10.5 4.8 19.3 4.4Z" stroke="currentColor" stroke-width="3.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-27'][data-selected]_&]:block" d="M20.3 4.6C29.1 4.1 35.3 10.6 35.4 19.8C35.5 29 28.8 35.6 19.8 35.3C10.7 35.1 4.3 28.7 4.7 19.4C5 10.4 11.3 5.1 20.3 4.6Z" stroke="currentColor" stroke-width="3.1" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-28'][data-selected]_&]:block" d="M19.6 4.3C28.9 4.5 35.7 10.9 35.3 19.8C34.9 28.9 28.4 35.4 19.5 35.5C10.4 35.6 4.2 28.2 4.6 19.4C5 10.4 10.7 4.1 19.6 4.3Z" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-29'][data-selected]_&]:block" d="M20.1 4.5C29.3 4 35.4 10.9 35.2 19.7C35 28.9 28.6 35.5 19.7 35.2C10.5 34.9 4.4 28.4 4.8 19.4C5.2 10.6 11.2 4.9 20.1 4.5Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-30'][data-selected]_&]:block" d="M19.4 4.6C28.3 3.8 35.5 10.5 35.5 19.5C35.6 28.9 28.9 35.3 19.9 35.4C10.8 35.5 4.4 28.5 4.7 19.5C5 10.5 10.7 5.1 19.4 4.6Z" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="hidden [td[data-date$='-31'][data-selected]_&]:block" d="M20.4 4.4C29.1 4.4 35.5 10.8 35.3 19.9C35.1 29.1 28.7 35.4 19.5 35.3C10.4 35.1 4.1 28.4 4.6 19.4C5.1 10.3 11.4 4.2 20.4 4.4Z" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </g>
                                                </svg>
                                                <div class="absolute inset-0 hidden [td[data-today]_&]:flex justify-center items-end"><div class="mb-1 size-1 rounded-full bg-zinc-800 dark:bg-white [td[data-selected]_&]:bg-white dark:[td[data-selected]_&]:bg-zinc-800"></div></div>
                                                <span class="relative z-10"><slot></slot></span>
                                            </div>
                                        </td>
                                    <?php else: ?>
                                        <td class="_max-sm:data-outside:opacity-0 p-0 data-unavailable:line-through data-in-range:bg-zinc-100 dark:data-in-range:bg-white/10 data-start:rounded-s-lg data-end:rounded-e-lg data-end-preview:rounded-e-lg first-of-type:rounded-s-lg last-of-type:rounded-e-lg [&[data-selected]+[data-selected]]:rounded-s-none [[data-in-range]:not([data-selected]):not([data-end-preview])+&[data-outside]]:bg-linear-to-r [&[data-outside]:has(+[data-in-range])]:bg-linear-to-l rtl:[[data-in-range]:not([data-selected]):not([data-end-preview])+&[data-outside]]:bg-linear-to-l rtl:[&[data-outside]:has(+[data-in-range])]:bg-linear-to-r data-outside:opacity-50 from-zinc-100 dark:from-white/10 from-1% [&[data-outside]:has(+[data-in-range][data-selected])]:bg-none!">
                                            <ui-tooltip position="top">
                                                <button type="button" class="{{ $sizeClasses }} text-sm font-medium text-zinc-800 dark:text-white flex flex-col items-center justify-center rounded-lg hover:bg-zinc-800/5 dark:hover:bg-white/5 [td[data-selected]:has(+td[data-selected])_&]:rounded-e-none [td[data-selected]+td[data-selected]_&]:rounded-s-none [td[data-selected]_&]:bg-[var(--color-accent)] [td[data-selected]_&]:text-[var(--color-accent-foreground)] [td[data-selected]_&[disabled]]:opacity-50 disabled:text-zinc-400 disabled:pointer-events-none disabled:cursor-default [[readonly]_&]:pointer-events-none [[readonly]_&]:cursor-default [[readonly]_&]:bg-transparent">
                                                    <div class="relative">
                                                        <div class="absolute inset-x-0 bottom-[-3px] hidden [td[data-today]_&]:flex justify-center items-end"><div class="size-1 rounded-full bg-zinc-800 dark:bg-white [td[data-selected]_&]:bg-white dark:[td[data-selected]_&]:bg-zinc-800"></div></div>

                                                        <div><slot></slot></div>

                                                        <template name="subtext">
                                                            <div class="absolute inset-x-0 bottom-[-1rem] flex justify-center font-medium text-xs text-zinc-400 dark:text-zinc-500 [[data-date-variant='success']_&]:text-lime-600 dark:[[data-date-variant='success']_&]:text-lime-400 [[data-date-variant='warning']_&]:text-yellow-600 dark:[[data-date-variant='warning']_&]:text-yellow-400 [[data-date-variant='danger']_&]:text-rose-500 dark:[[data-date-variant='danger']_&]:text-rose-400">
                                                                <slot></slot>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </button>

                                                <template name="details">
                                                    <div popover="manual" class="relative py-2 px-2.5 rounded-md text-xs text-white font-medium bg-zinc-800 dark:bg-zinc-700 dark:border dark:border-white/10 p-0 overflow-visible">
                                                        <slot></slot>
                                                    </div>
                                                </template>
                                            </ui-tooltip>
                                        </td>
                                    <?php endif; ?>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </ui-calendar-months>
</ui-calendar>
