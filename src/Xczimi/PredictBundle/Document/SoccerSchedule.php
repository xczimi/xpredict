<?php
namespace Xczimi\PredictBundle\Document;

define('fnScheduleHtml', dirname(__FILE__) . '/../data/whitecaps.schedule.html');
define('fnScheduleXml', dirname(__FILE__) . '/schedule.xml');

class SoccerSchedule
{
    private $fnScheduleHtml = fnScheduleHtml;
    private $fnScheduleXml = fnScheduleXml;
    public function __construct ()
    {}
    static $_instance;
    /**
     * factory
     *
     * @return Southside_Schedule
     */
    public static function getInstance ()
    {
        if (! isset(self::$_instance)) {
            self::$_instance = new SoccerSchedule();
        }
        return self::$_instance;
    }
    public function getScheduleHtml ()
    {
        $fnScheduleHtml = $this->fnScheduleHtml;
        $doc = new DOMDocument();
        if (! file_exists($fnScheduleHtml)) {
            file_put_contents($fnScheduleHtml, file_get_contents('http://www.whitecapsfc.com/schedule'));
        }
        @$doc->loadHTML(file_get_contents($fnScheduleHtml));
        $html = simplexml_import_dom($doc);
        return $html;
    }
    public function getScheduleXml ()
    {
        $fnScheduleXml = $this->fnScheduleXml;
        if (! file_exists($fnScheduleXml)) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Schedule />');
        } else {
            $xml = simplexml_load_file($fnScheduleXml);
        }
        return $xml;
    }
    public function saveScheduleXml ($xml)
    {
        $xml->asXML($this->fnScheduleXml);
    }
    public function refreshSchedule ()
    {
        echo '<pre>';
        $html = $this->getScheduleHtml();
        $xml = $this->getScheduleXml();
        $propertyXpaths = array(
			/* */'GameDate' => 'td[@class="views-field game-date"]' , 
			/* */'Kickoff' => 'td[@class="views-field start-time"]' , 
			/* */'HomeTeam' => 'td[@class="views-field home-team"]' ,
			/* */'AwayTeam' => 'td[@class="views-field away-team"]' ,
        	/* */'Venue' => 'td[@class="views-field venue"]');
        foreach ($html->xpath('//td[@class="views-field game-date"]/..') as $htmlGameNode) {
            $gameDates = $htmlGameNode->xpath('td[@class="views-field game-date"]');
            $gameDate = array_pop($gameDates);
            echo "Checking $gameDate";
            $gameNodes = $xml->xpath('//Game[@Date="' . $gameDate . '"]');
            if ($gameNodes) {
                $gameNode = array_pop($gameNodes);
                echo " Updating.";
            } else {
                $gameNode = $xml->addChild('Game');
                $gameNode->addAttribute('Date', $gameDate);
                list ($month, $day) = explode('/', $gameDate);
                $gameNode->addAttribute('ID', 50 * $month + $day);
                echo " Adding.";
            }
            foreach ($propertyXpaths as $property => $propertyXpath) {
                $propertyNodes = $htmlGameNode->xpath($propertyXpath);
                $propertyNode = array_pop($propertyNodes);
                $gameNode->$property = trim((string) $propertyNode);
                printf(" %s : %s;", $property, $gameNode->$property);
            }
            $gameNode->Timestamp = strtotime($gameNode->GameDate . " " . $gameNode->Kickoff);
            echo "\n";
        }
        echo '</pre>';
        $this->saveScheduleXml($xml);
    }
    public function getGameList() {
        $xml = $this->getScheduleXml();
        $gameList = array();
        foreach ($xml->Game as $gameNode) {
            $gameList[(int) $gameNode['ID']] = sprintf("[%s] %s vs %s",
            /* */(string) $gameNode->GameDate,
            /* */(string) $gameNode->HomeTeam,
            /* */(string) $gameNode->AwayTeam
            );
        }
        return $gameList;
    }
    public function getGame($ID) {
        $xml = $this->getScheduleXml();
        $GameNodes = $xml->xpath('Game[@ID="'.$ID.'"]');
        return array_pop($GameNodes);
    }
}