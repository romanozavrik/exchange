<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    public function createRequest(Request $request)
    {
        $this->validate($request, [
            'seller_id' => 'required|numeric',
            'currency_from' => 'required|string',
            'currency_to' => 'required|string',
            'amount_from' => 'required|numeric',
            'amount_to' => 'required|numeric',
        ]);

        $transaction = Transaction::create([
            'seller_id' => $request->input('seller_id'),
            'buyer_id' => null,
            'currency_from' => $request->input('currency_from'),
            'currency_to' => $request->input('currency_to'),
            'amount_from' => $request->input('amount_from'),
            'amount_to' => $request->input('amount_to'),
            'system_fee' => $request->input('amount_to') * 0.02,
        ]);

        return response()->json(['transaction_id' => $transaction->id]);
    }

    public function getRequests(Request $request)
    {
        $transactions = Transaction::where('buyer_id', null)->get();

        $response = [];
        foreach ($transactions as $transaction) {
            $response[] = [
                'seller_id' => $transaction->seller_id,
                'currency_from' => $transaction->currency_from,
                'currency_to' => $transaction->currency_to,
                'amount_from' => $transaction->amount_from,
                'amount_to' => $transaction->amount_to + $transaction->system_fee,
            ];
        }

        return response()->json($response);
    }

    public function applyRequest(Request $request)
    {
        $this->validate($request, [
            'buyer_id' => 'required|numeric',
            'transaction_id' => 'required|numeric',
        ]);

        $transaction = Transaction::find($request->input('transaction_id'));

        $transaction->buyer_id = $request->input('buyer_id');
        $transaction->save();

        $seller_wallet = Wallet::where('user_id', $transaction->seller_id)
            ->where('currency', $transaction->currency_from)->first();
        $seller_wallet->balance -= $transaction->amount_from;
        $seller_wallet->save();

        $buyer_wallet = Wallet::where('user_id', $request->input('buyer_id'))
            ->where('currency', $transaction->currency_to)->first();
        $buyer_wallet->balance += $transaction->amount_to;
        $buyer_wallet->save();

        return response()->json(['success' => true]);
    }

    public function getSystemFee(Request $request)
    {
        $this->validate($request, [
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        $transactions = Transaction::whereBetween('created_at', [
            $request->input('date_from'), $request->input('date_to')
        ])->get();

        $fees = [];
        foreach ($transactions as $transaction) {
            if (!isset($fees[$transaction->currency_from])) {
                $fees[$transaction->currency_from] = 0;
            }
            $fees[$transaction->currency_from] += $transaction->system_fee;
        }

        $response = [];
        foreach ($fees as $currency => $amount) {
            $response[] = [
                'currency' => $currency,
                'amount' => $amount,
            ];
        }

        return response()->json($response);
    }
}
