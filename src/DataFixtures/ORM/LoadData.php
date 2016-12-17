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
use Endroid\Bundle\DataSanitizeBundle\Entity\Team;

class LoadData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $leagueCount = 3;
        $teamCount = 12;
        $playerCount = 16;

        for ($l = 1; $l <= $leagueCount; $l++) {
            $league = new League();
            $league->setName('League '.$l);
            for ($t = 1; $t <= $teamCount; $t++) {
                $team = new Team();
                $team->setName('Team '.$t);
                for ($p = 1; $p <= $playerCount; $p++) {
                    $player = new Player();
                    $player->setName('Player '.$p);
                    $team->addPlayer($player);
                }
                $league->addTeam($team);
            }
            $manager->persist($league);
        }

        $manager->flush();
    }
}
