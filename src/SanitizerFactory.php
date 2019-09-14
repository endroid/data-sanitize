<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\DataSanitize;

use Doctrine\ORM\EntityManagerInterface;

final class SanitizerFactory
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RelationFinder */
    private $relationFinder;

    public function __construct(EntityManagerInterface $entityManager, RelationFinder $relationFinder)
    {
        $this->entityManager = $entityManager;
        $this->relationFinder = $relationFinder;
    }

    public function create(string $class): Sanitizer
    {
        return new Sanitizer($class, $this->entityManager, $this->relationFinder);
    }
}
