<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'balance'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByUserAndCurrency(Builder $query, User $user, string $currency)
    {
        return $query->where('user_id', $user->id)->where('currency', $currency);
    }

    public function hasSufficientFunds(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function withdraw(float $amount)
    {
        if (!$this->hasSufficientFunds($amount)) {
            throw new \Exception('Insufficient funds');
        }

        $this->decrement('balance', $amount);
    }

    public function deposit(float $amount)
    {
        $this->increment('balance', $amount);
    }
}
