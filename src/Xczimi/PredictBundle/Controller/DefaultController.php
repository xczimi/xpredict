<?php

namespace Xczimi\PredictBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use \Facebook;

use Xczimi\PredictBundle\Document\SoccerMatch;
use Xczimi\PredictBundle\Document\SoccerTeam;

use Xczimi\PredictBundle\Document\SoccerSchedule;

class DefaultController extends Controller
{
    /**
     * 
     * @var \Facebook;
     */
    protected $facebook = null;
    const CANVAS_URL = 'http://apps.facebook.com/xpredict';
    /**
     * 
     * @return \Facebook;
     */
    public function getFb()
    {
        if (is_null($this->facebook)) {
            $this->facebook = new \Facebook(
                    array('appId' => '111851335634283',
                            'secret' => '70b9cf859281cbed5372e3033135dc43',));
        }
        return $this->facebook;
    }
    public function getUser()
    {
        return $this->getFb()->getUser();
    }
    public function fbApi($param)
    {
        return $this->getFb()->api($param);
    }
    public function getFbUserName() {
        try {
            // Proceed knowing you have a logged in user who's authenticated.
            $user_profile = $this->fbApi('/me');
            $name = $user_profile['first_name'];
        } catch (FacebookApiException $e) {
            error_log($e);
            $user = null;
        }
        return $name;
    }
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction($name = null)
    {
        $user = $this->getUser();
        if (!$user) {
            return array(
                    'loginurl' => $this->getFb()
                            ->getLoginUrl(array('next' => self::CANVAS_URL)));
        }
        $predicts = $this->getFb()->api('me/xpredict:predict');
        //print_r($predicts['data']);exit;
        return array('now' => time(), 'name' => $this->getFbUserName(),
                'predicts' => $predicts['data'],
                'matches' => SoccerMatch::getList());//, 'logouturl' => $this->getFb()->getLogoutUrl(array('next'=>self::CANVAS_URL)));
    }
    /**
     * @Route("/match/{matchid}")
     * @Template
     */
    public function matchAction($matchid) {
        $match = SoccerMatch::load($matchid);
        return array('match' => $match,'name' => $this->getFbUserName());
    }
    /**
     * @Route("/predict/{matchid}")
     */
    public function predictAction($matchid) {
        $match = SoccerMatch::load($matchid);
        
        $resp = $this->getFb()->api("me/xpredict:predict", "post", array(
                'home_goal' => "2",
                'away_goal' => "0",
                'soccermatch' => "http://xpredict.xczimi.com/soccermatch/".$matchid,
        ));
        return $this->matchAction($matchid);
    }
    /**
     * @Route("/soccermatch/{matchid}")
     * @Template
     */
    public function soccermatchAction($matchid)
    {

        $match = SoccerMatch::load($matchid);
        return array('match' => $match);
    }
    /**
     * @Route("/soccerteam/{teamid}")
     * @Template
     */
    public function soccerteamAction($teamid)
    {
        $team = SoccerTeam::load($teamid);
        return array('team' => $team);
    }
    /**
     * Returns the Doctrine MongoDB document manager.
     *
     * @return Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * Returns the Doctrine repository manager for a given document.
     *
     * @param string $documentName The name of the document.
     *
     * @return Doctrine\ODM\MongoDB\DocumentRepository
     */
    protected function getRepository($documentName)
    {
        return $this->getDocumentManager()->getRepository($documentName);
    }
    protected function getDocument($document, $id)
    {
        $doc = $this->getRepository('XczimiPredictBundle:' . $document)
                ->find($id);
        if (!$doc) {
            $className = "Xczimi\PredictBundle\Document\\$document";
            $doc = new $className();
        }
        return $doc;
    }
}

