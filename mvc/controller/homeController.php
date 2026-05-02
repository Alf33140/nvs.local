<?php
require_once("../mvc/model/User.php");
require_once("../mvc/model/Character.php");
require_once("../mvc/model/News.php");
require_once("../mvc/model/Kill.php");
require_once("../mvc/model/VictoryPoint.php");
require_once("../mvc/model/Administration.php");

require_once("../app/validator/formValidator.php");
require_once("controller.php");

class HomeController extends Controller
{
    /**
     * Display the home index.
     *
     * @return view
     */
    public function index()
    {
		$administration = new Administration();
		$maintenance_mode = $administration->getMaintenanceMode();
		$information_msg = $administration->getInfoMsg();
		
		$news = new News();
		$news = $news->orderBy('date DESC')->get();
		
		$userModel = new User();
		$usersNbr = $userModel->select('count(id_joueur) as number')->get();
		$lastRegistered = $userModel->select('perso.id_perso, perso.nom_perso, perso.clan')->leftjoin('perso','joueur.id_joueur','=','perso.idJoueur_perso')->where('chef',1)->orderBy('joueur.id_joueur DESC')->limit(1)->get();
		
		$activePlayers = new Character();
		$activePlayers = $activePlayers->select('id_perso, clan')->where('chef',1)->where('est_gele',0)->where('est_pnj',0)->get();
		
		$northActivePlayers = 0;
		$southActivePlayers = 0;
		
		foreach($activePlayers as $active){
			switch($active->clan){
				case 1:
					$northActivePlayers++;
					break;
				case 2:
					$southActivePlayers++;
					break;
			}
		}
		require_once('../mvc/view/home/home.php');
    }
	
	/**
     * Display the presentation page
     *
     * @return view
     */
    public function presentation()
    {
		require_once('../mvc/view/home/presentation.php');
    }
	
	/**
     * Display the FAQ page
     *
     * @return view
     */
    public function faq()
    {
		require_once('../mvc/view/home/faq.php');
    }
	
	/**
     * Display the Forum page
     *
     * @return view
     */
    public function forum()
    {
		require_once('../mvc/view/home/faq.php');
    }
	
	/**
     * Display the ranking page
     *
     * @return view
     */
    public function ranking()
    {
		$lastKills = new Kill();
		$lastKills = $lastKills->select('dernier_tombe.date_capture, dernier_tombe.id_perso_capture, dernier_tombe.id_perso_captureur, killed.nom_perso as killed_name, killer.nom_perso as killer_name')
							->leftJoin('perso killed','killed.id_perso','=','dernier_tombe.id_perso_capture')
							->leftJoin('perso killer','killer.id_perso','=','dernier_tombe.id_perso_captureur')
							->where('killed.est_pnj',0)
							->where('killer.est_pnj',0)
							->orderBy('dernier_tombe.date_capture DESC')
							->limit(5)
							->get();
		
		$campKills = new Kill();
		$campKills = $campKills->select('dernier_tombe.camp_perso_capture as killedCamp, dernier_tombe.camp_perso_captureur as killerCamp')
							->where('dernier_tombe.camp_perso_capture','<>','0')
							->where('dernier_tombe.camp_perso_captureur','<>','0')
							// ->orderBy('dernier_tombe.date_capture DESC')
							->get();
							
		$northAlliesKilled = 0;
		$northEnemiesKilled = 0;
		$southAlliesKilled = 0;
		$southEnemiesKilled = 0;
		
		foreach($campKills as $kill){
			switch($kill->killedCamp){
				case 1:
					if($kill->killedCamp==$kill->killerCamp){
						$northAlliesKilled++;
					}else{
						$northEnemiesKilled++;
					}
					break;
				case 2:
					if($kill->killedCamp==$kill->killerCamp){
						$southAlliesKilled++;
					}else{
						$southEnemiesKilled++;
					}
					break;
			}
		}
		
		$activeCharacters = new Character();
		$activeCharacters = $activeCharacters->select('clan, camp.name, COUNT(CASE WHEN chef=1 THEN chef END) as activeChefs, COUNT(id_perso) as activeCharacters')
										->leftJoin('camp','camp.id','=','perso.clan')
										->where('est_pnj',0)
										->where('est_gele',0)
										->where('est_renvoye',0)
										->groupBy('clan')
										->get();
		
		$bestKillers = new Character();
		$bestKillers = $bestKillers->select("id_perso, nom_perso, nb_kill")
								->where('est_pnj',0)
								->groupBy('id_perso')
								->orderBy('nb_kill DESC')
								->limit(5)
								->get();
		
		$bestHunters = new Character();
		$bestHunters = $bestHunters->select("id_perso, nom_perso, nb_pnj")
								->where('est_pnj',0)
								->groupBy('id_perso')
								->orderBy('nb_pnj DESC')
								->limit(5)
								->get();
		
		$bestRanks = new Character();
		$bestRanks = $bestRanks->select("perso.id_perso, perso.nom_perso, perso.id_grade, grades.nom_grade, grades.image_grade")
								->leftJoin('grades','grades.id_grade','=','perso.id_grade')
								->where('est_pnj',0)
								->where('chef',1)
								->groupBy('id_perso')
								->orderBy('perso.id_grade DESC')
								->limit(5)
								->get();
								
		$victoryPoints = new VictoryPoint();
		$victoryPoints = $victoryPoints->orderBy('id_camp')->get();
		
		$totalVPNorth = 0;
		$totalVPSouth = 0;

		foreach($victoryPoints as $vp){
			switch($vp->id_camp){
				case 1:
					$totalVPNorth = $totalVPNorth+$vp->gain_pvict;
					break;
				case 2:
					$totalVPSouth = $totalVPSouth+$vp->gain_pvict;
					break;
				default:
			}
		}
								
		require_once('../mvc/view/home/ranking.php');
    }
	
	/**
     * Display the Credits page
     *
     * @return view
     */
    public function credits()
    {
		require_once('../mvc/view/home/credits.php');
    }
}