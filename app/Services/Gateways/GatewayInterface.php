<?php

namespace App\Services\Gateways;

interface GatewayInterface
{
    public function charge(array $data);
    public function refund($externalId);
}