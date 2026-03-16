<?php

namespace App\Services\Gateways;

class GatewayFactory
{
    public static function make($name)
    {
        return match ($name) {

            "gateway1" => new Gateway1Service(),

            "gateway2" => new Gateway2Service(),

            default => throw new \Exception("Gateway not supported")
        };
    }
}