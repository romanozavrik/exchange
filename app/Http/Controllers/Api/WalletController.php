<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->input('user_id');
        $user = User::find($user_id);

        if(!$user){
            return response()->json(['message' => 'User not found'], 404);
        }

        $wallets = $user->wallets;
        return response()->json(['wallets' => $wallets], 200);
    }

    public function show(Wallet $wallet)
    {
        $this->authorize('view', $wallet);
        return response()->json(['wallet' => $wallet], 200);
    }
}

