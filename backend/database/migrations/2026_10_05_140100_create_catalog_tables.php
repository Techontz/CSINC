<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('book_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->string('author')->nullable();
            $table->json('co_authors')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('sku', 64)->nullable()->unique();
            $table->string('isbn', 32)->nullable();
            $table->string('publisher')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('edition')->nullable();
            $table->string('language', 32)->default('English');
            $table->unsignedInteger('page_count')->nullable();
            $table->string('format', 64)->nullable();
            $table->unsignedBigInteger('price_cents')->nullable();
            $table->unsignedBigInteger('sale_price_cents')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->string('external_purchase_url')->nullable();
            $table->foreignId('cover_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('sample_path')->nullable();
            $table->string('status', 16)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'sort_order']);
            $table->index('sort_order');
        });

        Schema::create('book_book_category', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['book_id', 'book_category_id']);
            $table->index('book_category_id');
        });

        Schema::create('book_book_tag', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['book_id', 'book_tag_id']);
            $table->index('book_tag_id');
        });

        Schema::create('book_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unique(['book_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_media');
        Schema::dropIfExists('book_book_tag');
        Schema::dropIfExists('book_book_category');
        Schema::dropIfExists('books');
        Schema::dropIfExists('book_tags');
        Schema::dropIfExists('book_categories');
    }
};
