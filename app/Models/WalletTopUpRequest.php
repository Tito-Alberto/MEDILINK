<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTopUpRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_account_id',
        'user_id',
        'pharmacy_id',
        'amount',
        'status',
        'reference_code',
        'payment_reference',
        'notes',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'handled_at' => 'datetime',
    ];

    public function walletAccount()
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}

