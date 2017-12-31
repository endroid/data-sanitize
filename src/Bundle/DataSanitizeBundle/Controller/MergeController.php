<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\DataSanitize\Bundle\DataSanitizeBundle\Controller;

use Endroid\DataSanitize\Sanitizer\Sanitizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class MergeController extends Controller
{
    /**
     * @Route("/{name}", defaults={"name": null}, requirements={"name": "[^/]*"}, name="endroid_data_sanitize_merge_index")
     * @Template()
     *
     * @param string $name
     *
     * @return array|Response
     */
    public function indexAction($name)
    {
        // Disable web profiler when using React
        if ($this->has('profiler')) {
            $this->get('profiler')->disable();
        }

        return [
            'name' => $name,
        ];
    }

    /**
     * @Route("/{name}/merge", defaults={"name": null}, requirements={"name": "[^/]*"}, name="endroid_data_sanitize_merge_merge")
     *
     * @param Request $request
     * @param $name
     *
     * @return array|Response
     */
    public function mergeAction(Request $request, $name)
    {
        $sources = $request->request->get('sources');
        if (0 == count($sources)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Invalid sources',
            ]);
        }

        $target = $request->request->get('target');
        if (is_null($target)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Invalid target',
            ]);
        }

        $this->getSanitizer()->sanitize($name, $sources, $target);

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * @Route("/{name}/state", defaults={"name": null}, requirements={"name": "[^/]*"}, name="endroid_data_sanitize_merge_state")
     *
     * @param $name
     *
     * @return JsonResponse
     */
    public function stateAction($name)
    {
        $entities = $this->getDoctrine()->getRepository($this->getSanitizer()->getClass($name))->findBy([], ['id' => 'ASC']);
        $fields = $this->getSanitizer()->getFields($name);

        foreach ($entities as &$entity) {
            $data = ['id' => $entity->getId()];
            foreach ($fields as $field) {
                $data[$field] = (string) $entity->{'get'.ucfirst($field)}();
            }
            $entity = $data;
        }

        $state = [
            'entities' => $entities,
            'fields' => $fields,
            'sources' => [],
            'target' => null,
        ];

        return new JsonResponse($state);
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
                'label' => ucfirst(str_replace('_', ' ', $name)),
                'url' => $this->generateUrl('endroid_data_sanitize_merge_index', ['name' => $name]),
            ];
        }

        return [
            'menu' => $menu,
        ];
    }

    /**
     * @param array $entities
     * @param array $ids
     *
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
