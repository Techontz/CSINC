<x-filament-widgets::widget>
    <x-filament::section heading="Recent activity" icon="heroicon-o-clock">
        <ol class="relative ms-2 border-s border-slate-200">
            @forelse ($entries as $entry)
                <li class="mb-4 ms-5 last:mb-0">
                    <span class="absolute -start-[5px] mt-1.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-[#c08a2e]"></span>
                    <p class="text-sm text-slate-700">
                        <span class="font-medium text-slate-900">{{ $entry->user?->name ?? 'System' }}</span>
                        {{ $entry->description }}
                    </p>
                    <time class="text-xs text-slate-400" datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->diffForHumans() }}</time>
                </li>
            @empty
                <li class="ms-5 py-4 text-sm text-slate-500">Changes made in the CMS will be listed here.</li>
            @endforelse
        </ol>
    </x-filament::section>
</x-filament-widgets::widget>
