<?php

namespace App\Services\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Gateway2Service implements GatewayInterface
{
    public function charge(array $data)
    {
        try {

            $response = Http::timeout(5)
                ->withHeaders([
                    "Gateway-Auth-Token" => "tk_f2198cc671b5289fa856",
                    "Gateway-Auth-Secret" => "3d15e8ed6131446ea7e3456728b1211f"
                ])
                ->post('http://gateways:3002/transacoes', [
                    "valor" => $data["amount"],
                    "nome" => $data["name"],
                    "email" => $data["email"],
                    "numeroCartao" => $data["cardNumber"],
                    "cvv" => $data["cvv"]
                ]);

            if ($response->successful()) {

                return [
                    "success" => true,
                    "id" => $response["id"]
                ];
            }

        } catch (\Exception $e) {}

        return ["success" => false];
    }

    public function refund($externalId)
    {
        try {

            $response = Http::withHeaders([
                "Gateway-Auth-Token" => "tk_f2198cc671b5289fa856",
                "Gateway-Auth-Secret" => "3d15e8ed6131446ea7e3456728b1211f"
            ])->post('http://gateways:3002/transacoes/reembolso', [
                "id" => $externalId
            ]);
    
            if ($response->successful()) {

                return [
                    "success" => true
                ];
            }

        } catch (\Exception $e) {
            Log::error("Gateway2 error: " . $e->getMessage());
        }
        
        return ["success" => false];
    }
}