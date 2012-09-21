<?php
define('fnPlayersHtml', dirname(__FILE__) . '/../data/whitecaps.players.html');
define('fnPlayersXml', dirname(__FILE__) . '/players.xml');
class Southside_Players
{
    private $fnPlayersHtml = fnPlayersHtml;
    private $fnPlayersXml = fnPlayersXml;
    public function __construct ()
    {}
    static $_instance;
    /**
     * factory
     *
     * @return Southside_Players
     */
    public static function getInstance ()
    {
        if (! isset(self::$_instance)) {
            self::$_instance = new Southside_Players();
        }
        return self::$_instance;
    }
    public function getPlayersHtml ()
    {
        $fnPlayersHtml = $this->fnPlayersHtml;
        $doc = new DOMDocument();
        if (! file_exists($fnPlayersHtml)) {
            file_put_contents($fnPlayersHtml, file_get_contents('http://www.whitecapsfc.com/players'));
        }
        @$doc->loadHTML(file_get_contents($fnPlayersHtml));
        $html = simplexml_import_dom($doc);
        return $html;
    }
    public function getPlayersXml ()
    {
        $fnPlayersXml = $this->fnPlayersXml;
        if (! file_exists($fnPlayersXml)) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Players />');
        } else {
            $xml = simplexml_load_file($fnPlayersXml);
        }
        return $xml;
    }
    public function savePlayersXml ($xml)
    {
        $xml->asXML($this->fnPlayersXml);
    }
    public function refreshPlayers ()
    {
        echo '<pre>';
        $html = $this->getPlayersHtml();
        $xml = $this->getPlayersXml();
        $id = 0;
        foreach ($xml->Player as $playerNode) {
            $id = max($id, intval($playerNode['ID']) + 1);
        }
        echo "MAX id : $id\n";
        $propertyXpaths = array(
			/* */'FullName' => 'td[@class="views-field views-field-field-player-lname-value"]/a' , 
			/* */'Position' => 'td[@class="views-field views-field-field-player-position-detail-value"]' , 
			/* */'JerseyNo' => 'td[@class="views-field views-field-field-player-jersey-no-value"]');
        foreach ($html->xpath('//td[@class="views-field views-field-field-player-lname-value"]/a/../..') as $htmlPlayerNode) {
            $hrefs = $htmlPlayerNode->xpath('td[@class="views-field views-field-field-player-lname-value"]/a/@href');
            $playerName = str_replace('/players/', '', array_pop($hrefs));
            echo "Checking $playerName";
            $playerNodes = $xml->xpath('//Player[@Name="' . $playerName . '"]');
            if ($playerNodes) {
                $playerNode = array_pop($playerNodes);
                echo " Updating.";
            } else {
                $playerNode = $xml->addChild('Player');
                $playerNode->addAttribute('ID', $id ++);
                $playerNode->addAttribute('Name', $playerName);
                echo " Adding.";
            }
            foreach ($propertyXpaths as $property => $propertyXpath) {
                $propertyNodes = $htmlPlayerNode->xpath($propertyXpath);
                $propertyNode = array_pop($propertyNodes);
                $playerNode->$property = trim((string) $propertyNode);
                printf(" %s : %s;", $property, $playerNode->$property);
            }
            echo "\n";
        }
        echo '</pre>';
        $this->savePlayersXml($xml);
    }
    public function getPlayerNameList ()
    {
        $xml = $this->getPlayersXml();
        $playerNames = array();
        foreach ($xml->Player as $playerNode) {
            $playerNames[(int) $playerNode['ID']] = (string) $playerNode->FullName;
        }
        return $playerNames;
    }
    public function getPlayerNamePositionList ()
    {
        $xml = $this->getPlayersXml();
        $playerNames = array();
        foreach ($xml->Player as $playerNode) {
            $playerNames[(int) $playerNode['ID']] = (string) $playerNode->Position . " " . (string) $playerNode->FullName;
        }
        sort($playerNames);
        return $playerNames;
    }
}