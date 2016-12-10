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

class MergeController extends Controller
{
    /**
     * @Route("/{name}/list")
     * @Template()
     *
     * @param string $name
     * @return array
     */
    public function listAction($name)
    {
        $entityTypes = array(
            'user' => 'AppBundle:User',
        );

        $entityType = $entityTypes[$name];

        $entities = $this->getDoctrine()->getRepository($entityType)->findAll();

        return array(
            'entities' => $entities
        );
    }
}