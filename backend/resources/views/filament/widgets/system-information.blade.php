<x-filament-widgets::widget>
    <x-filament::section heading="System" icon="heroicon-o-server-stack">
        <dl class="csi-kv">
            @foreach ($items as $label => $value)
                <dt>{{ $label }}</dt>
                <dd>{{ $value }}</dd>
            @endforeach
        </dl>
    </x-filament::section>
</x-filament-widgets::widget>
