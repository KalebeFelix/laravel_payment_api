<?php

namespace App\Services\Gateways;

use Illuminate\Support\Facades\Http;

class Gateway1Service implements GatewayInterface
{
    public function charge(array $data)
    {
        try {

            $login = Http::timeout(5)->post('http://gateways:3001/login', [
                "email" => "dev@betalent.tech",
                "token" => "FEC9BB078BF338F464F96B48089EB498"
            ]);

            if (!$login->successful()) {
                return ["success" => false];
            }

            $token = $login["token"];

            $response = Http::timeout(5)
                ->withToken($token)
                ->post('http://gateways:3001/transactions', [
                    "amount" => $data["amount"],
                    "name" => $data["name"],
                    "email" => $data["email"],
                    "cardNumber" => $data["cardNumber"],
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

            $login = Http::post('http://gateways:3001/login', [
                "email" => "dev@betalent.tech",
                "token" => "FEC9BB078BF338F464F96B48089EB498"
            ]);

            $token = $login["token"];

            $response = Http::withToken($token)
                ->post("http://gateways:3001/transactions/{$externalId}/charge_back");

            if ($response->successful()) {

                return [
                    "success" => true
                ];
            }

        } catch (\Exception $e) {}

        return ["success" => false];
    }
}