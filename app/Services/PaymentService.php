<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessPaymentJob;

class PaymentService
{
    public function processPurchase($data)
    {
        DB::beginTransaction();

        try {

            $amount = 0;

            $productIds = collect($data['products'])->pluck('product_id');

            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($data['products'] as $item) {

                $product = $products[$item['product_id']];

                if ($product->amount < $item['quantity']) {
                    throw new \Exception("Produto {$product->name} sem estoque suficiente");
                }

                $amount += $product->price * $item['quantity'];
            }

            $client = Client::firstOrCreate([
                "email" => $data["email"]
            ], [
                "name" => $data["name"]
            ]);

            $transaction = Transaction::create([
                "client_id" => $client->id,
                "status" => "pending",
                "amount" => $amount,
                "card_last_numbers" => substr($data["cardNumber"], -4)
            ]);

            foreach ($data['products'] as $item) {

                $transaction->products()->attach(
                    $item['product_id'],
                    ['quantity' => $item['quantity']]
                );
            }

            ProcessPaymentJob::dispatch($transaction, [
                "amount" => $amount,
                "name" => $data["name"],
                "email" => $data["email"],
                "cardNumber" => $data["cardNumber"],
                "cvv" => $data["cvv"]
            ]);

            foreach ($data['products'] as $item) {

                $product = $products[$item['product_id']];

                $product->decrement('amount', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                "status" => "processing",
                "transaction_id" => $transaction->id
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }
}