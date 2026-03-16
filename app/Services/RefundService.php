<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Gateways\GatewayFactory;

class RefundService
{
    public function refund($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);

        if ($transaction->status !== "approved") {
            return response()->json([
                "error" => "Transaction cannot be refunded"
            ], 400);
        }

        try {

            $gateway = GatewayFactory::make($transaction->gateway);

            $response = $gateway->refund($transaction->external_id);

            if ($response["success"]) {

                $transaction->update([
                    "status" => "refunded"
                ]);

                return response()->json([
                    "status" => "refunded",
                    "transaction_id" => $transaction->id
                ]);
            }

            return response()->json([
                "error" => "Refund failed"
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }
}