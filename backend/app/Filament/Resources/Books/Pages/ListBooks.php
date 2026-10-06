<?php

namespace App\Filament\Resources\Books\Pages;

use App\Enums\BookStatus;
use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBooks extends ListRecords
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New product')->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        $counts = Book::query()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

        return [
            'all' => Tab::make('All')->badge($counts->sum()),
            'published' => Tab::make('Published')
                ->badge($counts[BookStatus::Published->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BookStatus::Published)),
            'draft' => Tab::make('Drafts')
                ->badge($counts[BookStatus::Draft->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BookStatus::Draft)),
            'archived' => Tab::make('Archived')
                ->badge($counts[BookStatus::Archived->value] ?? 0)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BookStatus::Archived)),
            'missing_file' => Tab::make('Missing file')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(Book::query()->whereNull('file_path')->whereNotNull('price_cents')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('file_path')->whereNotNull('price_cents')),
        ];
    }
}
