<?php
require_once("../mvc/model/Character.php");
require_once("../mvc/model/User.php");
require_once("../mvc/model/Unit.php");
require_once("../mvc/model/Grade.php");
require_once("../mvc/model/Weapon.php");
require_once("../mvc/model/Item.php");
require_once("../mvc/model/Account.php");
require_once("../mvc/model/Bank.php");
require_once("../mvc/model/Company.php");
require_once("../mvc/model/Building.php");
require_once("../mvc/model/BuildingType.php");
require_once("../mvc/service/CharacterService.php");
require_once("../mvc/service/EquipmentService.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class CharacterController extends Controller
{
    /**
     * Display the character index.
     *
     * @return view
     */
    public function index()
    {	
		$characters = new Character();
		$characters = $characters->select("perso.id_perso, perso.idJoueur_perso, perso.nom_perso, perso.type_perso, perso.chef, perso.est_renvoye, type_unite.id_unite, type_unite.nom_unite, type_unite.image_unite, type_unite.cout_pg, camp.id as camp_id, camp.name as camp_name")
						->where("idJoueur_perso",$_SESSION['ID_joueur'])
						->leftJoin("type_unite","type_unite.id_unite","=","perso.type_perso")
						->leftJoin("camp","camp.id","=","perso.clan")
						->orderBy('perso.est_renvoye')
						->get();
		
		$mainCharacPG = new Grade();
		$mainCharacPG = $mainCharacPG->select('grades.id_grade, grades.point_armee_grade, perso.pa_perso')
									->leftJoin('perso_as_grade','perso_as_grade.id_grade','=','grades.id_grade')
									->leftJoin('perso','perso.id_perso','=','perso_as_grade.id_perso')
									->where('perso.idJoueur_perso',$characters[0]->idJoueur_perso)
									->where('perso.chef',1)->get()[0];
			
		$used_pg = 0;
		
		foreach($characters as $character){
			if($character->est_renvoye==0){
				$used_pg += $character->cout_pg;
			}
		}
				
		$remaining_pg = $mainCharacPG->point_armee_grade - $used_pg;

		return require_once('../mvc/view/character/index.php');
    }
	
	/**
     * Display the character creation page.
     *
     * @return view
     */
    public function create()
    {	
    }
	
	/**
     * Display the specified character.
     *
     * @param  $id
     * @return view
     */
    public function show(int $id)
    {
		$characterModel = new Character();
		$character = $characterModel->select("perso.id_perso, perso.idJoueur_perso, perso.nom_perso, perso.type_perso, perso.message_perso, perso.description_perso, perso.id_grade, perso.xp_perso, perso.pi_perso, perso.pc_perso, perso.pmMax_perso,
										perso.pvMax_perso, perso.perception_perso, perso.recup_perso, perso.paMax_perso, perso.protec_perso, perso.chargeMax_perso, perso.message_perso,
										perso.description_perso, perso.clan, camp.name as camp_name, perso.chef, grades.id_grade, grades.nom_grade, grades.image_grade,
										type_unite.id_unite, type_unite.nom_unite, type_unite.description_unite, type_unite.image_unite, type_unite.img_extension, type_unite.pv_unite, type_unite.pm_unite, type_unite.pa_unite, type_unite.perception_unite, type_unite.recup_unite")
								->where("perso.id_perso",$id)
								->leftJoin("type_unite","type_unite.id_unite","=","perso.type_perso")
								->leftJoin("perso_as_grade","perso_as_grade.id_perso","=","perso.id_perso")
								->leftJoin("grades","perso_as_grade.id_grade","=","grades.id_grade")
								->leftJoin("camp","camp.id","=","perso.clan")
								->get()[0];

		// on contrôle que le perso appartient bien au joueur
		if($character->idJoueur_perso !=$_SESSION['ID_joueur']){
			throw new Exception('Page non autorisée',403);
		}
		
		if($character->camp_name=="Nord"){$camp="north";};
		if($character->camp_name=="Sud"){$camp="south";};
		
		// on récupère les bâtiments de respawn
		$allowedRespawns = new Building();
		$allowedRespawns = $allowedRespawns->select('instance_batiment.id_instanceBat, instance_batiment.nom_instance, instance_batiment.pv_instance, instance_batiment.x_instance, instance_batiment.y_instance, batiment.id_batiment, batiment.nom_batiment, batiment.image_prefix, batiment.capacity')
											->where('batiment.respawn_allowed',1)
											->where('instance_batiment.camp_instance',$character->clan)
											->leftJoin('batiment','batiment.id_batiment','=','instance_batiment.id_batiment')
											->orderBy('batiment.respawn_order DESC')
											->get();
		
		$respawnBuildings = [];
		
		foreach($allowedRespawns as $respawn){
			if(!isset($respawnBuildings[$respawn->id_batiment]['name'])){
				$respawnBuildings[$respawn->id_batiment]['name'] = $respawn->nom_batiment;
			}
			$respawnBuildings[$respawn->id_batiment]['buildings'][] = $respawn;
		}

		$characterRespawns = $characterModel->respawns($character->id_perso);
		$characRespawns = [];
		
		foreach($characterRespawns as $respawn){
			$characRespawns[] = $respawn['id_instance_bat'];
		}
		
		//on récupère les armes et accessoires
		$characterWeapons = new Weapon();
		$characterWeapons = $characterWeapons->select("arme.id_arme, arme.nom_arme, arme.image_arme, arme.porteeMax_arme, perso_as_arme.est_portee")
											->where("perso_as_arme.id_perso",$character->id_perso)
											->leftJoin("perso_as_arme","perso_as_arme.id_arme","=","arme.id_arme")
											->orderBy("arme.porteeMax_arme")
											->get();
		
		$characterEquipments = new Item();
		$characterEquipments = $characterEquipments->select("objet.id_objet, objet.nom_objet, objet.description_objet, objet.image_objet, objet.type_objet, camps, perso_as_objet.equip_objet")
											->where("perso_as_objet.id_perso",$character->id_perso)
											->where("objet.type_objet","E")
											->leftJoin("perso_as_objet","perso_as_objet.id_objet","=","objet.id_objet")
											// ->orderBy("arme.porteeMax_arme")
											->get();
		
		$equippedWeapons = [];
		$inBagWeapons = [];
		$inBagWeaponsIds = [];
		$allowedWeaponsIds = [];
		
		foreach($characterWeapons as $weapon){
			if($weapon->est_portee == 1){
				$equippedWeapons[] = $weapon;
			}else{
				$inBagWeapons[] = $weapon;
				$inBagWeaponsIds[] = $weapon->id_arme;
			}
		}
		
		$allowedWeapons = new Weapon();
		$allowedWeapons = $allowedWeapons->select("arme.id_arme")->where("arme_as_type_unite.id_type_unite",$character->id_unite)->leftJoin("arme_as_type_unite","arme_as_type_unite.id_arme","=","arme.id_arme")->get();
		
		foreach($allowedWeapons as $allowedWeapon){
			$allowedWeaponsIds[] = $allowedWeapon->id_arme;
		}
		
		$equippedEquipments = [];
		$inBagEquipments = [];
		$inBagEquipmentsIds = [];
		$allowedEquipmentsIds = [];
		
		foreach($characterEquipments as $equipment){
			if($equipment->equip_objet == 1){
				$equippedEquipments[] = $equipment;
			}else{
				$inBagEquipments[] = $equipment;
				$inBagEquipmentsIds[] = $equipment->id_objet;
			}
		}
		
		$allowedEquipments = new Item();
		$allowedEquipments = $allowedEquipments->select("objet.id_objet")->where("objet_as_type_unite.id_type_unite",$character->id_unite)->leftJoin("objet_as_type_unite","objet_as_type_unite.id_objet","=","objet.id_objet")->get();
		
		foreach($allowedEquipments as $allowedEquipment){
			$allowedEquipmentsIds[] = $allowedEquipment->id_objet;
		}
		
		// upgrade costs
		$costsModel = new Unit();
		$pvCost = $costsModel->upgradeCosts('pv',$character->pvMax_perso,$character->pv_unite);
		$pmCost = $costsModel->upgradeCosts('pm',$character->pmMax_perso,$character->pm_unite);
		$paCost = $costsModel->upgradeCosts('pa',$character->paMax_perso,$character->pa_unite);
		$perceptionCost = $costsModel->upgradeCosts('perception',$character->perception_perso,$character->perception_unite);
		$recupCost = $costsModel->upgradeCosts('recup',$character->recup_perso,$character->recup_unite);
		
		return require_once('../mvc/view/character/show.php');
    }
	
	/**
     * Store the character in the database
     *
     * @return view or redirect
     */
    public function store()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){

			// Validation du formulaire
			
			$errors =[];
			
			$errors = formValidator::validate([

			]);

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = ['class'=>'danger','message'=>'le formulaire comporte une ou plusieurs erreurs'];
				
				header('location:?action=create');
				die();
			}else{
				// on nettoie les données

					
				// on stocke
				$result = '';
				
				if($result){
					$_SESSION['flash'] = ['class'=>'success','message'=>'La compagnie '.$_POST['name'].' a bien été créée'];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				header('location:?action=create');
				die();
			}
		}else{
			header('location:?action=create');
			die();
		}
    }
	
	/**
     * Display the character edit page.
     *
     * @param $id
     * @return view
     */
    public function edit(int $id)
    {
		return $this->show($id);
    }
	
	/**
     * Store the updated character in the database
     *
	 * @param $id
     * @return redirect response
     */
    public function update(int $id)
	{
		$redirect = '?action=character&op=show';
		
		if($_SERVER['REQUEST_METHOD']!=='POST'){
			header('location:'.$redirect);
			die();
		}

		$dateTime = new DateTime();

		$characterModel = new Character();
		$character = $characterModel->select("perso.id_perso, perso.idJoueur_perso, perso.xp_perso, perso.pi_perso, perso.pc_perso, perso.pmMax_perso,
										perso.pvMax_perso, perso.perception_perso, perso.recup_perso, perso.paMax_perso, perso.protec_perso, perso.chargeMax_perso, perso.message_perso,
										perso.description_perso, perso.clan, perso.date_renvoi,
										type_unite.id_unite, type_unite.pv_unite, type_unite.pm_unite, type_unite.pa_unite, type_unite.perception_unite, type_unite.recup_unite, type_unite.cout_pg")
								->where("perso.id_perso",$id)
								->leftJoin("type_unite","type_unite.id_unite","=","perso.type_perso")
								->get();
		$character = $character[0];
		
		$unsetAttributes = [
			'id_unite',
			'pv_unite',
			'pm_unite',
			'pa_unite',
			'perception_unite',
			'recup_unite',
			'cout_pg'
		];
		
		if($character->idJoueur_perso !=$_SESSION['ID_joueur']){
			throw new Exception('Page non autorisée',403);
		}

		// Validation du formulaire
		
		$errors =[];

		switch($_POST['form']){
			case 'upgradeCapacities':
				// upgrade costs
				$costsModel = new Unit();
				$pvCost = $costsModel->upgradeCosts('pv',$character->pvMax_perso,$character->pv_unite);
				$pmCost = $costsModel->upgradeCosts('pm',$character->pmMax_perso,$character->pm_unite);
				$paCost = $costsModel->upgradeCosts('pa',$character->paMax_perso,$character->pa_unite);
				$perceptionCost = $costsModel->upgradeCosts('perception',$character->perception_perso,$character->perception_unite);
				$recupCost = $costsModel->upgradeCosts('recup',$character->recup_perso,$character->recup_unite);

				$redirect = '?action=character&op=show&id='.$character->id_perso;
				$tab = null;
				$inputs = [
						'pv_cost' => [['bail','not_required','numeric','same:'.$pvCost,'max:'.$character->pi_perso],'points de vie'],
						'pm_cost' => [['bail','not_required','numeric','same:'.$pmCost,'max:'.$character->pi_perso],"points de mouvement"],
						'pa_cost' => [['bail','not_required','numeric','same:'.$paCost,'max:'.$character->pi_perso],"points d'action"],
						'percep_cost' => [['bail','not_required','numeric','same:'.$perceptionCost,'max:'.$character->pi_perso],"perception"],
						'recup_cost' => [['bail','not_required','numeric','same:'.$recupCost,'max:'.$character->pi_perso],"récupération"]
					];
				break;
			case 'respawns':
				$redirect = '?action=character&op=show&id='.$character->id_perso;
				$tab = null;
				
				$allowedRespawns = new Building();
				$allowedRespawns = $allowedRespawns->select('instance_batiment.id_instanceBat, batiment.id_batiment, batiment.nom_batiment')
													->where('batiment.respawn_allowed',1)
													->where('instance_batiment.camp_instance',$character->clan)
													->leftJoin('batiment','batiment.id_batiment','=','instance_batiment.id_batiment')
													->orderBy('batiment.respawn_order DESC')
													->get();
				
				$inputs = [];
				foreach($allowedRespawns as $respawn){
					if($_POST[$respawn->id_batiment.'_select']!=0){
						$inputs[$respawn->id_batiment.'_select'] = [['bail','not_required','numeric','exists:instance_batiment,id_instanceBat'],''];
					}
				}
				break;
			case 'activateCharacter':
				$redirect = '?action=character';
				$tab = null;
				$inputs = [
						'activationBtn' => [['bail','required','string','in:activate,desactivate'],''],
						'character' => [['bail','required','numeric','same:'.$character->id_perso],''],
					];
				break;
			case 'changeNameForm':
				$redirect = '?action=character&op=show&id='.$character->id_perso;
				$tab = null;
				$inputs = [
						'changeNameInput' => [['bail','required','regex:/^[A-Za-zÀ-ÖØ-öø-ÿœŒæÆçÇÉéÈèÊêËëÀàÂâÎîÏïÔôÛûÙùÜüŸÿ][A-Za-zÀ-ÖØ-öø-ÿœŒæÆçÇÉéÈèÊêËëÀàÂâÎîÏïÔôÛûÙùÜüŸÿ\'’\-–—"«» ]{2,49}$/u'],''],
						'character' => [['bail','required','numeric','same:'.$character->id_perso],''],
					];
				break;
			case 'characDescForm':
				$redirect = '?action=character&op=show&id='.$character->id_perso;
				$tab = null;
				$inputs = [
						'character_desc' => [['bail','required','string','max:650'],'description'],
						'character' => [['bail','required','numeric','same:'.$character->id_perso],''],
					];
				break;
			case 'dailyMsgForm':
				$redirect = '?action=character&op=show&id='.$character->id_perso;
				$tab = null;
				$inputs = [
						'character_message' => [['bail','required','string','max:125'],'message du jour'],
						'character' => [['bail','required','numeric','same:'.$character->id_perso],''],
					];
				break;
			default:
			$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
			header('location:?action=character&op=show&id='.$character->id_perso);
			die();
		}

		$errors = formValidator::validate($inputs);

		// Si le formulaire contient des erreurs on renvoie :
		// les erreurs, les anciennes données
		// et on redirige vers la page de création

		if (!empty($errors)){
			$_SESSION['old_input'] = $_POST;
			$_SESSION['errors'] = $errors;
			$_SESSION['flash'] = [
				'class'=>'danger',
				'message'=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur",
				'tab'=>$tab];
			
			header('location:'.$redirect);
			die();
		}else{
			// on nettoie les données et on les injecte dans le modèle
			$sanitizer = new formValidator();
			switch($_POST['form']){
				case 'upgradeCapacities':
					$upgradeResult = (new CharacterService())->upgradeCapacities($character,$_POST,$unsetAttributes);

					if($upgradeResult){
						$_SESSION['flash'] = [
						'class'=> $upgradeResult->css_class,
						'message'=> $upgradeResult->message,
						'tab'=>$tab
						];
					}
					header('location:'.$redirect);
					die();
					break;
				case 'respawns':
					$respawnsResult = (new CharacterService())->upgradeRespawns($character,$_POST,$allowedRespawns);

					if($respawnsResult){
						$_SESSION['flash'] = [
						'class'=> $respawnsResult->css_class,
						'message'=> $respawnsResult->message,
						'tab'=>$tab
						];
					}
					header('location:'.$redirect);
					die();
					break;
				case 'activateCharacter':
					$activationResult = (new CharacterService())->activateToggle($character,$_POST,$unsetAttributes);
					if($activationResult){
						$_SESSION['flash'] = [
						'class'=> $activationResult->css_class,
						'message'=> $activationResult->message,
						'tab'=>$tab
						];
					}
					
					header('location:'.$redirect);
					die();
					break;
				case 'changeNameForm':
					$newName = new Character();
					$normalizedName = $newName->normalizeName($_POST['changeNameInput']);
					$newName = $newName->isNameTooClose($_POST['changeNameInput']);
					
					if($newName["match"]){
						$_SESSION['flash'] = [
							"class" => "warning",
							"message"=>"Modification impossible. Un autre perso porte un nom similaire ou identique",
							'tab'=>$tab
						];
						header('location:'.$redirect);
						die();
					}
					
					$character->nom_perso = $_POST['changeNameInput'];
					$character->normalized_name = $normalizedName;
					$msg = "Votre personnage s'appelle désormais ".$character->nom_perso;
					
					unset($character->id_unite,$character->pv_unite,$character->pm_unite,$character->pa_unite,$character->perception_unite,$character->recup_unite, $character->cout_pg);
					
					if(!$character->update()){
						$_SESSION['flash'] = [
							"class" => "warning",
							"message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
							'tab'=>$tab
						];
					}else{
						$_SESSION['flash'] = [
							'class'=>'success',
							'message'=> $msg,
							'tab'=>$tab
						];
					}
					header('location:'.$redirect);
					die();
					break;
				case 'characDescForm':
					$character->description_perso = $sanitizer->sanitize($_POST['character_desc']);
					$msg = "La description de votre personnage a été mise à jour";
					
					unset($character->id_unite,$character->pv_unite,$character->pm_unite,$character->pa_unite,$character->perception_unite,$character->recup_unite, $character->cout_pg);
					
					if(!$character->update()){
						$_SESSION['flash'] = [
							"class" => "warning",
							"message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
							'tab'=>$tab
						];
					}else{
						$_SESSION['flash'] = [
							'class'=>'success',
							'message'=> $msg,
							'tab'=>$tab
						];
					}
					header('location:'.$redirect);
					die();
					break;
				case 'dailyMsgForm':
					$character->message_perso = $sanitizer->sanitize($_POST['character_message']);
					$msg = "Le message du jour de votre personnage a été défini";
					
					unset($character->id_unite,$character->pv_unite,$character->pm_unite,$character->pa_unite,$character->perception_unite,$character->recup_unite, $character->cout_pg);
					
					if(!$character->update()){
						$_SESSION['flash'] = [
							"class" => "warning",
							"message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
							'tab'=>$tab
						];
					}else{
						$_SESSION['flash'] = [
							'class'=>'success',
							'message'=> $msg,
							'tab'=>$tab
						];
					}
					header('location:'.$redirect);
					die();
					break;
				default:
					$_SESSION['flash'] = [
						"class" => "warning",
						"message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
						'tab'=>$tab
					];
					header('location:'.$redirect);
					die();
			}
			
			$_SESSION['old_input'] = $_POST;
			$_SESSION['flash'] = [
				"class" => "warning",
				"message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
				'tab'=>$tab
				];
			header('location:'.$redirect);
			die();
		}
    }
	
	/**
     * delete the character from database
     *
	 * @param $id
     * @return redirect
     */
    public function destroy($id)
    {	
		// supprimer le perso
		
		header('location:?');
		die();
    }
}