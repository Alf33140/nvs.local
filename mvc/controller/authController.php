<?php
require_once("../mvc/model/User.php");
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Building.php");
require_once("../mvc/model/Camp.php");
require_once("../mvc/model/RespawnZone.php");
require_once("../mvc/model/Map.php");
require_once("../mvc/model/Unit.php");
require_once("../mvc/model/MailFile.php");
require_once("../mvc/model/Grade.php");
require_once("../mvc/model/Weapon.php");
require_once("../mvc/model/Skill.php");
require_once("../mvc/model/Notification.php");
require_once("../mvc/model/Event.php");

require_once("../app/validator/formValidator.php");
require_once("controller.php");

class AuthController extends Controller
{
    /**
     * Display the home index.
     *
     * @return view
     */
    public function index()
    {
		header('location:index.php');
		die();
    }
	
	/**
     * Display the register page
     *
     * @return view
     */
    public function register()
    {
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

		$activePlayersDifference = $northActivePlayers-$southActivePlayers;//$northActivePlayers-$southActivePlayers
		switch($activePlayersDifference){
			case -MAX_NB_JOUEUR_DIFF:
				$desactivatedCamp = 2;
				break;
			case MAX_NB_JOUEUR_DIFF:
				$desactivatedCamp = 1;
				break;
			default:
				$desactivatedCamp = 0;
			
		}
		
		return require_once('../mvc/view/register.php');
    }
	
	/**
     * Store de registration
     *
     * @return view
     */
    public function store()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){

			// Validation du formulaire
			
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

			$activePlayersDifference = $northActivePlayers-$southActivePlayers;//$northActivePlayers-$southActivePlayers
			switch($activePlayersDifference){
				case -MAX_NB_JOUEUR_DIFF:
					$AllowedCamps = '1';
					break;
				case MAX_NB_JOUEUR_DIFF:
					$AllowedCamps = '2';
					break;
				default:
					$AllowedCamps = '1,2';
				
			}
			
			$errors =[];
			
			$errors = formValidator::validate([
				'email_joueur' => [['bail','required','email','unique:joueur,email_joueur'],'Adresse mail'],
				'mdp_joueur' => [['bail','required','regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[!?*\-+_#%]).{8,}$/'],'Mot de passe'],
				'nom_perso' => [['bail','required','min:2','max:25','regex:/^[\w\s\-\'àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]{2,25}$/','unique:perso,nom_perso'],'Nom du personnage'],
				'nom_bataillon' => [['bail','required','min:2','max:35','regex:/^[\w\s\-\'àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]{2,35}$/','unique:perso,bataillon'],'Nom du bataillon'],
				'camp_perso' => [['bail','required','numeric','in:'.$AllowedCamps],'Camp'],
				'cgu' => [['bail','required','checked'],'CGU'],
				'charte' => [['bail','required','checked'],'Charte'],
			]);

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = ['class'=>'danger','message'=>'le formulaire comporte une ou plusieurs erreurs'];
				
				header('location:?action=register');
				die();
			}else{
				
				$player = new User();
				$leader = new Character();
				$infantry = new Character();
				$unitLeader = new Unit();
				$unitInfantry = new Unit();
				$camp = new Camp();
				$respawnZone = new RespawnZone();
				$building = new Building();
				$map = new Map();
				
				//initialisation du tour de jeu
				$nowDate = new DateTimeImmutable('NOW');
				$interval = new dateInterval('PT46H');//Tour de 46h
				$DLA = $nowDate->add($interval);
				
				//récupération des caractéristiques d'unité
				$unitLeader = $unitLeader->find(1);
				$unitInfantry = $unitInfantry->find(3);
				
				// on nettoie les données et on les associe aux modèles
				$sanitizer = new formValidator();
				
				$campChoice = intval($_POST['camp_perso']);
				$camp = $camp->find($campChoice);
				
				$respawnZone = $respawnZone->where('id_camp',$camp->id)->get();
				$respawnZone = $respawnZone[0];
				
				$buildings = $building->select('instance_batiment.id_instanceBat, instance_batiment.id_batiment, instance_batiment.x_instance, instance_batiment.y_instance, instance_batiment.contenance_instance, instance_batiment.pv_instance, instance_batiment.pvMax_instance, count(perso_in_batiment.id_perso) as nb_perso_in')
										->leftJoin('perso_in_batiment','instance_batiment.id_instanceBat','=','perso_in_batiment.id_instanceBat')
										->leftJoin('batiment','instance_batiment.id_batiment','=','batiment.id_batiment')
										->where('instance_batiment.camp_instance',$camp->id)
										->whereIn('instance_batiment.id_batiment',[9,8])
										->groupBy('instance_batiment.id_instanceBat')
										->orderBy('batiment.respawn_order')
										->get();
				
				// vérification des zones de respawn (ordre de respawn : fort > fortin > aléatoire dans la zone)
				$Building_respawn = '';
				$x_respawn = '';
				$y_respawn = '';
				
				// vérification dispo fort puis fortin
				foreach($buildings as $building){
					$enemiesArround = new Building();
					$enemiesArround = $enemiesArround->enemiesArround($building->x_instance,$building->y_instance,$camp->id,15);
					$lifePercent = round($building->pv_instance/$building->pvMax_instance*100,2);

					if($building->contenance_instance > $building->nb_perso_in +1 && $lifePercent>=90 && $enemiesArround<10){
						$Building_respawn = $building->id_instanceBat;
						$x_respawn_leader = $building->x_instance;
						$y_respawn_leader = $building->y_instance;
						$x_respawn_infantry = $building->x_instance;
						$y_respawn_infantry = $building->y_instance;
						break;
					}
				}
				
				// si pas de bâtiment dispo, respawn aléatoire sur la zone de respawn définie pour le camp
				if(!isset($Building_respawn) OR $Building_respawn<=0){
					$freeSpaces = $map->freeSpaceInZone($respawnZone->x_min_zone,$respawnZone->x_max_zone,$respawnZone->y_min_zone,$respawnZone->y_max_zone);
					
					if(!isset($freeSpaces) OR empty($freeSpaces)){
						$_SESSION['old_input'] = $_POST;
						$_SESSION['flash'] = ['class'=>'danger','message'=>'Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez un administrateur. (code Erreur : ZRC)'];
				
						header('location:?action=register');
						die();
					}
					foreach($freeSpaces as $key=>$space){
					$freeSpaces[$key]['position'] = $key; 
					}
					//position du chef
					$freespaceLeader = $freeSpaces[array_rand($freeSpaces)];
					$x_respawn_leader = $freespaceLeader['x_carte'];
					$y_respawn_leader = $freespaceLeader['y_carte'];
					$key_position_leader = $freespaceLeader['position'];
					
					//suppression de la position du chef pour éviter que le grouillot ne tombe sur la même
					unset($freeSpaces[$key_position_leader]);
					
					//position du grouillot
					$freespaceInfantry = $freeSpaces[array_rand($freeSpaces)];
					$x_respawn_infantry = $freespaceInfantry['x_carte'];
					$y_respawn_infantry = $freespaceInfantry['y_carte'];
				}

				// instanciation du joueur
				$player->email_joueur = $sanitizer->sanitize($_POST['email_joueur']);
				$player->mdp_joueur = password_hash($_POST['mdp_joueur'],PASSWORD_DEFAULT);
				$player->created_at = $nowDate->format('Y-m-d H:i:s');
				
				// instanciation du chef
				$leader->nom_perso = $sanitizer->sanitize($_POST['nom_perso']);
				$leader->normalized_name = $sanitizer->sanitize($_POST['nom_perso']);
				$leader->bataillon = $sanitizer->sanitize($_POST['nom_bataillon']);
				$leader->type_perso = $unitLeader->id_unite;
				$leader->x_perso = $x_respawn_leader;
				$leader->y_perso = $y_respawn_leader;
				$leader->id_grade = 2;
				$leader->pvMax_perso = $unitLeader->pv_unite; 
				$leader->pv_perso = $unitLeader->pv_unite; 
				$leader->pmMax_perso = $unitLeader->pm_unite;
				$leader->pm_perso = $unitLeader->pm_unite;
				$leader->perception_perso = $unitLeader->perception_unite;
				$leader->recup_perso = $unitLeader->recup_unite;
				$leader->protec_perso = $unitLeader->protection_unite;
				$leader->pa_perso = $unitLeader->pa_unite;
				$leader->paMax_perso = $unitLeader->pa_unite;
				$leader->image_perso = $unitLeader->image_unite.'_'.$camp->img_suffix.$unitLeader->img_extension;
				$leader->or_perso = 20;
				$leader->chef = 1;
				$leader->clan = $camp->id;
				$leader->DLA_perso = $DLA->format('Y-m-d H:i:s');
				$leader->dateCreation_perso = $nowDate->format('Y-m-d H:i:s');
				
				// instanciation du 1er grouillot
				$infantry->nom_perso = $leader->nom_perso.' Junior';
				$infantry->normalized_name = $leader->nom_perso.' Junior';
				$infantry->bataillon = $sanitizer->sanitize($_POST['nom_bataillon']);
				$infantry->type_perso = $unitInfantry->id_unite;
				$infantry->x_perso = $x_respawn_infantry;
				$infantry->y_perso = $y_respawn_infantry;
				$infantry->id_grade = 1;
				$infantry->pvMax_perso = $unitInfantry->pv_unite; 
				$infantry->pv_perso = $unitInfantry->pv_unite; 
				$infantry->pmMax_perso = $unitInfantry->pm_unite;
				$infantry->pm_perso = $unitInfantry->pm_unite;
				$infantry->perception_perso = $unitInfantry->perception_unite;
				$infantry->recup_perso = $unitInfantry->recup_unite;
				$infantry->protec_perso = $unitInfantry->protection_unite;
				$infantry->pa_perso = $unitInfantry->pa_unite;
				$infantry->paMax_perso = $unitInfantry->pa_unite;
				$infantry->image_perso = $unitInfantry->image_unite.'_'.$camp->img_suffix.$unitInfantry->img_extension;
				$infantry->or_perso = 0;
				$infantry->chef = 0;
				$infantry->clan = $camp->id;
				$infantry->DLA_perso = $DLA->format('Y-m-d H:i:s');
				$infantry->dateCreation_perso = $nowDate->format('Y-m-d H:i:s');
				
				// on stocke
				
				$savedPlayer = $player->saveWithModel();
				
				$leader->idJoueur_perso = $savedPlayer->id_joueur;
				$infantry->idJoueur_perso = $savedPlayer->id_joueur;

				$savedLeader = $leader->saveWithModel();
				$savedInfantry = $infantry->saveWithModel();

				// on place les persos dans le bâtiment ou à défaut sur l'emplacement de respawn designé
				if(isset($Building_respawn) AND $Building_respawn>0){
					$startBuilding = new Building();
					$insertCharacters = $startBuilding->insertCharacters([$savedLeader->id_perso,$savedInfantry->id_perso],$Building_respawn);
				}else{
					$addLeaderInMap = new Map();
					$addLeaderInMap = $addLeaderInMap->addCharacter($savedLeader->id_perso,$leader->image_perso,$leader->x_perso,$leader->y_perso);
					$addInfantryInMap = new Map();
					$addInfantryInMap = $addInfantryInMap->addCharacter($savedInfantry->id_perso,$infantry->image_perso,$infantry->x_perso,$infantry->y_perso);
				}
				
				//on crée les dossiers des messageries des persos
				$createMailfiles = new MailFile();
				$createMainMailfile = $createMailfiles->addFiles([$savedLeader->id_perso,$savedInfantry->id_perso],1);
				$createArchiveMailfile = $createMailfiles->addFiles([$savedLeader->id_perso,$savedInfantry->id_perso],2);
				
				// on ajoute le grade
				$createGrade = new Grade();
				$createLeaderGrade = $createGrade->addGrade($savedLeader->id_perso,2);
				$createInfantryGrade = $createGrade->addGrade($savedInfantry->id_perso,1);

				// on ajoute les armes
				$sabre = new Weapon();
				$sabre = $sabre->select('id_arme, nom_arme')->where('nom_arme','Sabre')->get();
				$sabre = $sabre[0];
				
				$pistolet = new Weapon();
				$pistolet = $pistolet->select('id_arme, nom_arme')->where('nom_arme','Pistolet')->get();
				$pistolet = $pistolet[0];
				
				$fusil = new Weapon();
				$fusil = $fusil->select('id_arme, nom_arme')->where('nom_arme','Fusil')->get();
				$fusil = $fusil[0];
				
				$baionnette = new Weapon();
				$baionnette = $baionnette->select('id_arme, nom_arme')->where('nom_arme','Baïonnette')->get();
				$baionnette = $baionnette[0];

				$addWeapon = new Weapon();
				$addLeaderSabre = $addWeapon->addWeapon($savedLeader->id_perso,$sabre->id_arme,1);
				$addLeaderPistol = $addWeapon->addWeapon($savedLeader->id_perso,$pistolet->id_arme,1);
				$addInfantryRifle = $addWeapon->addWeapon($savedInfantry->id_perso,$fusil->id_arme,1);
				$addInfantryBaionnette = $addWeapon->addWeapon($savedInfantry->id_perso,$baionnette->id_arme,1);
				
				// on ajoute les compétences
				$sieste = new Skill();
				$sieste = $sieste->select('id_competence, nom_competence')->where('slug_competence','resting')->get();
				$sieste = $sieste[0];
				
				$marcheForcee = new Skill();
				$marcheForcee = $marcheForcee->select('id_competence, nom_competence')->where('slug_competence','hard_walk')->get();
				$marcheForcee = $marcheForcee[0];
				
				$barricade = new Skill();
				$barricade = $barricade->select('id_competence, nom_competence')->where('slug_competence','barricade_build')->get();
				$barricade = $barricade[0];

				$addSkill = new Skill();
				$addLeaderSleeping = $addSkill->addSkill($savedLeader->id_perso,$sieste->id_competence);
				$addInfantrySleeping = $addSkill->addSkill($savedInfantry->id_perso,$sieste->id_competence);
				$addInfantryForcedWalk = $addSkill->addSkill($savedInfantry->id_perso,$marcheForcee->id_competence);
				$addInfantryBarricade = $addSkill->addSkill($savedInfantry->id_perso,$barricade->id_competence);
				
				
				//on ajoute le message de bienvenue
				
				switch($camp->id){
					case 1:
						$expediteur = "Abraham Lincoln";
						break;
					case 2:
						$expediteur = "Jefferson Davis";
						break;
					default:
						$expediteur = "admin";
						$nom_camp = "";
				}
				$notificationSubject = "Lettre prioritaire !";
				$notificationMsg = "Bienvenue dans Nord vs Sud ".$leader->nom_perso.",
				Nous sommes fier de t'accueillir dans nos rangs.
				
				Tu es pour le moment indépendant. Tu peux rester ainsi mais nous te conseillons de te rapprocher des compagnies pour un plaisir de jeu authentique.
				
				Un seul mot d'ordre, amuse toi soldat !
				L'équipe d'animation de Nord Vs Sud";
				
				$notification = new Notification();
				$notification->expediteur_message = $expediteur;
				$notification->objet_message = $notificationSubject;
				$notification->contenu_message = $notificationMsg;
				$notification->date_message = $nowDate->format('Y-m-d H:i:s');
				
				$notification->saveWithModel();
										
				$notification->notificationHelper($notification->id_message,$savedLeader->id_perso);
				
				// On ajoute les évènements nouveau joueur
				$event = new Event();
				$eventLeaderDetails = "A quitté le cocon pour l'action";
				$eventInfantryDetails = "A rejoint le bataillon ".$infantry->bataillon;
				$eventDate = $nowDate->format('Y-m-d H:i:s');
				$addLeaderEvent = $event->addEvent($savedLeader->id_perso, $leader->nom_perso, $eventLeaderDetails, $eventDate);
				$addInfantryEvent = $event->addEvent($savedInfantry->id_perso, $infantry->nom_perso, $eventInfantryDetails, $eventDate);
				
				// on enregistre les données utilisateurs pour la triche
				$ip_joueur = $_SERVER["REMOTE_ADDR"];
				$user_agent = get_user_agent();
				$cookie_val = htmlspecialchars($_COOKIE["PHPSESSID"]);
				$loginDate = $nowDate->format('Y-m-d H:i:s');
				
				$user = new User();
				$user = $user->addUserOkLogin($savedPlayer->id_joueur,$ip_joueur,$user_agent,$cookie_val,$loginDate);
					
				$result = true;//Ajouter la vérification pour une autre refacto...
				
				if($result){
					$_SESSION['flash'] = ['class'=>'success','message'=>"votre compte est créé !<br>Bienvenue dans l'aventure ".$leader->nom_perso.'. Connectez-vous pour commencer'];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez un administrateur."];
				}
				
				header('location:/');
				die();
			}
		}else{
			header('location:?action=register');
			die();
		}
    }
	
	/**
     * login auth
     *
     * @return redirect
     */
    public function login()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){

			// Validation du formulaire
			
			$errors =[];
			
			$errors = formValidator::validate([
				'pseudo' => [['bail','required'],'Pseudo'],
				'password' => [['bail','required'],'Mot de passe'],
				'captcha' => [['bail','required','same:'.$_SESSION["code"]],'Captcha'],
			]);

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de login

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = ['class'=>'danger','message'=>'Certains champs sont vides ou le CAPTCHA est incorrect. Veuillez réessayer'];
				
				header('location:../');
				die();
			}else{
				// on nettoie les données
				$pseudo	= trim(htmlspecialchars($_POST['pseudo']));
				$mdp = trim($_POST['password']);
				$captcha = trim($_POST['captcha']);
				
				$mdp_md5 = md5($mdp);
				$hash_mdp = password_hash($mdp,PASSWORD_DEFAULT);
				
				// récupération des infos joueur
				$user = new User();
				$user = $user->select("id_joueur, mdp_joueur, admin_perso, permission, id_perso")
						->leftJoin("perso","perso.idJoueur_perso","=","joueur.id_joueur")
						->where("nom_perso",$pseudo)->where("chef",1)->where("pendu",0)
						->get();
				$user = $user[0];

				$hash_joueur = $user->mdp_joueur;
				$id_joueur 	= $user->id_joueur;
				
				if(empty($user->id_joueur)){
					$_SESSION['old_input'] = $_POST;
					$_SESSION['errors']['pseudo'] = 'le champ est incorrect';
					$_SESSION['errors']['password'] = 'le champ est incorrect';
					$_SESSION['flash'] = ['class'=>'danger','message'=>"Ce compte n'existe pas ou le mot de passe est incorrect. Veuillez réessayer"];
					header('location:../');
					die();
				}
				
				// on récupère les données utilisateur pour les contrôles de triche ou fraude
				$ip_joueur = $_SERVER["REMOTE_ADDR"];
				$user_agent = get_user_agent();
				$cookie_val = htmlspecialchars($_COOKIE["PHPSESSID"]);
				
				if($mdp_md5 == $hash_joueur){
					$_SESSION["ID_joueur"] = $id_joueur;
					$_SESSION["admin"] = $user->admin_perso;
					$_SESSION["permission"] = $user->permission;
					$_SESSION["id_perso"] = $user->id_perso;
					$_SESSION["leader_id"] = $user->id_perso;
					
					unset($user->id_perso);
					
					$userOkLogin = new User();
					$userOkLogin = $userOkLogin->addUserOkLogin($id_joueur,$ip_joueur,$user_agent,$cookie_val);
					
					// temporaire : on renforce la sécurité du mot de passe
					$user->mdp_joueur = $hash_mdp;
					$user->update();
					
					header("location:../jeu/jouer.php?login=ok");
					die();
					
				}elseif(password_verify($mdp,$hash_joueur)){
					$_SESSION["ID_joueur"] = $id_joueur;
					$_SESSION["admin"] = $user->admin_perso;
					$_SESSION["permission"] = $user->permission;
					$_SESSION["id_perso"] = $user->id_perso;
					$_SESSION["leader_id"] = $user->id_perso;
					
					$user = new User();
					$user = $user->addUserOkLogin($id_joueur,$ip_joueur,$user_agent,$cookie_val);
					
					header("location:../jeu/jouer.php?login=ok");
					die();
				}
				else {
					
					$user = new User();
					$user = $user->addFailedLoginAttempt($id_joueur, $ip_joueur);
					
					$_SESSION['old_input'] = $_POST;
					$_SESSION['errors']['pseudo'] = 'le champ est incorrect';
					$_SESSION['errors']['password'] = 'le champ est incorrect';
					$_SESSION['flash'] = ["class" => "danger","message"=>"Ce compte n'existe pas ou le mot de passe est incorrect. Veuillez réessayer"];
					header('location:../');
					die();
				}
			}
		}else{
			header('location:?action=create');
			die();
		}
	}
	
	/**
     * new password
     *
     * @return redirect
     */
    public function changePassword()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){
			$user = new User();
			$user = $user->select('id_joueur, mdp_joueur')
					->where('id_joueur',$_SESSION['ID_joueur'])
					->get();
			$user = $user[0];
			
			if($user->id_joueur!=$_POST['profile']){
				throw new Exception('Page non autorisée',403);
			}
			
			//redirection
			switch($_POST['from']){
				case 'user':
					$redirect = '?action=user&op=show&id='.$user->id_joueur;
					break;
				default :
					$redirect = '/';
			}
			// Validation du formulaire

			$errors =[];
			
			$errors = formValidator::validate([
				'actual_pwd' => [['bail','required','current_password:'.$user->mdp_joueur],'Mot de passe actuel'],
				'new_pwd' => [['bail','required','regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[&@!?*\-+_#%]).{8,}$/'],'Nouveau mot de passe'],
				'confirm_pwd' => [['bail','required','same:'.$_POST["new_pwd"]],'Confirmation du mot de passe'],
			]);
			
			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de login

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = [
					'class'=>'danger',
					'message'=>'Une erreur est survenue. Veuillez réessayer',
					'tab'=>'security'
					];
				
				header('location:'.$redirect);
				die();
			}else{
				// on nettoie les données
				$user->mdp_joueur = password_hash($_POST['new_pwd'],PASSWORD_DEFAULT);
					
				// on stocke
				$result = $user->update();

				if($result){
					$_SESSION['flash'] = ['class'=>'success','message'=>'Le mot de passe a été changé','tab'=>'security'];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",'tab'=>'security'];
				}
				
				header('location:'.$redirect);
				die();
			}
		}else{
			throw new Exception('Page non trouvée',404);
		}
	}
	
		/**
     * logout
     *
     * @return redirect
     */
    public function logout()
    {
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header('location:/');
		die();
	}
}