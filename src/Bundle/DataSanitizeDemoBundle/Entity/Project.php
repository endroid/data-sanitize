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
 * @ORM\Table(name="data_sanitize_demo_project")
 */
class Project
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
     * @ORM\OneToMany(targetEntity="Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\Task", mappedBy="project", cascade={"persist"})
     */
    protected $tasks;

    /**
     * @ORM\ManyToMany(targetEntity="Endroid\DataSanitize\Bundle\DataSanitizeDemoBundle\Entity\User", inversedBy="projects", cascade={"persist"})
     */
    protected $users;

    /**
     * Creates a new instance.
     */
    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
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
            if ($task->getProject() !== $this) {
                $task->setProject($this);
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
            if ($task->getProject() === $this) {
                $task->setProject(null);
            }
        }
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users->toArray();
    }

    /**
     * @param User[] $users
     */
    public function setUsers(array $users)
    {
        foreach ($this->users as $user) {
            if (!in_array($user, $users)) {
                $this->removeUser($user);
            }
        }

        foreach ($users as $user) {
            $this->addUser($user);
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            if (!$user->hasProject($this)) {
                $user->addProject($this);
            }
        }
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->hasProject($this)) {
                $user->removeProject($this);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->name;
    }
}
