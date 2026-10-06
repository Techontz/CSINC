<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid', 'number', 'access_token', 'customer_name', 'customer_email', 'status',
        'subtotal_cents', 'total_cents', 'currency', 'stripe_session_id', 'stripe_payment_intent',
        'terms_accepted_at', 'paid_at', 'fulfilled_at', 'ip_address', 'internal_notes',
    ];

    protected $hidden = ['access_token'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal_cents' => 'integer',
            'total_cents' => 'integer',
            'terms_accepted_at' => 'datetime',
            'paid_at' => 'datetime',
            'fulfilled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->uuid ??= (string) Str::uuid();
            $order->access_token ??= Str::random(48);
            $order->number ??= 'CS-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function hasValidAccessToken(?string $token): bool
    {
        return is_string($token) && $token !== '' && hash_equals($this->access_token, $token);
    }
}
