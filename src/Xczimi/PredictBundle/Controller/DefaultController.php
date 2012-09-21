<?php

namespace Xczimi\PredictBundle\Controller;
use Xczimi\PredictBundle\Form\PredictType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Xczimi\PredictBundle\Api\Facebook;

use Xczimi\PredictBundle\Document\SoccerMatch;
use Xczimi\PredictBundle\Document\SoccerTeam;

use Xczimi\PredictBundle\Document\SoccerSchedule;

use Xczimi\PredictBundle\Document\Predict;

class DefaultController extends Controller
{
    /**
     * 
     * @var Facebook;
     */
    protected $facebook = null;
    const CANVAS_URL = 'http://apps.facebook.com/xpredict';
    /**
     * 
     * @return Facebook;
     */
    public function getFb()
    {
        if (is_null($this->facebook)) {
            $this->facebook = new Facebook($this->get('session'),
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
    public function getFbUserName()
    {
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
        return array('now' => time(), 'name' => $this->getFbUserName(),
                'predicts' => $predicts['data'],
                'matches' => SoccerMatch::getList());
    }
    /**
     * @Route("/match/{matchid}")
     * @Template
     */
    public function matchAction($matchid)
    {
        $user = $this->getUser();
        $predicts = $this->getFb()->api('me/xpredict:predict');
        $match = SoccerMatch::load($matchid);
        $matchPredict = false;
        foreach ($predicts['data'] as $predict) {
            if (preg_match(';/soccermatch/' . $matchid . ';',
                    $predict['data']['soccermatch']['url'])) {
                $matchPredict = $predict;
            }
        }
        $form = $this->createForm(new PredictType(), new Predict());
        return array('match' => $match, 'form' => $form->createView(),
                'predict' => $matchPredict, 'name' => $this->getFbUserName());
    }
    /**
     * @Route("/predict/{matchid}")
     */
    public function predictAction($matchid)
    {
        $user = $this->getUser();
        $match = SoccerMatch::load($matchid);

        $form = $this->createForm(new PredictType(), new Predict());
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $predict = $form->getData();
                try{
                $resp = $this->getFb()
                        ->api("me/xpredict:predict", "post",
                                array('home_goal' => $predict->home_goal,
                                        'away_goal' => $predict->away_goal,
                                        'soccermatch' => "http://xpredict.xczimi.com/soccermatch/"
                                                . $matchid,));
                }catch(\FacebookApiException $e){
                    
                }
            }
        }
        return $this->redirect('/match/' . $matchid);
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

