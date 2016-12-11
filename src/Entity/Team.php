<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\DataSanitizeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="data_sanitize_example_team")
 */
class Team
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Endroid\Bundle\DataSanitizeBundle\Entity\League", cascade={"persist"})
     */
    protected $league;

    /**
     * @ORM\ManyToOne(targetEntity="Endroid\Bundle\DataSanitizeBundle\Entity\Person", cascade={"persist"})
     */
    protected $coach;

    /**
     * @ORM\OneToMany(targetEntity="Endroid\Bundle\DataSanitizeBundle\Entity\Person", mappedBy="team", cascade={"persist"})
     */
    protected $players;

    /**
     * Creates a new instance.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
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
     * @return League
     */
    public function getLeague()
    {
        return $this->league;
    }

    /**
     * @param League $league
     */
    public function setLeague(League $league = null)
    {
        $this->league = $league;
    }

    /**
     * @return Person
     */
    public function getCoach()
    {
        return $this->coach;
    }

    /**
     * @param Person $coach
     */
    public function setCoach(Person $coach = null)
    {
        $this->coach = $coach;
    }

    /**
     * @return Person[]
     */
    public function getPlayers()
    {
        return $this->players->toArray();
    }

    /**
     * @param Person[] $players
     */
    public function setPlayers(array $players)
    {
        $this->players = $players;
    }

    /**
     * @param Person $player
     */
    public function addPlayer(Person $player)
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
        }

        $player->setTeam($this);
    }

    /**
     * @param Person $player
     */
    public function removePlayer(Person $player)
    {
        $this->players->removeElement($player);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->name;
    }
}
