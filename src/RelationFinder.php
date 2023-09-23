<?php

declare(strict_types=1);

namespace Endroid\DataSanitize;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

final readonly class RelationFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /** @return \Generator<Relation> */
    public function getIterator(string $class): \Generator
    {
        $classMetaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($classMetaData as $meta) {
            foreach ($meta->getAssociationMappings() as $mapping) {
                try {
                    yield $this->createRelation($class, $mapping, $meta);
                } catch (\Throwable) {
                }
            }
        }
    }

    /**
     * @param array<mixed>          $mapping
     * @param ClassMetadata<object> $classMetadata
     */
    private function createRelation(string $class, array $mapping, ClassMetadata $classMetadata): Relation
    {
        if ($mapping['targetEntity'] !== $class && $mapping['sourceEntity'] !== $class) {
            throw new \Exception('Could not create relation');
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
        throw new \Exception('Could not create relation');
    }
}
