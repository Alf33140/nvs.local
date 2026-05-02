<?php
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Company.php");
require_once("../mvc/model/Bank.php");
require_once("../mvc/model/Account.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class CommandController extends Controller
{
    /**
     * Display the command staff index.
     *
     * @return view
     */
    public function index(Character $perso)
    {
		if ($perso->clan==1) {
			$image_em = "em_nord.png";
		} else {
			$image_em = "em_sud.png";
		}
		
		// Liste des membres de l'etat major de ce camp
		$em_members = new Character();
		$em_members = $em_members->select('id_perso, nom_perso')->where('clan',$perso->clan)->where('etat_major',1)->get();

		$nb_persos_em = count($em_members);
		$majorite_em = ceil($nb_persos_em/2+1);
		
		$companyModel = new Company();
		
		// Liste des compagnies du camp
		$companies = $companyModel->select('compagnies.id_compagnie, compagnies.nom_compagnie, compagnies.image_compagnie, compagnies.resume_compagnie, compagnies.description_compagnie, compagnies.genie_civil, count(perso_in_compagnie.id_perso) as countMembers')
						->leftJoin('perso_in_compagnie','compagnies.id_compagnie','=','perso_in_compagnie.id_compagnie')
						->where('compagnies.id_clan',$perso->clan)
						->groupBy('compagnies.id_compagnie')
						->get();

		// Récupération de toutes les demandes de création de compagnie
		$company_demands = $companyModel->creationDemands($perso->clan);
		$waiting_votes =0;
		
		foreach($company_demands as $demand){
			$voteResults = [];
			$votes = $companyModel->creationVotes($demand->id);
			
			if($demand->votes_result==0 OR is_null($demand->votes_result)){
				$waiting_votes ++;
			}
			
			foreach($votes as $vote){
				if($vote->id_em_perso==$perso->id_perso){
					$demand->alreadyVoted = 1;
				}
				$voteResults[] = ['id_perso'=>$vote->id_em_perso,'vote'=>$vote->vote];
			}
			$demand->individualVotes = $voteResults;
		}
	
		return require_once('../mvc/view/command/index.php');
    }
	
	/**
     * Store the Command vote for the company creation in the database
     * @param Character class
     * @return redirect
     */
    public function vote(Character $perso)
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){

			// Validation du formulaire
			$errors =[];
			
			$errors = formValidator::validate([
				'compVoteId' => [['bail','required','numeric'],'Id compagnie'],
				'compVoteOption' => [['bail','required','numeric','in:-1,0,1'],'Vote'],
			]);

			// Si le formulaire contient des erreurs on renvoie les erreurs
			// et on redirige vers la page de création

			if (!empty($errors)){
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = ['class'=>'danger','message'=>'Une erreur est survenue pendant le vote. Veuillez recommencer'];
				
				header('location:?');
				die();
			}else{
				// on nettoie les données
				$companyId = intval($_POST['compVoteId']);
				$voteOption = intval($_POST['compVoteOption']);
				
				$company_Vote = new Company();
				
				//on vérifie que le perso n'a pas déjà voté
					// A FAIRE
					
				// on stocke le vote
				$result = $company_Vote->validationVote($companyId,$perso->id_perso,$voteOption);
				
				if($result){
					$_SESSION['flash'] = ['class'=>'success','message'=>"le vote a bien été pris en compte"];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				header('location:?');
				die();
			}
		}else{
			header('location:?');
			die();
		}
    }
	
	/**
     * manage the votes results for the company creation in the database
     * @param Character class
     * @return redirect
     */
    public function compValidation()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){

			// Validation du formulaire
			$errors =[];
			
			$errors = formValidator::validate([
				'compId' => [['bail','required','numeric'],'Id compagnie'],
				'validateChoice' => [['bail','required','numeric','in:-1,0,1'],'Choix de validation'],
			]);

			// Si le formulaire contient des erreurs on renvoie les erreurs
			// et on redirige vers la page de création

			if (!empty($errors)){
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = ['class'=>'danger','message'=>'Une erreur est survenue. Veuillez recommencer'];
				
				header('location:?');
				die();
			}else{
				// on nettoie les données
				$companyId = intval($_POST['compId']);
				$validateChoice = intval($_POST['validateChoice']);
				
				// Récupération de la demande de création de compagnie
				$companyModel = new Company();
				$demand = $companyModel->demand($companyId);
				
				switch($validateChoice){
					case -1:
						//mise à jour du résultat du vote
						$result = $companyModel->votesResult($demand->id,-1);
						$_SESSION['flash'] = ['class'=>'danger','message'=>'Vous avez refusé la création de la compagnie "'.$demand->nom_compagnie.'"'];
						break;
					case 0:
						//mise à jour du résultat du vote et reset
						$result = $companyModel->votesResult($demand->id,0);
						$reset = $companyModel->resetVotes($demand->id);
						$_SESSION['flash'] = ['class'=>'warning','message'=>'Vous avez réinitialisé le vote de création de la compagnie "'.$demand->nom_compagnie.'"'];
						break;
					case 1:
						// Création de la compagnie
						$newCompany = new Company();
						$newCompany->nom_compagnie = $demand->nom_compagnie;
						$newCompany->description_compagnie = $demand->description_compagnie;
						$newCompany->resume_compagnie = '';
						$newCompany->id_clan = $demand->camp;
						
						$newCompany = $newCompany->saveWithModel();

						// Création des types d'unités autorisés dans la compagnie -- A optimiser
						$allowedUnits = $newCompany->allowUnits([1,2,3,4,5,7,8]);
						
						// Définir le perso demandeur comme chef de compagnie
						$assignPerso = $newCompany->assignPerso($demand->id_perso,1,0);
						
						// Créer la banque de compagnie et créer un compte au chef de compagnie
						$bank = new Bank();
						$bank->id_compagnie = $newCompany->id_compagnie;
						$bank = $bank->saveWithModel();
						
						$account = new Account();
						$account->id_perso = $demand->id_perso;
						$account->bank_id = $bank->id;
						$account = $account->save();
						
						//mise à jour du résultat du vote
						$result = $companyModel->votesResult($demand->id,1);
						$_SESSION['flash'] = ['class'=>'success','message'=>'La compagnie "'.$demand->nom_compagnie.'" a bien été créée'];
						break;
					default:
						$_SESSION['flash'] = ['class'=>'danger','message'=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				header('location:?');
				die();
			}
		}else{
			header('location:?');
			die();
		}
    }
	
	/**
     * Soft delete the company demand. Keep it in database
     * @param $id
     * @return redirect
     */
    public function deleteDemand(int $id)
    {
		$companyModel = new Company();
		$result = $companyModel->deleteForCommand($id);
		
		if($result){
			$_SESSION['flash'] = ['class'=>'success','message'=>'La demande de compagnie a été retirée'];
		}else{
			$_SESSION['flash'] = ['class'=>'danger','message'=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
		}
		header('location:?');
		die();
    }
	
	/**
     * delete totally the company. irreversible
     * @param $id
     * @return redirect
     */
    public function deleteComp(int $id)
    {
		$company = new Company();
		$company = $company->select('compagnies.id_compagnie,compagnies.nom_compagnie,banque_as_compagnie.id as bank_ID')->leftJoin('banque_as_compagnie','compagnies.id_compagnie','=','banque_as_compagnie.id_compagnie')->find($id);
		
		$company_name = $company->nom_compagnie;

		// Virer les persos de la compagnie
		$dismissAll = $company->dismissAll();
		
		// Effacer l'historique et les logs de la banque de compagnie, virer les persos et supprimer la banque
		$bank = new Bank();
		$bank = $bank->find($company->bank_ID);
		$account = new Account();
		
		$accountsDeleted = $account->where('bank_id',$bank->id)->delete();
		if($accountsDeleted){
			$historyDeleted = $bank->deleteHistory();
			$logsDeleted = $bank->deleteBankLog();
		}
		if($historyDeleted AND $logsDeleted){
			$bankDeleted = $bank->delete();
		}
		if($bankDeleted){
			$companyDeleted = $company->delete();
		}
		
		if($companyDeleted){
			$_SESSION['flash'] = ['class'=>'success','message'=>'La compagnie "'.$company_name.'" a bien été supprimée'];
		}else{
			$_SESSION['flash'] = ['class'=>'danger','message'=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
		}
		header('location:?');
		die();
    }
}