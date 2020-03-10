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
use Doctrine\ORM\Mapping\ClassMetadata;

final class RelationFinder
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getIterator(string $class): \Generator
    {
        /** @var ClassMetadata[] $classMetaData */
        $classMetaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($classMetaData as $meta) {
            foreach ($meta->getAssociationMappings() as $mapping) {
                $relation = $this->createRelation($class, $mapping, $meta);
                if ($relation instanceof RelationInterface) {
                    yield $relation;
                }
            }
        }
    }

    private function createRelation(string $class, array $mapping, ClassMetadata $classMetadata): ?RelationInterface
    {
        if ($mapping['targetEntity'] !== $class && $mapping['sourceEntity'] !== $class) {
            return null;
        }

        if (isset($mapping['joinTable']['name'])) {
            return $this->createTableRelation($class, $mapping);
        } elseif (isset($mapping['joinColumns']) && count($mapping['joinColumns']) > 0 && $mapping['targetEntity'] === $class) {
            return $this->createColumnRelation($mapping);
        }

        return null;
    }

    private function createTableRelation(string $class, array $mapping): TableRelation
    {
        $joinColumn = $mapping['joinTable']['joinColumns'][0]['name'];
        $inverseJoinColumn = $mapping['joinTable']['inverseJoinColumns'][0]['name'];

        $relation = new TableRelation(
            $this->entityManager->getConnection(),
            $mapping['joinTable']['name'],
            $mapping['sourceEntity'] == $class ? $joinColumn : $inverseJoinColumn,
            $mapping['targetEntity'] == $class ? $joinColumn : $inverseJoinColumn
        );

        return $relation;
    }

    private function createColumnRelation(array $mapping): ColumnRelation
    {
        $relation = new ColumnRelation(
            $this->entityManager,
            $mapping['sourceEntity'],
            $mapping['fieldName']
        );

        return $relation;
    }
}
