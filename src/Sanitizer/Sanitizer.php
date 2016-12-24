<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\DataSanitizeBundle\Sanitizer;

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
                    if (isset($strategy[$key])) {
                        switch ($relation['join']) {
                            case self::JOIN_TYPE_TABLE:
                                $items = $source->{'get'.ucfirst($relation['property'])}();
                                foreach ($items as $item) {
                                    $target->{'add'.ucfirst(substr($relation['property'], 0, -1))}($item);
                                }
                                break;
                            case self::JOIN_TYPE_COLUMN:
                                $queryBuilder = $this->manager->createQueryBuilder();
                                $queryBuilder
                                    ->update($relation['source'], 'source')
                                    ->set('source.'.$relation['property'], $target->getId())
                                    ->where('source.'.$relation['property'].' = :target')
                                    ->setParameter('target', $source->getId())
                                    ->getQuery()->execute();
                                break;
                        }
                        $this->manager->remove($source);
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
                        $name = $class == $mapping['sourceEntity'] ? $mapping['fieldName'] : $mapping['inversedBy'];
                        $relations[$key] = [
                            'id' => $key,
                            'join' => self::JOIN_TYPE_TABLE,
                            'property' => $mapping['fieldName'],
                            'source' => $mapping['sourceEntity'],
                            'target' => $mapping['targetEntity'],
                            'description' => 'Copy '.$name,
                        ];
                    } elseif (isset($mapping['joinColumns'])) {
                        $key = $meta->table['name'] . '.' . $mapping['fieldName'];
                        $name = $class == $mapping['sourceEntity'] ? $mapping['fieldName'] : $mapping['inversedBy'];
                        $relations[$key] = [
                            'id' => $key,
                            'join' => self::JOIN_TYPE_COLUMN,
                            'property' => $mapping['fieldName'],
                            'source' => $mapping['sourceEntity'],
                            'target' => $mapping['targetEntity'],
                            'description' => 'Copy '.$name,
                        ];
                    }
                }
            }
        }

        return $relations;
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
