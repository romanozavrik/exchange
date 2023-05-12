<?php

namespace Database\Seeders;

use App\Enums\Currency;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('secret')
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'janesmith@example.com',
            'password' => Hash::make('secret123')
        ]);

        $usd_wallet1 = Wallet::create([
            'user_id' => $user1->id,
            'currency' => Currency::USD->value,
            'balance' => 100,
        ]);

        $uah_wallet1 = Wallet::create([
            'user_id' => $user1->id,
            'currency' => Currency::UAH->value,
            'balance' => 5000,
        ]);

        $usd_wallet2 = Wallet::create([
            'user_id' => $user2->id,
            'currency' => Currency::USD->value,
            'balance' => 10,
        ]);

        $uah_wallet2 = Wallet::create([
            'user_id' => $user2->id,
            'currency' => Currency::UAH->value,
            'balance' => 2500,
        ]);

        $eur_wallet2 = Wallet::create([
            'user_id' => $user2->id,
            'currency' => Currency::EUR->value,
            'balance' => 400,
        ]);
    }
}
