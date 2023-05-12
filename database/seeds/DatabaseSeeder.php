<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;

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
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'janesmith@example.com',
            'password' => bcrypt('password'),
        ]);

        $usd_wallet1 = Wallet::create([
            'user_id' => $user1->id,
            'currency' => 'USD',
            'balance' => 100,
        ]);

        $uah_wallet1 = Wallet::create([
            'user_id' => $user1->id,
            'currency' => 'UAH',
            'balance' => 5000,
        ]);

        $usd_wallet2 = Wallet::create([
            'user_id' => $user2->id,
            'currency' => 'USD',
            'balance' => 10,
        ]);

        $uah_wallet2 = Wallet::create([
            'user_id' => $user2->id,
            'currency' => 'UAH',
            'balance' => 2500,
        ]);

        $eur_wallet2 = Wallet::create([
            'user_id' => $user2->id,
            'currency' => 'EUR',
            'balance' => 400,
        ]);
    }
}
