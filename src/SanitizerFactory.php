<?php

declare(strict_types=1);

namespace Endroid\DataSanitize;

use Doctrine\ORM\EntityManagerInterface;

final class SanitizerFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RelationFinder $relationFinder
    ) {
    }

    public function create(string $class): Sanitizer
    {
        return new Sanitizer($class, $this->entityManager, $this->relationFinder);
    }
}
