<?php
namespace App\Domain;

interface ItemRepositoryInterface
{
    public function findAll(): array;
}
