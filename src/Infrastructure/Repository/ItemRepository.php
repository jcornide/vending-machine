<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\ItemNotFoundException;
use App\Domain\ItemRepositoryInterface;

final class ItemRepository implements ItemRepositoryInterface
{
    private static $items = [
        ['name' => 'Soda', 'price' => 1.50, 'stock' => 0],
        ['name' => 'Water', 'price' => 0.65, 'stock' => 0],
        ['name' => 'Juice', 'price' => 1.00, 'stock' => 0]
    ];

    public function findAll(): array
    {
        return self::$items;
    }

    public function changeStock(string $itemName, int $quantity): self
    {
        self::$items[$this->findItemIdByName($itemName)]['stock'] += $quantity;
        return $this;
    }

    public function itemHasStock(string $itemName): bool
    {
        return self::$items[$this->findItemIdByName($itemName)]['stock'] > 0;
    }

    public function findItemByName(string $itemName): array
    {
        return self::$items[$this->findItemIdByName($itemName)];
    }

    public function findItemIdByName(string $itemName): int
    {
        $itemId = array_search($itemName, array_column(self::$items, 'name'));
        if ($itemId === false) {
            throw new ItemNotFoundException(
                "Item not found: $itemName, available items are: " . implode(', ', array_column(self::$items, 'name')));
        }
        return $itemId;
    }
}
