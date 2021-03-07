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

final class Sanitizer
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $class;

    /** @var RelationFinder */
    private $relationFinder;

    public function __construct(string $class, EntityManagerInterface $entityManager, RelationFinder $relationFinder)
    {
        $this->class = $class;
        $this->entityManager = $entityManager;
        $this->relationFinder = $relationFinder;
    }

    /**
     * @param array<string> $fields
     *
     * @return array<mixed>
     */
    public function getData(array $fields): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->from($this->class, 'entity');

        foreach ($fields as $field) {
            $property = explode('.', $field);
            if (2 == count($property)) {
                $queryBuilder->leftJoin('entity.'.$property[0], $property[0]);
                $queryBuilder->addSelect($field.' AS '.$this->getAlias($field));
            } else {
                $queryBuilder->addSelect('entity.'.$field);
            }
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getAlias(string $field): string
    {
        $parts = explode('.', $field);
        $alias = $parts[0];

        if (2 == count($parts)) {
            $alias .= ucfirst($parts[1]);
        }

        return $alias;
    }

    /** @param array<string> $sourceIds */
    public function merge(array $sourceIds, string $targetId): void
    {
        $targetIndex = array_search($targetId, $sourceIds);

        if (false !== $targetIndex) {
            unset($sourceIds[$targetIndex]);
        }

        /** @var Relation $relation */
        foreach ($this->relationFinder->getIterator($this->class) as $relation) {
            $relation->merge($sourceIds, $targetId);
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->delete($this->class, 'entity')
            ->where($queryBuilder->expr()->in('entity.id', $sourceIds))
        ;

        $queryBuilder->getQuery()->execute();
    }
}
