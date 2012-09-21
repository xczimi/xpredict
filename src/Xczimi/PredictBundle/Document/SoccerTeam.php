<?php

namespace Xczimi\PredictBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

class SoccerTeam
{
    protected $id;
    protected $name = "Vancouver Whitecaps";
    public function __toString()
    {
        return $this->getName();
    }
    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SoccerTeam
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }
    private static $teams;
    public static function load($id)
    {
        $teams = self::getList();
        return $teams[$id];
    }
    public static function addTeam(&$teams, $teamName)
    {
        $teamId = self::getTeamId($teamName);
        if (!isset($teams[$teamId])) {
            $teams[$teamId] = new SoccerTeam();
            $teams[$teamId]->setId($teamId)->setName($teamName);
        }
    }
    public static function getTeamId($teamName)
    {
        return preg_replace('/[^a-z]/i', '', (string) $teamName);
    }
    public static function getList()
    {
        static $teams;
        if (!$teams) {
            $schedule = SoccerSchedule::getInstance();
            $teams = array();
            foreach ($schedule->getGameList() as $matchid => $scheduledMatchString) {
                $scheduledMatch = $schedule->getGame($matchid);
                self::addTeam($teams, (string) $scheduledMatch->HomeTeam);
                self::addTeam($teams, (string) $scheduledMatch->AwayTeam);
            }
        }
        return $teams;
    }
}
