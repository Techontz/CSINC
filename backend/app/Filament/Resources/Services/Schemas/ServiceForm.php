<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ServiceGroup;
use App\Filament\Forms\MediaPicker;
use App\Models\Service;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Service')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(190)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation): void {
                                    if ($operation === 'create' && (blank($get('slug')) || $get('slug') === Str::slug((string) $old))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->required()
                                ->alphaDash()
                                ->unique(Service::class, 'slug', ignoreRecord: true)
                                ->helperText('Used as the on-page anchor, e.g. /services#business-formation.'),
                            Textarea::make('summary')->required()->rows(4)->maxLength(600),
                            TagsInput::make('highlights')
                                ->placeholder('Add highlight')
                                ->helperText('Short labels shown under the description, e.g. “EIN Registration”.'),
                            RichEditor::make('body')
                                ->label('Additional detail (optional)')
                                ->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Placement')
                        ->schema([
                            Select::make('group')->options(ServiceGroup::class)->required()->selectablePlaceholder(false)->default(ServiceGroup::Business),
                            Toggle::make('is_published')->label('Published')->default(true),
                            TextInput::make('sort_order')->label('Display order')->numeric()->minValue(0)->default(0),
                        ]),
                    Section::make('Image')->schema([MediaPicker::make('image_id', 'site')->hiddenLabel()]),
                    Section::make('Link')
                        ->schema([
                            TextInput::make('link_label')->maxLength(60),
                            TextInput::make('link_url')->maxLength(255)->regex('/^(\/|https?:\/\/|mailto:|#)/')->requiredWith('link_label'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
