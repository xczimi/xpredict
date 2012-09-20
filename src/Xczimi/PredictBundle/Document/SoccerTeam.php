<?php

namespace Xczimi\PredictBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\Document
 */
class SoccerTeam
{
    /**
     * @MongoDB\Id
     */
    protected $id;
    /**
     * @MongoDB\String
     */
    protected $name = "Vancouver Whitecaps";
    public function __toString() {
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
}
