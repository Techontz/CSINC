<?php

namespace App\Http\Requests\Api\Admin;

use App\Enums\BookStatus;
use App\Media\MediaUploader;
use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $book = $this->route('book');

        return $book instanceof Book
            ? $this->user()->can('update', $book)
            : $this->user()->can('create', Book::class);
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('title') && ! $this->route('book')) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $book = $this->route('book');
        $required = $book ? 'sometimes' : 'required';
        $imageMimes = implode(',', array_map(fn (string $mime): string => str($mime)->after('/')->toString(), MediaUploader::IMAGE_MIMES));

        return [
            'title' => [$required, 'string', 'max:190'],
            'slug' => [$required, 'string', 'max:190', 'alpha_dash', Rule::unique('books', 'slug')->ignore($book)],
            'subtitle' => ['nullable', 'string', 'max:190'],
            'author' => ['nullable', 'string', 'max:190'],
            'co_authors' => ['nullable', 'array', 'max:10'],
            'co_authors.*' => ['string', 'max:190'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:100000'],
            'sku' => ['nullable', 'string', 'max:64', Rule::unique('books', 'sku')->ignore($book)],
            'isbn' => ['nullable', 'string', 'max:32', 'regex:/^[0-9Xx\-\s]{10,17}$/'],
            'publisher' => ['nullable', 'string', 'max:190'],
            'publication_date' => ['nullable', 'date'],
            'edition' => ['nullable', 'string', 'max:100'],
            'language' => ['nullable', 'string', 'max:32'],
            'page_count' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'format' => ['nullable', 'string', 'max:64'],
            'price_cents' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'sale_price_cents' => ['nullable', 'integer', 'min:0', 'lte:price_cents'],
            'currency' => ['nullable', 'string', 'size:3', 'alpha'],
            'external_purchase_url' => ['nullable', 'url:https,http', 'max:255'],
            'status' => ['nullable', Rule::enum(BookStatus::class)],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:book_categories,id'],
            'tags' => ['nullable', 'array', 'max:30'],
            'tags.*' => ['string', 'max:60'],
            'cover' => ['nullable', 'file', 'mimes:'.$imageMimes, 'mimetypes:'.implode(',', MediaUploader::IMAGE_MIMES), 'max:'.MediaUploader::MAX_IMAGE_KB, 'dimensions:min_width=200,min_height=200'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.MediaUploader::MAX_DOCUMENT_KB],
            'sample' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.MediaUploader::MAX_DOCUMENT_KB],
        ];
    }
}
