<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="data_sanitize_demo_user")
 */
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Project", mappedBy="users", cascade={"persist"})
     * @ORM\JoinColumn(name="data_sanitize_example_user_project")
     */
    protected $projects;

    /**
     * @ORM\OneToMany(targetEntity="Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Task", mappedBy="user", cascade={"persist"})
     */
    protected $tasks;

    /**
     * Creates a new instance.
     */
    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects->toArray();
    }

    /**
     * @param Project[] $projects
     */
    public function setProjects($projects)
    {
        foreach ($this->projects as $project) {
            if (!in_array($project, $projects)) {
                $this->removeProject($project);
            }
        }

        foreach ($projects as $project) {
            $this->addProject($project);
        }
    }

    /**
     * @param Project $project
     *
     * @return bool
     */
    public function hasProject(Project $project)
    {
        return $this->projects->contains($project);
    }

    /**
     * @param Project $project
     */
    public function addProject(Project $project)
    {
        if (!$this->hasProject($project)) {
            $this->projects->add($project);
            if (!$project->hasUser($this)) {
                $project->addUser($this);
            }
        }
    }

    /**
     * @param Project $project
     */
    public function removeProject(Project $project)
    {
        if ($this->hasProject($project)) {
            $this->projects->removeElement($project);
            if ($project->hasUser($this)) {
                $project->removeUser($this);
            }
        }
    }

    /**
     * @return Task[]
     */
    public function getTasks()
    {
        return $this->tasks->toArray();
    }

    /**
     * @param Task[] $tasks
     */
    public function setTasks(array $tasks)
    {
        foreach ($this->tasks as $task) {
            if (!in_array($task, $tasks)) {
                $this->removeTask($task);
            }
        }

        foreach ($tasks as $task) {
            $this->addTask($task);
        }
    }

    /**
     * @param Task $task
     *
     * @return bool
     */
    public function hasTask(Task $task)
    {
        return $this->tasks->contains($task);
    }

    /**
     * @param Task $task
     */
    public function addTask(Task $task)
    {
        if (!$this->hasTask($task)) {
            $this->tasks->add($task);
            if ($task->getUser() !== $this) {
                $task->setUser($this);
            }
        }
    }

    /**
     * @param Task $task
     */
    public function removeTask(Task $task)
    {
        if ($this->hasTask($task)) {
            $this->tasks->removeElement($task);
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }
    }

    public function __toString()
    {
        return $this->name;
    }
}
