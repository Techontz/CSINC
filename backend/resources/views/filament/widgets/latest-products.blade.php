<x-filament-widgets::widget>
    <x-filament::section heading="Recently updated products" icon="heroicon-o-book-open">
        <x-slot name="afterHeader">
            <x-filament::link :href="$indexUrl" size="sm" icon="heroicon-m-arrow-right" icon-position="after">All products</x-filament::link>
        </x-slot>
        @forelse ($books as $book)
            <a href="{{ $editUrl($book) }}" wire:navigate class="csi-list-row group">
                @if ($book->cover)
                    <img src="{{ $book->cover->url }}" alt="" class="csi-cover-thumb" loading="lazy">
                @else
                    <span class="csi-cover-thumb flex items-center justify-center bg-slate-100"><x-filament::icon icon="heroicon-o-photo" class="h-4 w-4 text-slate-400" /></span>
                @endif
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-slate-800 group-hover:text-[#00294c]">{{ $book->title }}</span>
                    <span class="block text-xs text-slate-500">Updated {{ $book->updated_at->diffForHumans() }}</span>
                </span>
                <x-filament::badge :color="$book->status->getColor()">{{ $book->status->getLabel() }}</x-filament::badge>
            </a>
        @empty
            <p class="py-6 text-center text-sm text-slate-500">No products yet.</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
