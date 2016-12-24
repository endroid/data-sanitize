<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\DataSanitizeBundle\Controller;

use Doctrine\ORM\Mapping\ClassMetadata;
use Endroid\Bundle\DataSanitizeBundle\Sanitizer\Sanitizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @return array|Response
     */
    public function indexAction(Request $request, $name)
    {
        $class = $this->getSanitizer()->getClass($name);
        $strategy = (array) $request->query->get('strategy');
        $relations = $this->getSanitizer()->getRelations($name);
        $entities = $this->getDoctrine()->getRepository($this->getSanitizer()->getClass($name))->findAll();
        $selected = $this->filter($entities, (array) $request->query->get('selected'));
        $target = $this->getDoctrine()->getRepository($this->getSanitizer()->getClass($name))->findOneBy(array('id' => $request->query->get('target')));

        if ($request->getMethod() == 'POST') {
            $this->getSanitizer()->sanitize($name, $selected, $target, $strategy);
            return $this->redirectToRoute('endroid_datasanitize_merge_index', [ 'name' => $name ]);
        }

        foreach ($relations as $key => $relation) {
            if ($relation['join'] == Sanitizer::JOIN_TYPE_COLUMN && $relation['source'] === $class) {
                unset($relations[$key]);
            }
        }

        return [
            'name' => $name,
            'class' => $class,
            'strategy' => $strategy,
            'relations' => $relations,
            'entities' => $entities,
            'listFields' => $this->getSanitizer()->getListFields($name),
            'selected' => $selected,
            'target' => $target,
            'editFields' => $this->getSanitizer()->getEditFields($name),
        ];
    }

    /**
     * @Route("/menu")
     * @Template()
     */
    public function menuAction()
    {
        $menu = [];
        $config = $this->getSanitizer()->getConfig();
        foreach ($config as $name => $entityConfig) {
            $menu[] = [
                'label' => $name,
                'url' => $this->generateUrl('endroid_datasanitize_merge_index', ['name' => $name])
            ];
        }

        return [
            'menu' => $menu,
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
     * @return Sanitizer
     */
    protected function getSanitizer()
    {
        return $this->get('endroid_data_sanitize.sanitizer');
    }
}
