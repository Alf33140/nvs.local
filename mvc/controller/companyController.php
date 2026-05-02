<?php
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Company.php");
require_once("../mvc/model/Account.php");
require_once("../mvc/model/Bank.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class CompanyController extends Controller
{	
    /**
     * Display the company index.
     *
     * @return view
     */
    public function index()
    {
		$character = new Character();
		$character = $character->select('perso.id_perso, camp.name as camp_name, camp.id as camp_id, perso_in_compagnie.id_compagnie')
								->leftJoin('perso_in_compagnie','perso_in_compagnie.id_perso','=','perso.id_perso')
								->leftJoin('camp','camp.id','=','perso.clan')
								->where('perso.id_perso',$_SESSION["id_perso"])
								->get();
		$character = $character[0];
		
		$companies = new Company();
		$companies = $companies->select('compagnies.id_compagnie, compagnies.nom_compagnie, compagnies.image_compagnie, compagnies.resume_compagnie, compagnies.description_compagnie, compagnies.genie_civil, count(perso_in_compagnie.id_perso) as countMembers')
						->leftJoin('perso_in_compagnie','compagnies.id_compagnie','=','perso_in_compagnie.id_compagnie')
						->where('compagnies.id_clan',$character->camp_id)
						->groupBy('compagnies.id_compagnie')
						->get();
		
		if(isset($character->id_compagnie) AND $character->id_compagnie>0 AND !isset($_GET['all'])){
			$redirect = 'location:?action=show&id='.$character->id_compagnie;
			header($redirect);
			die();
		}
		return require_once('../mvc/view/company/index.php');
    }
	
	/**
     * Display the company join process.
     *
     * @return view
     */
    public function joinComp(int $id)
    {
		$character = new Character();
		$character = $character->select('perso.id_perso, camp.name as camp_name, camp.id as camp_id, perso.type_perso, perso_in_compagnie.id_compagnie, perso_demande_anim.type_demande')
								->leftJoin('perso_in_compagnie','perso_in_compagnie.id_perso','=','perso.id_perso')
								->leftJoin('camp','camp.id','=','perso.clan')
								->leftJoin('perso_demande_anim','perso.id_perso','=','perso_demande_anim.id_perso')
								->where('perso.id_perso',$_SESSION["id_perso"])
								->get();
		$character = $character[0];
		
		// le perso ne peut pas rejoindre une compagnie s'il a fait une demande de changement de camp
		if($character->type_demande==4){
			$_SESSION['flash'] = ["class" => "warning","message"=>"Vous ne pouvez pas rejoindre une compagnie car vous avez fait une demande de changement de camp"];
				
			header('location:?');
			die();
		}
		
		$company = new Company();
		$company = $company->select('compagnies.id_compagnie, compagnies.id_clan, compagnies.nom_compagnie, compagnies.image_compagnie, compagnies.resume_compagnie, compagnies.description_compagnie, compagnies.genie_civil, compagnies.capacity, banque_as_compagnie.id as id_bank')
						->leftJoin('banque_as_compagnie','compagnies.id_compagnie','=','banque_as_compagnie.id_compagnie')
						->where('compagnies.id_compagnie',$id)
						->get();
		$company = $company[0];
		
		/* contrôle si le perso est du bon camp */
		if($character->camp_id!=$company->id_clan){
			$_SESSION['flash'] = ['class'=>'danger','message'=>"Cette compagnie n'existe pas ou ne fait pas partie de votre camp"];
			
			$result = false;
			header('location:?');
			die();
		}
		
		/* contrôle si le perso a déjà fait une demande ou appartient déjà à une compagnie */
		if(isset($character->id_compagnie)){
			if($character->id_compagnie==$company->id_compagnie){
				$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous appartenez déjà à cette compagnie"];
			}else{
				$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous avez déjà fait une demande ou vous êtes déjà dans une compagnie"];
			}
				
			header('location:?');
			die();
		}
		
		/* contrôle s'il reste de la place dans la compagnie */
		$companyModel = new Company();
		$countMembers = $companyModel->countMembers($company->id_compagnie);
		
		if($company->capacity<=$countMembers){
			$_SESSION['flash'] = ['class'=>'danger','message'=>"La compagnie est complète. Vous ne pouvez pas la rejoindre"];
			header('location:?');
			die();
		}

		/* contrôle si la compagnie accepte ce type d'unité */
		$allowedUnit = $companyModel->checkUnit($company->id_compagnie,$character->type_perso);
		
		if(!$allowedUnit){
			$_SESSION['flash'] = ['class'=>'danger','message'=>"Cette compagnie n'accepte pas ce type d'unité"];
			header('location:?');
			die();
		}

		/* Si tous les contrôles sont bons intégrer le perso dans la compagnie. */
		$addPerso = $company->assignPerso($character->id_perso,10,1);
		header('location:?');
		die();
    }
	
	/**
     * Display the company quit process.
     *
	 * @param  $companyID id of the company
	 * @param  $memberID id of the character
     * @return view
     */
    public function quitComp(int $companyID, int $memberID)
    {
		// traiter l'annulation de demande d'intégration
		// traiter la demande de quitter la compagnie
		
		$sessionCharacter = new Character();
		$sessionCharacter = $sessionCharacter->select('perso.id_perso, perso_in_compagnie.id_compagnie, perso_in_compagnie.attenteValidation_compagnie, poste.role_level')
								->leftJoin('perso_in_compagnie','perso_in_compagnie.id_perso','=','perso.id_perso')
								->leftJoin('poste','perso_in_compagnie.poste_compagnie','=','poste.id_poste')
								->where('perso.id_perso',$_SESSION["id_perso"])
								->get();
		$sessionCharacter = $sessionCharacter[0];
		
		$character = new Character();
		$character = $character->select('perso.id_perso, perso_in_compagnie.id_compagnie, perso_in_compagnie.attenteValidation_compagnie, poste.role_level')
								->leftJoin('perso_in_compagnie','perso_in_compagnie.id_perso','=','perso.id_perso')
								->leftJoin('poste','perso_in_compagnie.poste_compagnie','=','poste.id_poste')
								->where('perso.id_perso',$memberID)
								->get();
		$character = $character[0];
		
		// Si ce n'est pas le perso qui demande ou que ce n'est pas un membre autorisé on annule l'action
		if($character->id_perso<>$_SESSION["id_perso"] AND !in_array($sessionCharacter->role_level,[1,2,4])){
			$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous n'avez pas l'autorisation de faire cette action"];
			header('location:?');
			die();
		}
		
		$company = new Company();
		$company = $company->select('compagnies.id_compagnie, compagnies.nom_compagnie, compagnies.image_compagnie, compagnies.resume_compagnie, compagnies.description_compagnie, compagnies.genie_civil, compagnies.capacity, banque_as_compagnie.id as id_bank')
						->leftJoin('banque_as_compagnie','compagnies.id_compagnie','=','banque_as_compagnie.id_compagnie')
						->where('compagnies.id_compagnie',$companyID)
						->get();
		$company = $company[0];
		
		if(!isset($character->id_compagnie)){
			$msg = "Ce perso ne fait partie d'aucune compagnie";
			if($character->id_perso==$_SESSION["id_perso"]){
				$msg = "Vous ne faites partie d'aucune compagnie";
			}
			$_SESSION['flash'] = ['class'=>'danger','message'=>$msg];
			header('location:?');
			die();
		}
		
		if($character->attenteValidation_compagnie==1){
			$msg = 'Vous avez annulé la demande d\'intégration du perso <span class="fw-semibold">'.$character->id_perso.'</span> dans la compagnie "<span class="fst-italic">'.$company->nom_compagnie.'</span>"';
			if($character->id_perso==$_SESSION["id_perso"]){
				$msg = 'Vous avez annulé votre demande d\'intégration dans la compagnie "<span class="fst-italic">'.$company->nom_compagnie.'</span>';
			}
			$dismissPerso = $company->dismissPerso($character->id_perso);
		}else{
			// si c'est le chef, il doit donner son poste à un autre ou s'il est seul il doit supprimer la compagnie
			if($character->role_level==1){
				// seul le chef peut se faire démissionner
				if($character->id_perso<>$_SESSION["id_perso"]){
					$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous ne pouvez pas supprimer le chef !"];
					header('location:?action=show&id='.$company->id_compagnie);
					die();
				}
				
				$companyModel = new Company();
				$countMembers = $companyModel->countMembers($company->id_compagnie);
				
				if($countMembers>1){
					$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous devez d'abord choisir un nouveau chef avant de quitter la compagnie"];
					header('location:?action=show&id='.$company->id_compagnie);
					die();
				}else{
					$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous êtes le dernier membre. Faites une demande de suppression de compagnie dans la page d'administration"];
					header('location:?action=show&id='.$company->id_compagnie);
					die();
				}
			}
			
			// Contrôle si le perso a des dettes
			$account = new Account();
			$account = $account->select('id, montant, montant_emprunt')->where('id_perso',$character->id_perso)->get();
			$account = $account[0];
			
			if($account->montant_emprunt>0){
				$msg = 'Le perso a des dettes et ne peut pas quitter la compagnie';
				if($character->id_perso==$_SESSION["id_perso"]){
					$msg = 'Vous ne pouvez pas quitter la compagnie si vous avez des dettes. Réglez vos dettes pour partir';
				}
				$_SESSION['flash'] = ['class'=>'danger','message'=>$msg];
				header('location:?action=show&id='.$company->id_compagnie);
				die();
			}

			if($character->id_perso<>$_SESSION["id_perso"]){
				// la demande provient d'un membre autorisé. On supprime le perso de la compagnie.
				$bank = new Bank();
				$bank = $bank->select('id, id_compagnie, montant')->find($company->id_bank);
				
				if($bank->id_compagnie<>$company->id_compagnie){
					$_SESSION['flash'] = ['class'=>'success','message'=>"une erreur est survenue. Si le problème persiste. Contactez un administrateur"];
					header('location:?');
				}

				// on supprime le compte du perso et on supprime le perso de la compagnie
				$account->delete();
				$bank = $bank->addBankLog($company->id_compagnie, $character->id_perso,5,$account->montant,$bank->montant,'le perso a quitté la compagnie', false);
				$dismissPerso = $company->dismissPerso($character->id_perso);
				
				$msg = 'Le perso'.$character->id_perso.' a été supprimé de la compagnie "<span class="fst-italic">'.$company->nom_compagnie.'</span>"';

			}else{
				// la demande provient du perso concerné. On fait une "demande" de quitter la compagnie.
				$msg = 'Vous avez fait la demande de quitter la compagnie "<span class="fst-italic">'.$company->nom_compagnie.'</span>"';
				$dismissPerso = $company->dismissPerso($character->id_perso,TRUE);
			}
		}
		
		$_SESSION['flash'] = ['class'=>'success','message'=>$msg];		
		header('location:?');
		die();
    }
	
	/**
     * Display the specified company.
     *
     * @param  $id
     * @return view
     */
    public function show(int $id)
    {
		$character = new Character();
		$character = $character->select('perso.id_perso, camp.name as camp_name, camp.id as camp_id, perso_in_compagnie.id_compagnie, perso_in_compagnie.attenteValidation_compagnie, poste.role_level')
					->leftJoin('perso_in_compagnie','perso_in_compagnie.id_perso','=','perso.id_perso')
					->leftJoin('camp','camp.id','=','perso.clan')
					->leftJoin('poste','perso_in_compagnie.poste_compagnie','=','poste.id_poste')
					->where('perso.id_perso',$_SESSION["id_perso"])->get();
		$character = $character[0];
		
		$debts = new Account();
		$debts = $debts->select('montant_emprunt')->where('id_perso',$character->id_perso)->get();
		
		if($debts){
			$debts = $debts[0];
		}
		
		$company = new Company();
		$company = $company->select('compagnies.id_compagnie, compagnies.id_clan, compagnies.nom_compagnie, compagnies.image_compagnie, compagnies.resume_compagnie, compagnies.description_compagnie, compagnies.genie_civil, banque_as_compagnie.id as id_bank')
						->leftJoin('banque_as_compagnie','compagnies.id_compagnie','=','banque_as_compagnie.id_compagnie')
						->where('compagnies.id_compagnie',$id)
						->get();
		$company = $company[0];
		
		if($character->camp_id!=$company->id_clan){
			$_SESSION['flash'] = ['class'=>'danger','message'=>"Cette compagnie n'existe pas ou ne fait pas partie de votre camp"];
			header('location:?all');
			die();
		}
		
		$companyMembers = new Character();
		$companyMembers = $companyMembers->select('perso.id_perso, perso.nom_perso, perso.x_perso, perso.y_perso, poste.nom_poste, poste.role_level, grades.nom_grade, grades.image_grade, perso_in_compagnie.attenteValidation_compagnie')
							->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')
							->leftJoin('poste','perso_in_compagnie.poste_compagnie','=','poste.id_poste')
							->leftJoin('perso_as_grade','perso.id_perso','=','perso_as_grade.id_perso')
							->leftJoin('grades','grades.id_grade','=','perso_as_grade.id_grade')
							->where('perso_in_compagnie.id_compagnie',$company->id_compagnie)
							->where('perso_in_compagnie.attenteValidation_compagnie',0)
							->orderBy('poste.id_poste')->get();
		
		$validationWaiting = new Character();
		$validationWaiting = $validationWaiting->select('perso_in_compagnie.id_perso, perso_in_compagnie.attenteValidation_compagnie as validation')
								->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')
								->where('perso_in_compagnie.id_compagnie',$company->id_compagnie)
								->whereIn('perso_in_compagnie.attenteValidation_compagnie',[1,2])
								->get();
		
		$waitingDemandIn = 0;
		$waitingDemandOut = 0;
		
		foreach($validationWaiting as $waiting){
			switch($waiting->validation){
				case 1:
					$waitingDemandIn++;
					;
				break;
				case 2:
					$waitingDemandOut++;
					;
				break;
			}
		}
		
		$waitingLoans = new Account();
		$waitingLoans = $waitingLoans->select('COUNT(*) as loans')
						->where('bank_id',$company->id_bank)
						->where('demande_emprunt',1)
						->get();
		$waitingLoans = $waitingLoans[0];
		
		$companyLeader = 'Non désigné';
		$companySecond = 'Non désigné';
		$companyRecruiter = 'Non désigné';
		$companyDiplomat = 'Non désigné';
		$companytreasurer = 'Non désigné';
		
		if(empty($company->image_compagnie)){
			$img_company = 'Sample_logo.png';
		}else{
			$img_company = $company->image_compagnie;
		}

		return require_once('../mvc/view/company/show.php');
    }
	
	/**
     * Store the company in the database
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
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
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
     * Display the company edit page.
     *
     * @param $id
     * @return view
     */
    public function edit(int $id)
    {	
    }
	
	/**
     * Store the updated company in the database
     *
	 * @param $id
     * @return redirect response
     */
    public function update(int $id)
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
				$_SESSION['flash'] = ['class'=>'danger','message'=>"le formulaire comporte une ou plusieurs erreurs"];
				
				header('location:?action=edit&id='.$id);
				die();
			}else{
				
				// on nettoie les données et on les injecte dans le modèle
				$sanitizer = new formValidator();
				
				if(true){
					$_SESSION['flash'] = ['class'=>'success','message'=>'La compagnie '.$_POST['name'].' a bien été éditée'];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				header('location:?action=edit&id='.$id);
			}
			
		}else{
			header("location:?action=edit&id=$id");
			die();
		}
    }
	
	/**
     * delete the company from database
     *
	 * @param $id
     * @return redirect
     */
    public function destroy($id)
    {	
		// virer tous les persos de la compagnie
		// vider les comptes et supprimer le compte en banque
		
		header('location:?');
		die();
    }
}