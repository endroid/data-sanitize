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
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Endroid\Bundle\DataSanitizeBundle\Entity\League", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $league;

    /**
     * @ORM\OneToMany(targetEntity="Endroid\Bundle\DataSanitizeBundle\Entity\Player", mappedBy="team", cascade={"persist"})
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
     * @return Player[]
     */
    public function getPlayers()
    {
        return $this->players->toArray();
    }

    /**
     * @param Player[] $players
     */
    public function setPlayers(array $players)
    {
        // Remove players not present in new
        foreach ($this->players as $player) {
            if (!in_array($player, $players)) {
                $this->removePlayer($player);
            }
        }

        // Add all players from new
        foreach ($players as $player) {
            $this->addPlayer($player);
        }
    }

    /**
     * @param Player $player
     */
    public function addPlayer(Player $player)
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
        }

        $player->setTeam($this);
    }

    /**
     * @param Player $player
     */
    public function removePlayer(Player $player)
    {
        $this->players->removeElement($player);
        $player->setTeam(null);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->name;
    }
}
