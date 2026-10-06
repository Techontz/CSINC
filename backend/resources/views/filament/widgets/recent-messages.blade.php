<x-filament-widgets::widget>
    <x-filament::section heading="Latest inquiries" icon="heroicon-o-inbox">
        <x-slot name="afterHeader">
            <x-filament::link :href="$indexUrl" size="sm" icon="heroicon-m-arrow-right" icon-position="after">Inbox</x-filament::link>
        </x-slot>
        @forelse ($messages as $message)
            <a href="{{ $viewUrl($message) }}" wire:navigate class="csi-list-row group">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                    {{ mb_strtoupper(mb_substr($message->first_name, 0, 1).mb_substr((string) $message->last_name, 0, 1)) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2">
                        <span @class(['truncate text-sm text-slate-800', 'font-semibold' => $message->status === \App\Enums\MessageStatus::New])>{{ $message->full_name }}</span>
                        <span class="shrink-0 text-xs text-slate-400">{{ $message->created_at->diffForHumans(short: true) }}</span>
                    </span>
                    <span class="block truncate text-xs text-slate-500">{{ $message->subject }} — {{ \Illuminate\Support\Str::limit($message->message, 60) }}</span>
                </span>
                <x-filament::badge :color="$message->status->getColor()" size="sm">{{ $message->status->getLabel() }}</x-filament::badge>
            </a>
        @empty
            <div class="py-8 text-center">
                <x-filament::icon icon="heroicon-o-inbox" class="mx-auto h-8 w-8 text-slate-300" />
                <p class="mt-2 text-sm text-slate-500">No inquiries yet. Submissions from the contact form appear here.</p>
            </div>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
