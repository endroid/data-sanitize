<?php

declare(strict_types=1);

namespace Endroid\DataSanitize;

use Doctrine\ORM\EntityManagerInterface;

final readonly class SanitizerFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RelationFinder $relationFinder,
    ) {
    }

    /** @param class-string $class */
    public function create(string $class): Sanitizer
    {
        return new Sanitizer($class, $this->entityManager, $this->relationFinder);
    }
}
