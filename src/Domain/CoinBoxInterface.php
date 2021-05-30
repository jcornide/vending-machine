<?php

namespace App\Domain;

interface CoinBoxInterface
{
    public function addCoin(string $coinValue, int $quantity = 1): self;

    public function getTotalAvailable(): float;

    public function refundUserCredit(): array;

    public function addUserCoin(string $coinValue, int $quantity = 1): self;

    public function getAvailableCredit(): float;

    public function refund(float $subtract): ?array;

    public function chargeUser(float $amount): self;

    public static function getAvailableCoinValues(): array;

    public static function getAvailableCoinsForDisplay(): array;

    public function getCoinsForRefund(float $amount): ?array;

    public function isChangeAvailable(float $amount): bool;
}
