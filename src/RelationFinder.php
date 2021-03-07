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

    /** @return \Generator<Relation> */
    public function getIterator(string $class): \Generator
    {
        /** @var array<ClassMetadata> $classMetaData */
        $classMetaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($classMetaData as $meta) {
            foreach ($meta->getAssociationMappings() as $mapping) {
                $relation = $this->createRelation($class, $mapping, $meta);
                if ($relation instanceof Relation) {
                    yield $relation;
                }
            }
        }
    }

    /** @param array<mixed> $mapping */
    private function createRelation(string $class, array $mapping, ClassMetadata $classMetadata): ?Relation
    {
        if ($mapping['targetEntity'] !== $class && $mapping['sourceEntity'] !== $class) {
            return null;
        }

        if (isset($mapping['joinTable']['name'])) {
            $joinColumn = $mapping['joinTable']['joinColumns'][0]['name'];
            $inverseJoinColumn = $mapping['joinTable']['inverseJoinColumns'][0]['name'];

            return new Relation(
                $this->entityManager->getConnection(),
                $mapping['joinTable']['name'],
                $mapping['sourceEntity'] == $class ? $joinColumn : $inverseJoinColumn
            );
        } elseif (isset($mapping['joinColumns']) && count($mapping['joinColumns']) > 0 && $mapping['targetEntity'] === $class) {
            return new Relation(
                $this->entityManager->getConnection(),
                $classMetadata->getTableName(),
                $mapping['joinColumns'][0]['name']
            );
        }

        return null;
    }
}
