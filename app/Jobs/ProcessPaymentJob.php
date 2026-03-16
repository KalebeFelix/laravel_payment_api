<?php

namespace App\Jobs;

use App\Models\Gateway;
use App\Models\Transaction;
use App\Services\Gateways\GatewayFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 5;
    public $backoff = [10, 30, 60];

    private $transaction;
    private $data;

    public function __construct(Transaction $transaction, $data)
    {
        $this->transaction = $transaction;
        $this->data = $data;
    }

    public function handle()
    {
        Log::info("JOB STARTED");
        $gateways = Gateway::where("is_active", true)
            ->orderBy("priority")
            ->get();

        foreach ($gateways as $gateway) {

            $strategy = GatewayFactory::make($gateway->name);

            $response = $strategy->charge($this->data);

            Log::info("Gateway response: " . json_encode($response));

            if ($response["success"]) {

                $this->transaction->update([
                    "status" => "approved",
                    "gateway_id" => $gateway->id,
                    "external_id" => $response["id"]
                ]);

                return;
            }
        }

        $this->transaction->update([
            "status" => "failed"
        ]);
    }
}