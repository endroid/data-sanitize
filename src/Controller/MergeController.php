<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\DataSanitizeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{name}")
 */
class MergeController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     *
     * @param Request $request
     * @param $name
     * @return array
     */
    public function indexAction(Request $request, $name)
    {
        $entities = $this->getDoctrine()->getRepository($this->getClass($name))->findAll();
        $selected = $this->filter($entities, (array) $request->query->get('selected'));
        $target = $this->getDoctrine()->getRepository($this->getClass($name))->findOneBy(array('id' => $request->query->get('target')));

        return [
            'name' => $name,
            'entities' => $entities,
            'selected' => $selected,
            'target' => $target,
        ];
    }

    /**
     * @Route("/list")
     * @Template()
     *
     * @param string $name
     * @param array $entities
     * @param array $selected
     * @return array
     */
    public function listAction($name, $entities, $selected)
    {
        return [
            'entities' => $entities,
            'selected' => $selected,
        ];
    }

    /**
     * @Route("/selected")
     * @Template()
     *
     * @param string $name
     * @param array $selected
     * @param mixed $target
     * @return array
     */
    public function selectedAction($name, $selected, $target)
    {
        return [
            'selected' => $selected,
            'target' => $target,
        ];
    }

    /**
     * @Route("/target")
     * @Template()
     *
     * @param string $name
     * @param mixed $target
     * @return array
     */
    public function targetAction($name, $target)
    {
        return [
            'target' => $target,
        ];
    }

    /**
     * @param array $entities
     * @param array $ids
     * @return array
     */
    protected function filter($entities, $ids)
    {
        $filtered = [];
        foreach ($entities as $entity) {
            if (in_array($entity->getId(), $ids)) {
                $filtered[$entity->getId()] = $entity;
            }
        }

        return $filtered;
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function getClass($name)
    {
        $classes = [
            'user' => 'UserBundle:User',
        ];

        return $classes[$name];
    }
}