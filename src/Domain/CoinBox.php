<?php
declare(strict_types=1);

namespace App\Domain;

final class CoinBox implements CoinBoxInterface
{
    private const AVAILABLE_COIN_VALUES = ['0.05', '0.10', '0.25', '1'];
    const COIN_SYMBOL = "€";

    private array $coins = [];

    private float $userCredit = 0;

    public function __construct()
    {
        foreach (self::AVAILABLE_COIN_VALUES as $type) {
            $this->coins[$type] = 0;
        }
    }

    public function addCoin(string $coinValue, int $quantity = 1): self
    {
        $coinValue = trim(str_replace(self::COIN_SYMBOL, '', $coinValue));
        if (!in_array($coinValue, self::AVAILABLE_COIN_VALUES)) {
            throw new InvalidCoinException("$coinValue is not a valid coin, valid coins are " . implode(', ', self::AVAILABLE_COIN_VALUES));
        }
        $this->coins[$coinValue] = $this->coins[$coinValue] + $quantity;
        return $this;
    }

    public static function getAvailableCoinValues(): array
    {
        return self::AVAILABLE_COIN_VALUES;
    }

    public static function getAvailableCoinsForDisplay(): array
    {
        return preg_filter('/$/', " " . self::COIN_SYMBOL, self::AVAILABLE_COIN_VALUES);
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
        return $this->refund($this->userCredit);
    }

    public function getCoins(): array
    {
        return $this->coins;
    }

    public function addUserCoin(string $coinValue, int $quantity = 1): self
    {
        $this->addCoin($coinValue, $quantity);
        $this->userCredit += $coinValue * $quantity;
        return $this;
    }

    public function getAvailableCredit(): float
    {
        return $this->userCredit;
    }

    public function chargeUser(float $amount): self
    {
        if ($amount > $this->userCredit) {
            throw new \InvalidArgumentException("The user can not be charged $amount, it only has $this->userCredit");
        }
        $this->userCredit -= $amount;
        return $this;
    }

    public function refund(float $amount): ?array
    {
        $coins = $this->getCoinsForRefund($amount);
        if (is_array($coins)) {
            $this->subtractCoins($coins);
            $this->userCredit -= $amount;
            return $coins;
        }

        return null;
    }

    public function getCoinsForRefund(float $amount): ?array
    {
        $sortedCoinValues = self::AVAILABLE_COIN_VALUES;
        ksort($sortedCoinValues, SORT_NUMERIC);
        $sortedCoinValues = array_reverse($sortedCoinValues);

        $coins = [];
        $subtract = $amount;
        foreach($sortedCoinValues as $coinValue) {
            if( $this->coins[$coinValue] > 0 && $coinValue <= $subtract ) {
                if ( $subtract / $coinValue > $this->coins[$coinValue] ) {
                    $subtract = $subtract - $coinValue * $this->coins[$coinValue];
                    $coins[$coinValue] = $this->coins[$coinValue];
                } elseif ($subtract == $coinValue) {
                    $subtract = $subtract - $coinValue;
                    $coins[$coinValue] = 1;
                } else {
                    $coins[$coinValue] = floor($subtract / $coinValue);
                    $subtract = $subtract - ($coinValue * floor($subtract / $coinValue));
                }
            }
            $subtract = round($subtract, 2);
        }

        return $subtract == 0 ? $coins : null;
    }

    public function isChangeAvailable(float $amount): bool
    {
        return is_array($this->getCoinsForRefund($amount));
    }

    private function subtractCoins(array $coins): self
    {
        foreach ($coins as $coinValue => $quantity) {
            $this->coins[$coinValue] = $this->coins[$coinValue] - $quantity;
        }
        return $this;
    }
}
