<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Services\RefundService;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['client', 'products'])->get();
        return response()->json($transactions);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['client', 'products']);
        return response()->json($transaction);
    }

    public function purchase(Request $request, PaymentService $paymentService)
    {
        $data = $request->validate([
            "products" => "required|array",
            "products.*.product_id" => "required|exists:products,id",
            "products.*.quantity" => "required|integer|min:1",
            "name" => "required|string",
            "email" => "required|email",
            "cardNumber" => "required|string|size:16",
            "cvv" => "required|string|size:3"
        ]);

        return $paymentService->processPurchase($data);
    }

    public function refund($id, RefundService $refundService)
    {
        return $refundService->refund($id);
    }
}
