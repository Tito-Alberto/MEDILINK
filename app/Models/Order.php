<?php

namespace App\Models;

use App\Services\OrderWalletReversalService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::updated(function (self $order) {
            if (! $order->wasChanged('status')) {
                return;
            }

            $newStatus = self::normalizeStatus((string) $order->status);
            $oldStatus = self::normalizeStatus((string) $order->getOriginal('status'));

            if (! self::isCancellationStatus($newStatus) || self::isCancellationStatus($oldStatus)) {
                return;
            }

            app(OrderWalletReversalService::class)->reverseOrderCredits($order);
        });
    }

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'subtotal',
        'delivery_fee',
        'total',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    private static function normalizeStatus(string $status): string
    {
        return mb_strtolower(trim($status));
    }

    private static function isCancellationStatus(string $status): bool
    {
        return in_array($status, [
            'cancelado',
            'cancelada',
            'cancelled',
            'canceled',
            'rejeitado',
            'rejeitada',
            'rejected',
            'recusado',
            'recusada',
        ], true);
    }
}
