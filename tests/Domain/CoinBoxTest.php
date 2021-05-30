<?php
declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\CoinBox;
use App\Domain\InvalidCoinException;
use PHPUnit\Framework\TestCase;

class CoinBoxTest extends TestCase
{
    /** @test */
    public function whenUserAddsACoinTheCreditIncreases()
    {
        $coinBox = new CoinBox();
        $coinBox->addUserCoin('0.25', 1);

        $this->assertEquals(0.25, $coinBox->getAvailableCredit());
    }

    /** @test */
    public function whenUserAddsANonValidCoinAnExceptionShouldBeLaunched()
    {
        $coinBox = new CoinBox();
        $this->expectException(InvalidCoinException::class);
        $coinBox->addUserCoin('3', 1);
    }

    /** @test */
    public function whenCoinsAreAddedTheTotalAvailableChangesButTheUserCreditRemainsTheSame()
    {
        $coinBox = new CoinBox();
        $coinBox
            ->addCoin('0.10', 5)
            ->addCoin('0.05', 10)
            ->addCoin('0.25', 2)
            ->addCoin('1', 1);
        $this->assertEquals(2.5, $coinBox->getTotalAvailable());
    }

    /** @test */
    public function whenManagerAddsANonValidCoinAnExceptionShouldBeLaunched()
    {
        $coinBox = new CoinBox();
        $this->expectException(InvalidCoinException::class);
        $coinBox->addCoin('3', 1);
    }

    /** @test */
    public function returnUserCredit()
    {
        $coinBox = new CoinBox();

        $coinBox
            ->addUserCoin('1', 2)
            ->addUserCoin('0.25', 2)
            ->addCoin('1', 3)
            ->addCoin('0.05', 4);

        $this->assertEquals(2.5, $coinBox->getAvailableCredit());
        $this->assertEquals(5.7, $coinBox->getTotalAvailable());

        $expectedChange = [
            '1' => 2,
            '0.25' => 2
        ];
        $coinsReturned = $coinBox->refundUserCredit();

        $this->assertEquals(0, $coinBox->getAvailableCredit());
        $this->assertEquals(3.2, $coinBox->getTotalAvailable());
        $this->assertCount(0, array_diff($expectedChange, $coinsReturned));
    }

    /** @test */
    public function availableChangeShouldBeAvailableIfThereAreEnoughCoins()
    {
        $coinBox = new CoinBox();
        $coinBox
            ->addCoin('1', 3)
            ->addCoin('0.25', 10)
            ->addCoin('0.10', 15)
            ->addCoin('0.05', 20);

        $this->assertEquals(8, $coinBox->getTotalAvailable());

        $this->assertEquals(
            ['1' => 3, '0.25' => 5, '0.10' => 1],
            $coinBox->refund(4.35)
        );

        $this->assertEquals(3.65, $coinBox->getTotalAvailable());
    }

    /** @test */
    public function subtractUserCredit()
    {
        $coinBox = new CoinBox();
        $coinBox
            ->addCoin('0.25', 2)
            ->addCoin('0.10', 2)
            ->addUserCoin('1', 1);

        $this->assertEquals(1.70, $coinBox->getTotalAvailable());
        $coinBox->chargeUser(0.65);
        $coinBox->refund(0.35);
        $this->assertEquals(0, $coinBox->getAvailableCredit());
        $this->assertEquals(1.35, $coinBox->getTotalAvailable());
    }

    /** @test */
    public function isChangeAvailable()
    {
        $coinBox = new CoinBox();
        $coinBox
            ->addCoin('0.25', 2)
            ->addCoin('0.10', 2);

        $this->assertFalse($coinBox->isChangeAvailable(1));
        $this->assertTrue($coinBox->isChangeAvailable(0.50));
        $this->assertTrue($coinBox->isChangeAvailable(0.35));
        $this->assertTrue($coinBox->isChangeAvailable(0.60));
        $this->assertTrue($coinBox->isChangeAvailable(0));
    }
}
