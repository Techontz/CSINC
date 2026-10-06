<?php

namespace App\Models;

use App\Models\Concerns\RevalidatesFrontend;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BookTag extends Model
{
    use RevalidatesFrontend;

    protected $fillable = ['name', 'slug'];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }

    public function revalidationTags(): array
    {
        return ['books'];
    }
}
