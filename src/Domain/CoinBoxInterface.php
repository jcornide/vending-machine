<?php

namespace App\Domain;

interface CoinBoxInterface
{
    public function addCoin(string $coinValue, int $quantity = 1): self;

    public function getTotalAvailable(): float;

    public function refundUserCredit(): array;

    public function addUserCoin(string $coinValue, int $quantity = 1): self;

    public function getAvailableCredit(): float;

    public function refund(float $amount): ?array;
}
