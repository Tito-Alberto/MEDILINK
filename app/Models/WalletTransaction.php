<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_account_id',
        'direction',
        'category',
        'amount',
        'balance_after',
        'status',
        'reference_code',
        'description',
        'meta',
        'performed_by',
        'related_type',
        'related_id',
        'posted_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
        'posted_at' => 'datetime',
    ];

    public function walletAccount()
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}

