<?php
require_once("../mvc/model/User.php");
require_once("../mvc/model/Map.php");
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Building.php");
require_once("../mvc/model/Vehicle.php");
require_once("../mvc/model/GameLog.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class GameboardController extends Controller
{
    /**
     * Display the game board index.
     *
     * @return view
     */
    public function index()
    {
		$dateTime = new DateTime();
		$now = (clone $dateTime)->format('Y-m-d H:i:s');
		
		$user = new User();
		$user = $user->select('id_joueur, dossier_img, admin_perso, animateur, redacteur, valid_case, afficher_rosace, bousculade_deplacement, demande_perm, permission, pendu, perso.clan as camp')
				->leftJoin('perso','perso.idJoueur_perso','=','joueur.id_joueur')
				->where('id_joueur',$_SESSION['ID_joueur'])
				->get();
		$user = $user[0];

		// on vérifie si le joueur est en permission/gelé
		if($user->permission){
			$permissionDate = new DateTime($user->permission);

			if($user->demande_perm==1){
				$_2Days = new DateInterval('P2D');
				$permWith2days = (clone $permissionDate)->add($_2Days);
				$remainingTime = $dateTime->diff($permWith2days);

				if($dateTime<$permWith2days){
					$permissionMsg = 'demande de permission en cours. La permission sera effective le '.$permWith2days->format('d-m-Y à H:i:s').'.<br> Il vous reste '.$remainingTime->format('%a jour(s) et %H:%I:%S').' pour annuler ';
				}else{
					$user->demande_perm = 0;
					$user->permission = $permWith2days->format('Y-m-d H:i:s');
					
					$buildingModel = new Building();
					$characModel = new Character();
					$vehicleModel = new Vehicle();
	
					$characterModel = new Character();
					$userCharacters = $characterModel->select('perso.id_perso')
														->where('perso.idJoueur_perso',$_SESSION['ID_joueur'])
														->get();
					if($userCharacters){
						foreach($userCharacters as $character){
							$characters_id [] = $character->id_perso;
							$character->x_perso = -1000;
							$character->y_perso = -1000;
							$character->update();
						}
						$removeCharacFromBuilding = $buildingModel->removeCharacters($characters_id);
						$removeCharacFromMap = $characModel->removeCharacFromMap($characters_id);
						$removeCharacFromVehicle = $vehicleModel->removeCharacFromVehicle($characters_id);
					}
					unset($user->camp);
					$user->update();
					header('location:/');
					die();
				}
			}elseif($user->demande_perm==0){
				$totalDays = $permissionDate->diff($dateTime);
				return require_once('../mvc/view/user/leave.php');
				die();
			}
		}
		
		// perso selectionné
		if(isset($_POST["liste_perso"]) && $_POST["liste_perso"] != "") {
			$_SESSION['id_perso'] = $_POST["liste_perso"];
			header("Location:?");
		}
		
		$selected_Character = new Character();
		$selected_Character = $selected_Character->select('perso.id_perso, idJoueur_perso, nom_perso, x_perso, y_perso, pm_perso, pmMax_perso, image_perso, pa_perso, perception_perso, bonusPerception_perso, recup_perso, bonusRecup_perso, bonusPM_perso, type_perso, type_combat, paMax_perso, pv_perso, charge_perso, chargeMax_perso, DLA_perso, est_gele, clan, est_renvoye,
									perso_as_grade.id_grade, grades.nom_grade')
								->leftJoin('perso_as_grade','perso_as_grade.id_perso','=','perso.id_perso')
								->leftJoin('grades','perso_as_grade.id_grade','=','grades.id_grade')
								->leftJoin('type_unite','perso.type_perso','=','type_unite.id_unite')
								->where('perso.id_perso',$_SESSION["id_perso"])
								->get();
		$selected_Character = $selected_Character[0];

		// contrôle si le perso appartient bien au joueur
		if($user->id_joueur!=$selected_Character->idJoueur_perso){
			// log de triche
			$cheatingLog = new GameLog();
			$cheatingLog->category = 2;
			$cheatingLog->action_type = 1;
			$cheatingLog->character_id = $_SESSION["id_perso"];
			$cheatingLog->description = 'Le joueur ['.$user->id_joueur.'] a essayé de prendre contrôle du perso ['.$selected_Character->idJoueur_perso.'] qui ne lui appartient pas !';
			$cheatingLog->refered_page = $_SERVER['REQUEST_URI'];
			$cheatingLog->created_at = $now;
			$cheatingLog->save();
			
			// logout
			$_SESSION = array(); // On écrase le tableau de session
			session_destroy(); // On détruit la session
		
			header("Location:../index.php");
		}
		
		// on vérifie que le perso n'est pas désactivé
		if($selected_Character->est_renvoye==1){
			
			$_SESSION['id_perso'] = $_SESSION["leader_id"];
			header("Location:?");
		}
		
		$dossier_img_joueur = $user->dossier_img;
		$afficher_rosace 	= $user->afficher_rosace;
		$bousculade_dep		= $user->bousculade_deplacement;
		$cadrillage			= 1;

		// contrôle des logs d'accès et du rafraîchissement de page
		$intervalTenSec = new DateInterval('PT10S');
		$intervalThirtySec = new DateInterval('PT30S');
		
		$tenSecondsAgo = (clone $dateTime)->sub($intervalTenSec);
		$tenSecondsAgo = $tenSecondsAgo->format('Y-m-d H:i:s');
		
		$thirtySecondsAgo = (clone $dateTime)->sub($intervalThirtySec);
		$thirtySecondsAgo = $thirtySecondsAgo->format('Y-m-d H:i:s');
		
		$accessControl = new GameLog();
		$accessControl = $accessControl->select('COUNT(*) as ten_sec_access_log')
						->where('character_id',$_SESSION["id_perso"])
						->where('category',1)
						->where('action_type',1)
						->where('created_at','>',$tenSecondsAgo)
						->get();
		$accessControl = $accessControl[0];
		
		if($accessControl->ten_sec_access_log>10){
			$animAlert = new GameLog();
			$animAlert = $animAlert->select('COUNT(*) as thirty_sec_alerts')
						->where('character_id',$_SESSION["id_perso"])
						->where('category',5)
						->where('action_type',2)
						->where('created_at','>',$thirtySecondsAgo)
						->get();
			$animAlert = $animAlert[0];		
			
			if($animAlert->thirty_sec_alerts==0){
				$animAlert = new GameLog();
				
				//log d'accès à la page
				$animAlert->category = 5;
				$animAlert->action_type = 2;
				$animAlert->character_id = $_SESSION["id_perso"];
				$animAlert->description = "Page de jeu - plus de 10 refresh en moins de 10 secondes";
				$animAlert->refered_page = $_SERVER['REQUEST_URI'];
				$animAlert->created_at = $now;
				$animAlert->save();
			}
		}
		
		$gameLog = new GameLog();
		
		//log d'accès à la page
		$gameLog->category = 1;
		$gameLog->action_type = 1;
		$gameLog->character_id = $_SESSION["id_perso"];
		$gameLog->description = "accès à la page de jeu";
		$gameLog->refered_page = $_SERVER['REQUEST_URI'];
		$gameLog->created_at = $now;
		$gameLog->save();
		
		/* récupération de la carte */
		// $character_view = new Map();
		// $character_view = $character_view->getCarteWithPerc(1, $character->x_perso, $character->y_perso, $character->perception_perso+$character->bonusPerception_perso);
		
		// TO DO 
		// Récupérer toutes les infos du joueur et du perso
		// Vérifier si le perso est en vie, est gelé, et si son tour vient de débuter
		// affichage de la carte
		// gestion des déplacements
		// gestion des entrées/sorties de bâtiment
		// gestion des objets au sol
		// gestion de l'affichage pour les administrateurs, animateurs, rédacteurs, gestionnaires de compagnie...
		// gestion de l'affichage pour les missions, la messagerie

		require_once('jouer_BR.php');
    }
}