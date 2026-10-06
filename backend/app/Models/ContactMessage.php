<?php

namespace App\Models;

use App\Enums\MessageStatus;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'company', 'subject', 'message', 'details', 'source',
        'status', 'read_at', 'replied_at', 'handled_by', 'internal_notes', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'status' => MessageStatus::class,
            'details' => 'array',
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim($this->first_name.' '.$this->last_name));
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', MessageStatus::New);
    }

    public function markAs(MessageStatus $status, ?User $by = null): void
    {
        $this->status = $status;
        $this->handled_by = $by?->getKey() ?? $this->handled_by;

        if ($status !== MessageStatus::New) {
            $this->read_at ??= now();
        }

        if ($status === MessageStatus::Replied) {
            $this->replied_at ??= now();
        }

        $this->save();
    }
}
