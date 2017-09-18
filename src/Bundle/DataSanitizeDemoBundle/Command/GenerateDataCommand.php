<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Command;

use Doctrine\ORM\EntityManager;
use Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Project;
use Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Tag;
use Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Task;
use Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDataCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('endroid:data-sanitize-demo:generate-data')
            ->setDescription('Generate demo data')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getEntityManager();

        $projectCount = 8;
        $projectUserCount = 5;
        $userTaskCount = 3;
        $tagCount = 3;

        $currentUser = 1;
        $currentTask = 1;

        $tags = [];
        for ($t = 1; $t <= $tagCount; $t++) {
            $tag = new Tag();
            $tag->setName('Tag '.$t);
            $tags[$t] = $tag;
        }

        for ($p = 1; $p <= $projectCount; $p++) {
            $project = new Project();
            $project->setName('Project '.$p);
            for ($u = 1; $u <= $projectUserCount; $u++) {
                $user = new User();
                $user->setName('User '.$currentUser);
                for ($t = 1; $t <= $userTaskCount; $t++) {
                    $task = new Task();
                    $task->setName('Task '.$currentTask);
                    $task->setTags($tags);
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

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}
