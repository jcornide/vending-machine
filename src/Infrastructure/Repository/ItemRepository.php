<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\ItemNotFoundException;
use App\Domain\ItemRepositoryInterface;

final class ItemRepository implements ItemRepositoryInterface
{
    private static $items = [
        ['name' => 'Soda', 'price' => 1.5, 'stock' => 0],
        ['name' => 'Water', 'price' => 0.65, 'stock' => 0],
        ['name' => 'Juice', 'price' => 1, 'stock' => 0]
    ];

    public function findAll(): array
    {
        return self::$items;
    }

    public function changeStock(string $itemName, int $quantity)
    {
        self::$items[$this->findItemIdByName($itemName)]['stock'] += $quantity;
    }

    public function findItemIdByName(string $itemName): int
    {
        $itemId = array_search($itemName, array_column(self::$items, 'name'));
        if (!$itemId === false) {
            throw new ItemNotFoundException();
        }
        return $itemId;
    }
}
