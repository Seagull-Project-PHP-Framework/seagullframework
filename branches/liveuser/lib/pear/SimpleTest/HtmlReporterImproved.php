<?php
/**
	 * Belongs to no package
	 *
	 * Cette classe h�rite de HtmlRporter et autorise le marquage des temps d'ex�cution
	 *
	 * PHP 5
	 *
	 *
	 * @category    stamplib/test
	 * @package     stamp
	 * @author      Xavier TAGLIARINO  xtagliarino@screentonic.com>
	 * @copyright  2006 The PHP Group
	 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
	 * @version     SVN: $0:$  	 
	 * @since       File available since Release 0.5
	 * @access public
*/

class HtmlReporterImproved extends HtmlReporter {

    //Temps de d�marrage (servira de r�f�rence un peu partout entre deux tests)
    private $timeDeb;
    //Unit� s, ms
    private $unit='ms';
    //Multiplicateur
    //*1 pour les secondes
    //*1000 pour les millsecondes
    private	$multiplicateur=1;

    //Initialisation du rapport
    public function __construct($unit='') {
        //On initialise le temps de d�marrage
        $this->timeDeb=$this->getMicroTime();
        //On ex�cute le constructeur de la classe parente
        $this->HtmlReporter();
        //On d�finit le multiplicateur
        switch($unit)
        {
            //Millisecondes
            case 'ms':
            $this->unit='ms';
            $this->multiplicateur=1000;
            break;

            //Secondes
            case 's':
            $this->unit='s';
            $this->multiplicateur=1;
            break;

            //Secondes par d�faut
            default:
            $this->unit='ms';
            $this->multiplicateur=1000;
            break;
        }
    }

    //Message Ok
    public function paintPass($message) {
        
        //On impl�mente directement la m�thode car non impl�ment�e dans le classe parente
        echo $message='<span style=" background-color:#009900; color:#FFFFFF;"> * SUCCESS :</span>
        <span style="color:#FFFFFF; background-color:#BBBBBB;"> '.$message.'<span>
        <br/><span style="color:#000000; background-color:#FFFFFF;">-> time of test : '.$this->getExecutionTime().' '.$this->unit.'<br/><span>';
        //On fait appelle � la m�thode parente car elle est impl�ment�e dans celle-ci
        parent::paintPass($message);
        //On r�initialise le temps de d�marrage avec le temps en cours
        $this->timeDeb=$this->getMicroTime();
        
    }

    //Message Ko
    public function paintFail($message) {
        
        //On allonge le message � transmettre � la classe parente
        $message.='<br/>-> time of test : '.$this->getExecutionTime().' '.$this->unit;
        //On fait appelle � la m�thode parente car elle est impl�ment�e dans celle-ci
        parent::paintFail($message);
        //On r�initialise le temps de d�marrage avec le temps en cours
        $this->timeDeb=$this->getMicroTime();
    }

        //Mesure du temps en cours
        public function getMicroTime() {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }
   
        //Mesure d'un Temps d'ex�cution
        private function getExecutionTime()
        {
            return round((($this->getMicroTime()-$this->timeDeb)*$this->multiplicateur),5);
        }
    }
?>