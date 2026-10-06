<x-filament-widgets::widget>
    <x-filament::section heading="Catalogue health" description="Issues that affect what customers can buy" icon="heroicon-o-heart">
        <div class="space-y-1">
            @unless ($paymentsConfigured)
                <div class="csi-alert mb-3">
                    <x-filament::icon icon="heroicon-m-credit-card" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>Online payments are not configured. Add your Stripe keys to the server environment to enable checkout.</span>
                </div>
            @endunless
            @foreach ($checks as $check)
                <a href="{{ $check['url'] }}" wire:navigate class="csi-list-row group rounded-md px-1 hover:bg-slate-50">
                    <span @class([
                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-sm font-semibold',
                        'bg-emerald-50 text-emerald-700' => $check['count'] === 0,
                        'bg-amber-50 text-amber-700' => $check['count'] > 0,
                    ])>{{ $check['count'] }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-slate-800">{{ $check['label'] }}</span>
                        <span class="block text-xs text-slate-500">{{ $check['help'] }}</span>
                    </span>
                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-slate-500" />
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
