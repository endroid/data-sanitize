<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\DataSanitize\Sanitizer;

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
     * @param array         $config
     * @param EntityManager $manager
     */
    public function __construct(array $config, EntityManager $manager)
    {
        $this->config = $config;
        $this->manager = $manager;
    }

    /**
     * @param string $name
     * @param array  $sources
     * @param int    $target
     */
    public function sanitize($name, array $sources, $target)
    {
        $relations = $this->getRelations($name);

        foreach ($sources as $source) {
            if ($target !== $source) {
                foreach ($relations as $key => $relation) {
                    switch ($relation['join']) {
                        case self::JOIN_TYPE_TABLE:

                            // @todo move to Mysql Adapter
                            // @todo enclose in single transaction allowing rollback

                            // 1. Avoid creating duplicates by first removing rows for entries that have both relations
                            $query = 'SELECT `'.$relation['relation_column'].'` FROM `'.$relation['table'].'` WHERE `'.$relation['subject_column']."` IN ('".$source."','".$target."') GROUP BY `".$relation['relation_column'].'` HAVING COUNT(`'.$relation['relation_column'].'`) = 2;';
                            $doubles = $this->manager->getConnection()->executeQuery($query)->fetchAll();
                            foreach ($doubles as &$double) {
                                $double = $double[$relation['relation_column']];
                            }
                            if (count($doubles) > 0) {
                                $query = 'DELETE FROM `'.$relation['table'].'` WHERE `'.$relation['subject_column']."` = '".$target."' AND `".$relation['relation_column']."` IN ('".implode("','", $doubles)."');";
                                $this->manager->getConnection()->executeUpdate($query);
                            }

                            // 2. Update relations
                            $query = 'UPDATE `'.$relation['table'].'` SET `'.$relation['subject_column']."` = '".$target."' WHERE `".$relation['subject_column']."` = '".$source."';";
                            $this->manager->getConnection()->executeUpdate($query);
                            break;
                        case self::JOIN_TYPE_COLUMN:
                            $query = 'UPDATE `'.$relation['table'].'` SET `'.$relation['column']."` = '".$target."' WHERE `".$relation['column']."` = '".$source."';";
                            $this->manager->getConnection()->executeUpdate($query);
                            break;
                    }
                }

                $queryBuilder = $this->manager->createQueryBuilder();
                $queryBuilder
                    ->delete($this->getClass($name), 'source')
                    ->where('source.id = :source')
                    ->setParameter('source', $source)
                ;

                $queryBuilder->getQuery()->execute();
            }
        }
    }

    /**
     * @param string $name
     *
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
                        $relations[$key] = [
                            'id' => $key,
                            'join' => self::JOIN_TYPE_TABLE,
                            'table' => $mapping['joinTable']['name'],
                            'subject_column' => $mapping['sourceEntity'] == $class ? $mapping['joinTable']['joinColumns'][0]['name'] : $mapping['joinTable']['inverseJoinColumns'][0]['name'],
                            'relation_column' => $mapping['targetEntity'] == $class ? $mapping['joinTable']['joinColumns'][0]['name'] : $mapping['joinTable']['inverseJoinColumns'][0]['name'],
                            'required' => false,
                            'description' => 'Update relations with '.$relationClass->getShortName(),
                        ];
                    } elseif (isset($mapping['joinColumns']) && count($mapping['joinColumns']) > 0 && $mapping['targetEntity'] == $class) {
                        $key = $meta->table['name'].'.'.$mapping['joinColumns'][0]['name'];
                        $relation = $mapping['sourceEntity'] == $class ? $mapping['targetEntity'] : $mapping['sourceEntity'];
                        $relationClass = new ReflectionClass($relation);
                        $relations[$key] = [
                            'id' => $key,
                            'join' => self::JOIN_TYPE_COLUMN,
                            'table' => $meta->table['name'],
                            'column' => $mapping['joinColumns'][0]['name'],
                            'required' => false,
                            'description' => 'Update relations with '.$relationClass->getShortName(),
                        ];

                        if ($this->hasForeignKey($relations[$key])) {
                            $relations[$key]['required'] = true;
                        }
                    }
                }
            }
        }

        return $relations;
    }

    /**
     * @param array $relation
     *
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
     *
     * @return string
     */
    public function getClass($name)
    {
        return $this->config[$name]['class'];
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getFields($name)
    {
        return $this->config[$name]['fields'];
    }
}
