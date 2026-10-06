<x-filament-widgets::widget>
    <div class="csi-welcome">
        <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="csi-welcome-eyebrow">{{ $date }}</p>
                <h2 class="csi-welcome-heading">{{ $greeting }}, {{ $name }}.</h2>
                <p class="csi-welcome-body">Here is what is happening across the CSinc91 website, catalogue and inbox today.</p>
            </div>
            @if (count($actions))
                <div class="grid grid-cols-2 gap-3 xl:flex xl:flex-wrap xl:justify-end">
                    @foreach ($actions as $action)
                        <a href="{{ $action['url'] }}" class="csi-quick-action" wire:navigate>
                            <x-filament::icon :icon="$action['icon']" class="h-5 w-5 text-[#e9d3a8]" />
                            <span class="whitespace-nowrap">{{ $action['label'] }}</span>
                            @if (! empty($action['badge']))
                                <span class="ms-auto rounded-sm bg-[#c08a2e] px-1.5 text-xs font-semibold text-[#001c35]">{{ $action['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        <svg class="pointer-events-none absolute -right-16 -top-24 h-80 w-80 opacity-[0.07]" viewBox="0 0 200 200" aria-hidden="true">
            <circle cx="100" cy="100" r="98" fill="none" stroke="#fff" stroke-width="1" />
            <circle cx="100" cy="100" r="70" fill="none" stroke="#fff" stroke-width="1" />
            <circle cx="100" cy="100" r="42" fill="none" stroke="#fff" stroke-width="1" />
        </svg>
    </div>
</x-filament-widgets::widget>
