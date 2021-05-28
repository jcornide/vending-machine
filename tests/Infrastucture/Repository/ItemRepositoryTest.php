<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Repository;

use App\Infrastructure\Repository\ItemRepository;
use PHPUnit\Framework\TestCase;

class ItemRepositoryTest extends TestCase
{
    /** @test */
    public function findAllMethodShouldReturnAnArray()
    {
        $repository = new ItemRepository();
        $this->assertIsArray($repository->findAll());
    }

    /** @test */
    public function addingStockToItemShouldIncreaseStockOfTheItem()
    {
        $repository = new ItemRepository();

        $items = $repository->findAll();
        $firstItem = $items[0];

        $this->assertEquals(0, $firstItem['stock']);
        $repository->changeStock($items[0]['name'], 3);

        $items = $repository->findAll();
        $firstItem = $items[0];

        $this->assertEquals(3, $firstItem['stock']);

    }
}

