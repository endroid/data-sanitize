<?php

declare(strict_types=1);

namespace Endroid\DataSanitize;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ManyToManyOwningSideMapping;
use Doctrine\ORM\Mapping\ToOneOwningSideMapping;

final readonly class RelationFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /** @return \Generator<Relation> */
    public function getIterator(string $class): \Generator
    {
        $classMetaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($classMetaData as $meta) {
            foreach ($meta->getAssociationMappings() as $mapping) {
                try {
                    yield $this->createRelation($class, $mapping, $meta);
                } catch (\Throwable) {
                    // Ignore associations that cannot be represented as a relation.
                    continue;
                }
            }
        }
    }

    /**
     * @param ClassMetadata<object> $classMetadata
     */
    private function createRelation(string $class, AssociationMapping $mapping, ClassMetadata $classMetadata): Relation
    {
        if ($mapping->targetEntity !== $class && $mapping->sourceEntity !== $class) {
            throw new \Exception('Could not create relation');
        }

        if ($mapping instanceof ManyToManyOwningSideMapping) {
            $joinColumn = $mapping->joinTable->joinColumns[0] ?? null;
            $inverseJoinColumn = $mapping->joinTable->inverseJoinColumns[0] ?? null;
            if (null === $joinColumn || null === $inverseJoinColumn) {
                throw new \Exception('Could not create relation');
            }

            return new Relation(
                $this->entityManager->getConnection(),
                $mapping->joinTable->name,
                $mapping->sourceEntity === $class ? $joinColumn->name : $inverseJoinColumn->name,
            );
        }

        if ($mapping instanceof ToOneOwningSideMapping && $mapping->targetEntity === $class) {
            $joinColumn = $mapping->joinColumns[0] ?? null;
            if (null !== $joinColumn) {
                return new Relation(
                    $this->entityManager->getConnection(),
                    $classMetadata->getTableName(),
                    $joinColumn->name,
                );
            }
        }
        throw new \Exception('Could not create relation');
    }
}
