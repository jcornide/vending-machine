<?php
declare(strict_types=1);

namespace App\Domain;

final class CoinBox implements CoinBoxInterface
{
    private const AVAILABLE_COIN_VALUES = ['0.05', '0.10', '0.25', '1'];

    private $coins = [];

    private $userCoins = [];

    public function __construct()
    {
        foreach (self::AVAILABLE_COIN_VALUES as $type) {
            $this->coins[$type] = 0;
            $this->userCoins[$type] = 0;
        }
    }

    public function addCoin(string $coinValue, int $quantity = 1): self
    {
        if (!in_array($coinValue, self::AVAILABLE_COIN_VALUES)) {
            throw new InvalidCoinException("$coinValue is not a valid coin, valid coins are " . implode(', ', self::AVAILABLE_COIN_VALUES));
        }
        $this->coins[$coinValue] = $this->coins[$coinValue] + $quantity;
        return $this;
    }

    public function getTotalAvailable(): float
    {
        $total = 0;
        foreach ($this->coins as $coinValue => $quantity) {
            $total = $total + ((float) $coinValue * $quantity);
        }
        return round($total, 2);
    }

    public function refundUserCredit(): array
    {
        $availableUserCoins = $this->userCoins;
        $this->subtractCoins($this->userCoins);
        $this->userCoins = [];
        return $availableUserCoins;
    }



    public function addUserCoin(string $coinValue, int $quantity = 1): self
    {
        if (!in_array($coinValue, self::AVAILABLE_COIN_VALUES)) {
            throw new InvalidCoinException("$coinValue is not a valid coin, valid coins are " . implode(', ', self::AVAILABLE_COIN_VALUES));
        }
        $this->userCoins[$coinValue] = $this->userCoins[$coinValue] + $quantity;
        $this->addCoin($coinValue, $quantity);
        return $this;
    }

    public function getAvailableCredit(): float
    {
        $total = 0;
        foreach ($this->userCoins as $coinValue => $quantity) {
            $total = $total + ((float) $coinValue * $quantity);
        }
        return $total;
    }

    public function refund(float $amount): ?array
    {
        $sortedCoinValues = self::AVAILABLE_COIN_VALUES;
        ksort($sortedCoinValues, SORT_NUMERIC);
        $sortedCoinValues = array_reverse($sortedCoinValues);

        $coins = [];
        foreach($sortedCoinValues as $coinValue) {
            if( $this->coins[$coinValue] > 0 && $coinValue <= $amount ) {
                if ( $amount / $coinValue > $this->coins[$coinValue] ) {
                    $amount = $amount - $coinValue * $this->coins[$coinValue];
                    $coins[$coinValue] = $this->coins[$coinValue];
                } elseif ($amount == $coinValue) {
                    $amount = $amount - $coinValue;
                    $coins[$coinValue] = 1;
                } else {
                    $coins[$coinValue] = floor($amount / $coinValue);
                    $amount = $amount - ($coinValue * floor($amount / $coinValue));
                }
            }
            $amount = round($amount, 2);
        }

        if ($amount == 0) {
            $this->subtractCoins($coins);
            return $coins;
        }

        return null;
    }

    private function subtractCoins(array $coins): self
    {
        foreach ($coins as $coinValue => $quantity) {
            $this->coins[$coinValue] = $this->coins[$coinValue] - $quantity;
        }
        return $this;
    }
}
