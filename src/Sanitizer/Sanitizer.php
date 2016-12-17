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
    /**
     * @var array
     */
    protected $entities;

    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * Creates a new instance.
     *
     * @param array $entities
     * @param EntityManager $manager
     */
    public function __construct(array $entities, EntityManager $manager)
    {
        $this->entities = $entities;
        $this->manager = $manager;
    }

    /**
     * @param string $name
     * @param array $sources
     * @param mixed $target
     */
    public function sanitize($name, array $sources, $target)
    {
        foreach ($sources as $source) {
            $this->sanitizeSingleSource($name, $source, $target);
        }

        $this->manager->flush();
    }

    /**
     * @param string $name
     * @param mixed $source
     * @param mixed $target
     */
    protected function sanitizeSingleSource($name, $source, $target)
    {
        if ($source == $target) {
            return;
        }

        $class = $this->getClass($name);

        /** @var ClassMetaData[] $metaData */
        $metaData = $this->manager->getMetadataFactory()->getAllMetadata();

        foreach ($metaData as $meta) {
            foreach ($meta->getAssociationMappings() as $mapping) {
                if ($mapping['targetEntity'] == $class) {
                    $key = $this->createRelationKey($mapping);
//                    $queryBuilder = $this->manager->createQueryBuilder();
//                    $queryBuilder
//                        ->select('source')
//                        ->from($mapping['sourceEntity'], 'source')
//                        ->join('source.'.$mapping['fieldName'], 'target')
//                        ->where('target = :target')
//                        ->setParameter('target', $target)
//                    ;
//
//                    $results = $queryBuilder->getQuery()->getResult();
//                    foreach ($results as $result) {
//                        $result->{'remove'.ucfirst($mapping['fieldName'])}($selected);
//                    }

//                    dump($meta);
//                    dump($mapping);
//                    dump($relations);
//                    die;
                }
                if ($mapping['sourceEntity'] == $class) {
                    $key = $this->createRelationKey($mapping);
                    $sourceData = $source->{'get'.$mapping['fieldName']}();
                    $targetData = $target->{'get'.$mapping['fieldName']}();
                    if (is_array($sourceData)) {
                        $targetData = array_merge($sourceData, $targetData);
                        $target->{'set'.$mapping['fieldName']}($targetData); // set players
                    } else {
//                        dump($data);
                    }
                }
            }
        }

        $this->manager->remove($source);
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
                if ($mapping['targetEntity'] == $class) {
                    $reflect = new ReflectionClass($meta->name);
                    $relations[] = [
                        'id' => $this->createRelationKey($mapping),
                        'description' => $reflect->getShortName().' has '.$mapping['fieldName'].' target',
                    ];
                }
                if ($mapping['sourceEntity'] == $class) {
                    $relations[] = [
                        'id' => $this->createRelationKey($mapping),
                        'description' => ucfirst($name).' has '.$mapping['fieldName'].' source',
                    ];
                }
            }
        }

        return $relations;
    }

    /**
     * @param array $mapping
     * @return string
     */
    public function createRelationKey(array $mapping)
    {
        return sha1($mapping['sourceEntity'].$mapping['targetEntity'].$mapping['fieldName']);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getClass($name)
    {
        return $this->entities[$name]['class'];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getListFields($name)
    {
        return $this->entities[$name]['list'];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getEditFields($name)
    {
        return $this->entities[$name]['edit'];
    }
}
