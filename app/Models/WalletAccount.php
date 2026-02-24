<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'label',
        'currency',
        'balance',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function topUpRequests()
    {
        return $this->hasMany(WalletTopUpRequest::class);
    }

    public function withdrawRequests()
    {
        return $this->hasMany(WalletWithdrawRequest::class);
    }

    public function userOwner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function pharmacyOwner()
    {
        return $this->belongsTo(Pharmacy::class, 'owner_id');
    }
}

