<?php

namespace App\Models;

use App\Services\OrderWalletReversalService;
use App\Services\OrderStockRestorationService;
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
            app(OrderStockRestorationService::class)->restoreOrderStock($order);
        });
    }

    protected $fillable = [
        'customer_user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_nif',
        'notes',
        'subtotal',
        'delivery_fee',
        'tax_amount',
        'total',
        'status',
        'invoice_number',
        'invoice_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'invoice_date' => 'datetime',
        'customer_confirmed_notified_at' => 'datetime',
        'customer_confirmed_seen_at' => 'datetime',
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
