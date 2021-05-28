<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\ItemRepositoryInterface;

final class ItemRepository implements ItemRepositoryInterface
{
    private const ITEMS = [
        ['name' => 'Soda', 'price' => 1.5],
        ['name' => 'Water', 'price' => 0.65],
        ['name' => 'Juice', 'price' => 1]
    ];

    public function findAll(): array
    {
        return self::ITEMS;
    }
}
