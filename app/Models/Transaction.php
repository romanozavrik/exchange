<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'seller_id',
        'buyer_id',
        'currency_from',
        'currency_to',
        'amount_from',
        'amount_to',
        'system_fee'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class);
    }

    public function apply(User $buyer)
    {
        if ($this->buyer_id !== null) {
            throw new \Exception('Transaction has already been applied');
        }

        $this->validateCurrency($this->currency_from, $this->amount_from, $this->seller);
        $this->validateCurrency($this->currency_to, $this->amount_to, $buyer);

        $this->transferFunds($this->seller, $buyer);

        $this->update(['buyer_id' => $buyer->id]);
    }

    private function validateCurrency(string $currency, float $amount, User $user)
    {
        $wallet = $user->getWallet($currency);

        if (!$wallet) {
            throw new \Exception('Wallet not found');
        }

        if ($wallet->balance < $amount) {
            throw new \Exception('Insufficient funds');
        }
    }

    private function transferFunds(User $seller, User $buyer)
    {
        $seller_wallet = $seller->getWallet($this->currency_from);
        $buyer_wallet = $buyer->getWallet($this->currency_to);

        $seller_wallet->withdraw($this->amount_from);
        $buyer_wallet->deposit($this->amount_to);
    }
}
