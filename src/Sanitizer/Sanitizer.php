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
        $class = $this->getClass($name);

        /** @var ClassMetaData[] $metaData */
        $metaData = $this->manager->getMetadataFactory()->getAllMetadata();

        foreach ($sources as $source) {
            if ($source == $target) {
                continue;
            }

            foreach ($metaData as $meta) {
                foreach ($meta->getAssociationMappings() as $mapping) {
                    if ($mapping['targetEntity'] == $class) {
                        $key = $this->createRelationKey($mapping);
                        if (isset($strategy[$key])) {
                            // Copy values
                        } else {
                            // Break relations to avoid integrity issues
                        }


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
                        $sourceData = $source->{'get'.ucfirst($mapping['fieldName'])}();
                        if (isset($strategy[$key])) {
                            $targetData = $target->{'get'.$mapping['fieldName']}();
                            $targetData = is_array($sourceData) ? array_merge($sourceData, $targetData) : $targetData;
                            $target->{'set'.ucfirst($mapping['fieldName'])}($targetData);
                        }
                        $sourceData = is_array($sourceData) ? [] : null;
                        $source->{'set' . ucfirst($mapping['fieldName'])}($sourceData);
                    }
                }
            }
            $this->manager->remove($source);
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
                if ($mapping['targetEntity'] == $class) {
                    $reflect = new ReflectionClass($meta->name);
                    $relations[] = [
                        'id' => $this->createRelationKey($mapping),
                        'description' => 'Copy '.lcfirst($reflect->getShortName()).' '.$mapping['fieldName'],
                    ];
                }
                if ($mapping['sourceEntity'] == $class) {
                    $relations[] = [
                        'id' => $this->createRelationKey($mapping),
                        'description' => 'Copy '.$name.' '.$mapping['fieldName'],
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
