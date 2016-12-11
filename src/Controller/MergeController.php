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
        $entities = $this->getDoctrine()->getRepository($this->getSanitizer()->getClass($name))->findAll();
        $selected = $this->filter($entities, (array) $request->query->get('selected'));
        $target = $this->getDoctrine()->getRepository($this->getSanitizer()->getClass($name))->findOneBy(array('id' => $request->query->get('target')));

        if ($request->getMethod() == 'POST') {
            $this->getSanitizer()->sanitize($name, $selected, $target);
        }

        return [
            'name' => $name,
            'entities' => $entities,
            'listFields' => $this->getSanitizer()->getListFields($name),
            'selected' => $selected,
            'target' => $target,
            'editFields' => $this->getSanitizer()->getEditFields($name),
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
