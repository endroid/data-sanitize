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

final class ColumnRelation extends AbstractRelation
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $class;

    /** @var string */
    private $fieldName;

    public function __construct(EntityManagerInterface $entityManager, string $class, string $fieldName)
    {
        $this->entityManager = $entityManager;
        $this->class = $class;
        $this->fieldName = $fieldName;
    }

    public function merge(array $sources, string $target): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->update($this->class, 'relation')
            ->set('relation.'.$this->fieldName, $target)
            ->where($queryBuilder->expr()->in('relation.'.$this->fieldName, $sources))
        ;

        $queryBuilder->getQuery()->execute();
    }
}
