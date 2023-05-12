<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function createRequest(Request $request)
    {
        $this->validate($request, [
            'seller_id' => ['required', 'numeric'],
            'currency_from' => ['required', 'string'],
            'currency_to' => ['required', 'string'],
            'amount_from' => ['required', 'numeric'],
            'amount_to' => ['required', 'numeric'],
        ]);

        $seller_wallet = Wallet::where('user_id', 'seller_id')
            ->where('currency', 'currency_from')
            ->first();
        if ($seller_wallet->balance < 'amount_to') {
            return response()->json(['error' => 'Insufficient funds'], 400);
        }

        $transaction = Transaction::create([
            'seller_id' => $request->input('seller_id'),
            'buyer_id' => null,
            'currency_from' => $request->input('currency_from'),
            'currency_to' => $request->input('currency_to'),
            'amount_from' => $request->input('amount_from'),
            'amount_to' => $request->input('amount_to'),
            'system_fee' => floatval($request->input('amount_to')) * 0.02,
        ]);

        return response()->json(['transaction_id' => $transaction->id]);
    }

    public function getRequests(Request $request)
    {
        $transactions = Transaction::with(['seller:id', 'currency_from:id,name', 'currency_to:id,name'])
            ->select('seller_id', 'currency_from', 'currency_to', 'amount_from', 'amount_to', 'system_fee')
            ->where('buyer_id', null)
            ->get(['seller_id', 'currency_from', 'currency_to', 'amount_from', 'amount_to', 'system_fee']);

        $response = $transactions->map(function ($transaction) {
            return [
                'seller_id' => $transaction->seller->id,
                'currency_from' => $transaction->currency_from->name,
                'currency_to' => $transaction->currency_to->name,
                'amount_from' => $transaction->amount_from,
                'amount_to' => $transaction->amount_to + $transaction->system_fee,
            ];
        });

        return response()->json($response);
    }

    public function applyRequest(Request $request)
    {
        $this->validate($request, [
            'buyer_id' => 'required|numeric',
            'transaction_id' => 'required|numeric',
        ]);

        $transaction = Transaction::find($request->input('transaction_id'));

        if (!$transaction) {
            return response()->json(['success' => false, 'error' => 'Transaction not found']);
        }

        if ($transaction->buyer_id !== null) {
            return response()->json(['success' => false, 'error' => 'Transaction already applied']);
        }

        $transaction->apply(User::find($request->input('buyer_id')));

        return response()->json(['success' => true]);
    }

    public function getSystemFee(Request $request)
    {
        $this->validate($request, [
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        $fees = Transaction::whereBetween('created_at', [
            $request->input('date_from'), $request->input('date_to')
        ])
            ->select('currency_from', DB::raw('SUM(system_fee) as total_fee'))
            ->groupBy('currency_from')
            ->get();

        $response = $fees->map(function($fee) {
            return [
                'currency' => $fee->currency_from,
                'amount' => $fee->total_fee,
            ];
        });

        return response()->json($response);
    }
}
