<?php

namespace Xczimi\PredictBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

class SoccerMatch
{
    protected $id;
    protected $hometeam;
    protected $awayteam;
    protected $kickoff;
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
     * Set hometeam
     *
     * @param Xczimi\PredictBundle\Document\SoccerTeam $homeTeam
     * @return SoccerMatch
     */
    public function setHomeTeam(\Xczimi\PredictBundle\Document\SoccerTeam $homeTeam)
    {
        $this->hometeam = $homeTeam;
        return $this;
    }

    /**
     * Get hometeam
     *
     * @return Xczimi\PredictBundle\Document\SoccerTeam $homeTeam
     */
    public function getHomeTeam()
    {
        return $this->hometeam;
    }

    /**
     * Set awayteam
     *
     * @param Xczimi\PredictBundle\Document\SoccerTeam $awayTeam
     * @return SoccerMatch
     */
    public function setAwayTeam(\Xczimi\PredictBundle\Document\SoccerTeam $awayTeam)
    {
        $this->awayteam = $awayTeam;
        return $this;
    }

    /**
     * Get awayteam
     *
     * @return Xczimi\PredictBundle\Document\SoccerTeam $awayTeam
     */
    public function getAwayTeam()
    {
        return $this->awayteam;
    }

    /**
     * Set kickoff
     *
     * @param timestamp $kickoff
     * @return SoccerMatch
     */
    public function setKickoff($kickoff)
    {
        $this->kickoff = $kickoff;
        return $this;
    }

    /**
     * Get kickoff
     *
     * @return timestamp $kickoff
     */
    public function getKickoff()
    {
        return $this->kickoff;
    }
    public function getName() {
        return $this->getHomeTeam()->getName().' vs '.$this->getAwayTeam()->getName();
    }
    public static function load($id)
    {
        $list = self::getList();
        return $list[$id];
    }
    public static function getList()
    {
        $schedule = SoccerSchedule::getInstance();
        foreach($schedule->getGameList() as $matchid => $scheduledMatchString) {
            $scheduledMatch = $schedule->getGame($matchid);
            $match = new self();
            $match->setId($matchid);
            $match->setKickoff($scheduledMatch->Timestamp);
            $match->setHomeTeam(SoccerTeam::load(SoccerTeam::getTeamId($scheduledMatch->HomeTeam)));
            $match->setAwayTeam(SoccerTeam::load(SoccerTeam::getTeamId($scheduledMatch->AwayTeam)));
            $matches[$matchid] = $match;
        }
        return $matches;
    }
}
