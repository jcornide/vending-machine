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
}

