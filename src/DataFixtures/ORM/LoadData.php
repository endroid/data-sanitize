<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\DataSanitizeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Endroid\Bundle\DataSanitizeBundle\Entity\League;
use Endroid\Bundle\DataSanitizeBundle\Entity\Player;
use Endroid\Bundle\DataSanitizeBundle\Entity\Project;
use Endroid\Bundle\DataSanitizeBundle\Entity\Task;
use Endroid\Bundle\DataSanitizeBundle\Entity\Team;
use Endroid\Bundle\DataSanitizeBundle\Entity\User;

class LoadData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $projectCount = 8;
        $projectUserCount = 5;
        $userTaskCount = 3;

        $currentUser = 1;
        $currentTask = 1;

        for ($p = 1; $p <= $projectCount; $p++) {
            $project = new Project();
            $project->setName('Project '.$p);
            for ($u = 1; $u <= $projectUserCount; $u++) {
                $user = new User();
                $user->setName('User '.$currentUser);
                for ($t = 1; $t <= $userTaskCount; $t++) {
                    $task = new Task();
                    $task->setName('Task '.$currentTask);
                    $user->addTask($task);
                    $project->addTask($task);
                    $currentTask++;
                }
                $currentUser++;
                $project->addUser($user);
            }
            $manager->persist($project);
        }

        $manager->flush();
    }
}
