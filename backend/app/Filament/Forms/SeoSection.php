<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

/**
 * Search & social metadata with a live search-result preview.
 */
class SeoSection
{
    /**
     * @param  callable(Get): string  $path  Builds the public path for the preview.
     */
    public static function make(callable $path, string $titleField = 'title', string $descriptionFallbackField = 'short_description', ?string $imageField = 'og_image_id'): Section
    {
        $components = [
            TextInput::make('seo_title')
                ->label('Meta title')
                ->maxLength(190)
                ->live(debounce: 400)
                ->helperText(fn (?string $state): string => mb_strlen((string) $state).' / 60 characters recommended. Leave empty to use the title.'),
            Textarea::make('seo_description')
                ->label('Meta description')
                ->rows(3)
                ->maxLength(320)
                ->live(debounce: 400)
                ->helperText(fn (?string $state): string => mb_strlen((string) $state).' / 155 characters recommended. Leave empty to generate automatically.'),
        ];

        if ($imageField) {
            $components[] = MediaPicker::make($imageField, 'seo')
                ->label('Social sharing image')
                ->helperText('Shown when shared on social networks. Defaults to the cover / site image. Ideal size 1200×630.');
        }

        $components[] = TextEntry::make('serp_preview')
            ->label('Search preview')
            ->state(function (Get $get) use ($path, $titleField, $descriptionFallbackField): HtmlString {
                $title = $get('seo_title') ?: $get($titleField) ?: 'Untitled';
                $description = $get('seo_description') ?: $get($descriptionFallbackField) ?: 'A description will be generated from the content.';
                $url = rtrim((string) config('services.frontend.url'), '/').$path($get);

                return new HtmlString(
                    '<div class="csi-serp"><div class="csi-serp-url">'.e($url).'</div>'
                    .'<div class="csi-serp-title">'.e(str($title)->limit(65)).' — CSinc91</div>'
                    .'<div class="csi-serp-desc">'.e(str(strip_tags((string) $description))->limit(160)).'</div></div>'
                );
            });

        return Section::make('Search & sharing')
            ->description('Control how this appears on Google and social media.')
            ->icon('heroicon-o-magnifying-glass')
            ->collapsible()
            ->schema($components);
    }
}
