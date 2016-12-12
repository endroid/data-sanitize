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
     * @param array $selected
     * @param mixed $target
     */
    public function sanitize($name, $selected, $target)
    {
        $class = $this->entities[$name]['class'];

        unset($selected[$target->getId()]);

        /** @var ClassMetaData[] $metaData */
        $metaData = $this->manager->getMetadataFactory()->getAllMetadata();
        foreach ($metaData as $meta) {
            foreach ($meta->getAssociationMappings() as $mapping) {
                if ($mapping['targetEntity'] == $class) {
                    if (!$mapping['isOwningSide']) {
                        continue;
                    }
                    $repository = $this->manager->getRepository($mapping['sourceEntity']);
                    foreach ($selected as $entity) {
                        $relations = $repository->findBy([$mapping['fieldName'] => $entity]);
                        foreach ($relations as $relation) {
                            $relation->{'set'.ucfirst($mapping['fieldName'])}($target);
                        }
                    }
                    dump($meta);
                    dump($mapping);
                    dump('b');
//                    $relation = $target->{'get' . ucfirst($mapping['fieldName'])}();
//                    dump($relation);
                }
                if ($mapping['sourceEntity'] == $class) {
                    dump($meta);
                    dump($mapping);
                    dump('c');
//                    $relation = $target->{'get' . ucfirst($mapping['fieldName'])}();
//                    foreach ($selected as $entity) {
//                        $entity->{'set'.ucfirst($mapping['fieldName'])}($relation);
//                    }
                }
            }
        }

        foreach ($selected as $entity) {
            $this->manager->remove($entity);
        }

        $this->manager->flush();
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
