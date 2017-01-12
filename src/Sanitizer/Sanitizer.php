<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\DataSanitizeBundle\Sanitizer;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use ReflectionClass;

class Sanitizer
{
    const JOIN_TYPE_TABLE = 'table';
    const JOIN_TYPE_COLUMN = 'column';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * Creates a new instance.
     *
     * @param array $config
     * @param EntityManager $manager
     */
    public function __construct(array $config, EntityManager $manager)
    {
        $this->config = $config;
        $this->manager = $manager;
    }

    /**
     * @param string $name
     * @param array $sources
     * @param mixed $target
     * @param array $strategy
     */
    public function sanitize($name, array $sources, $target, array $strategy)
    {
        $relations = $this->getRelations($name);

        foreach ($sources as $source) {
            if ($target !== $source) {
                foreach ($relations as $key => $relation) {
                    switch ($relation['join']) {
                        case self::JOIN_TYPE_TABLE:
                            if (isset($strategy[$key])) {

                                // assign existing relations to target
                                $query = "
                                    UPDATE
                                        `".$relation['table']."`
                                    SET
                                        `".

                                // remove source




                                dump($relation);
                                die;
//                                $items = $source->{'get' . ucfirst($relation['property'])}();
//                                foreach ($items as $item) {
//                                    $target->{'add' . ucfirst(substr($relation['property'], 0, -1))}($item);
//                                }
                            }
                            break;
                        case self::JOIN_TYPE_COLUMN:
                            if (isset($strategy[$key])) {
                                // Copy to target entity
                                $queryBuilder = $this->manager->createQueryBuilder();
                                $queryBuilder
                                    ->update($relation['source'], 'source')
                                    ->set('source.' . $relation['property'], $target->getId())
                                    ->where('source.' . $relation['property'] . ' = :target')
                                    ->setParameter('target', $source->getId())
                                    ->getQuery()->execute();
                            } elseif ($relation['remove'] && !$relation['orphanRemoval']) {
                                // Remove from target entity
                                $queryBuilder = $this->manager->createQueryBuilder();
                                $queryBuilder
                                    ->delete($relation['source'], 'source')
                                    ->where('source.' . $relation['property'] . ' = :target')
                                    ->setParameter('target', $source->getId())
                                    ->getQuery()->execute();
                            }
                            break;
                    }
                }
                $this->manager->remove($source);
            }
        }

        $this->manager->flush();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getRelations($name)
    {
        $class = $this->getClass($name);

        /** @var ClassMetaData[] $metaData */
        $metaData = $this->manager->getMetadataFactory()->getAllMetadata();

        $relations = [];
        foreach ($metaData as $meta) {
            foreach ($meta->getAssociationMappings() as $mapping) {
                if ($mapping['targetEntity'] == $class || $mapping['sourceEntity'] == $class) {
                    if (isset($mapping['joinTable']['name'])) {
                        $key = $mapping['joinTable']['name'];
                        $relation = $mapping['sourceEntity'] == $class ? $mapping['targetEntity'] : $mapping['sourceEntity'];
                        $relationClass = new ReflectionClass($relation);

                        dump($mapping);

                        $relations[$key] = [
                            'id' => $key,
                            'join' => self::JOIN_TYPE_TABLE,
                            'source_column' => '',
                            'required' => false,
                            'description' => 'Maintain relations with '.$relationClass->getShortName(),
                        ];

                        dump($relations[$key]);
















//                        $name = $class == $mapping['sourceEntity'] ? $mapping['fieldName'] : $mapping['inversedBy'];
//                        $relations[$key] = [
//                            'id' => $key,
//                            'join' => self::JOIN_TYPE_TABLE,
//                            'table' => $meta->table['name'],
//                            'column' => null,
//                            'property' => $name,
//                            'source' => $mapping['sourceEntity'],
//                            'target' => $mapping['targetEntity'],
//                            'remove' => false,
//                            'orphanRemoval' => $mapping['orphanRemoval'],
//                            'meta' => $meta,
//                        ];
//
//                        dump($relations[$key]);
//                        die;
                    } elseif (isset($mapping['joinColumns'])) {
                        $key = $meta->table['name'] . '.' . $mapping['joinColumns'][0]['name'];
                        $name = $class == $mapping['sourceEntity'] ? $mapping['fieldName'] : $mapping['inversedBy'];
                        $relations[$key] = [
                            'id' => $key,
                            'join' => self::JOIN_TYPE_COLUMN,
                            'table' => $meta->table['name'],
                            'column' => $mapping['joinColumns'][0]['name'],
                            'property' => $mapping['fieldName'],
                            'source' => $mapping['sourceEntity'],
                            'target' => $mapping['targetEntity'],
                            'remove' => false,
                            'orphanRemoval' => $mapping['orphanRemoval'],
                            'description' => 'Copy related '.$name.' to target entity',
                            'meta' => $meta,
                        ];

                        if ($this->hasForeignKey($relations[$key])) {
                            $relations[$key]['remove'] = true;
                        }
                    }
                }
            }
        }

        return $relations;
    }

    /**
     * @param array $relation
     * @return bool
     */
    protected function hasForeignKey(array $relation)
    {
        $platform = $this->manager->getConnection()->getDatabasePlatform();

        if (!$platform instanceof MySqlPlatform) {
            return false;
        }

        // Query all foreign keys on table.column
        $query = "
            SELECT
                *
            FROM
                information_schema.TABLE_CONSTRAINTS AS tc,
                information_schema.KEY_COLUMN_USAGE kcu
            WHERE
                tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                    AND
                tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND
                tc.TABLE_SCHEMA = DATABASE()
                    AND
                tc.TABLE_NAME = '".$relation['table']."'
                    AND
                kcu.COLUMN_NAME = '".$relation['column']."'";

        return $this->manager->getConnection()->executeQuery($query)->rowCount() > 0;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getClass($name)
    {
        return $this->config[$name]['class'];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getListFields($name)
    {
        return $this->config[$name]['list'];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getEditFields($name)
    {
        return $this->config[$name]['edit'];
    }
}
