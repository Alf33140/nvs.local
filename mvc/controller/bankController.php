<?php
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Bank.php");
require_once("../mvc/model/Account.php");
require_once("../mvc/model/Notification.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class BankController extends Controller
{
    /**
     * Display the bank index.
     *
     * @return view
     */
    public function index()
    {		
		$id_perso = $_SESSION['id_perso'];
		$perso = new Character();
		$perso = $perso->select('perso.id_perso, bank_id, clan')->leftJoin('banque_compagnie','perso.id_perso','=','banque_compagnie.id_perso')->find($id_perso);
		
		return require_once('../mvc/view/bank/index.php');
    }
	
	/**
     * Display the bank creation page.
     *
     * @return view
     */
    public function create()
    {	
    }
	
	/**
     * Display the specified bank.
     *
     * @param  $id
     * @return view
     */
    public function show(int $id)
    {
		$bank = new Bank();
		$bank = $bank->select('*')->find($id);
		
		if(isset($bank) AND !empty($bank)){
			
			$id_perso = $_SESSION['id_perso'];
			$perso = new Character();
			$perso = $perso->select('perso.id_perso, clan, id_compagnie, or_perso')->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')->find($id_perso);
			
			if($perso->id_compagnie==$bank->id_compagnie){
				
				$companyMembers = new Character();
				$companyMembers = $companyMembers->select('perso.id_perso, nom_perso, clan, id_compagnie, or_perso')->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')->where('id_compagnie',$bank->id_compagnie)->where('perso.id_perso','<>',$perso->id_perso)->get();
			
				$account = new Account();
				$account = $account->where('id_perso',$perso->id_perso)->get();
				$account = $account[0];

				$overview_limit = 5;
				$bank_log = $bank->getBankLog($overview_limit,true,$perso->id_perso);
				$loans = $bank->getBankLog(10,true,$perso->id_perso,[2,3]);
				
				$remainingLoan = 0;
				
				if($loans){
					foreach($loans as $loan){
						switch($loan->operation){
							case 2:
								$remainingLoan += $loan->montant_transfert;
								break;
							case 3:
								$remainingLoan -= $loan->montant_transfert;
								break;
						}
					}
				}
				
				$accounts = new Account();
				$loansBalance = $accounts->select('SUM(montant_emprunt) as balance')->where('bank_id',$bank->id)->get();
				$loansBalance = $loansBalance[0]->balance;
				
				$disposableIncome = $bank->montant - $loansBalance;
				
				$antizerk = $bank->antiZerkDeposit($perso->id_perso);
				
				return require_once('../mvc/view/bank/show.php');
			}else{
				$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous n'avez pas accès à la banque de cette compagnie"];
				header('location:?');
				die();
			}
		}else{
				$_SESSION['flash'] = ['class'=>'warning','message'=>"Cette banque de compagnie n'existe pas ou a été supprimée"];
				header('location:?');
				die();
			}
		
    }
	
	/**
     * Display the specified account.
     *
     * @param  $id account ID
     * @return json
     */
    public function accountDetails(int $id)
    {
		$id_perso = $_SESSION['id_perso'];
		$perso = new Character();
		$perso = $perso->select('perso.id_perso, clan, id_compagnie, or_perso')->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')->find($id_perso);
		
		$account = new Account();
		$account = $account->select('banque_compagnie.id, banque_compagnie.id_perso, perso.nom_perso, bank_id, montant, id_compagnie')
							->leftJoin('perso_in_compagnie','banque_compagnie.id_perso','=','perso_in_compagnie.id_perso')
							->leftJoin('perso','banque_compagnie.id_perso','=','perso.id_perso')
							->where("banque_compagnie.id",$id)
							->get();
		$account = $account[0];
		
		if($perso->id_compagnie==$account->id_compagnie){
			
			$bank = new Bank();
			$bank = $bank->select('*')->find($account->bank_id);

			$overview_limit = 5;
			$bank_log = $bank->getBankLog($overview_limit,true,$account->id_perso);
			$loans = $bank->getBankLog(10,true,$account->id_perso,[2,3]);
			
			$remainingLoan = 0;
			
			if($loans){
				foreach($loans as $loan){
					switch($loan->operation){
						case 2:
							$remainingLoan += $loan->montant_transfert;
							break;
						case 3:
							$remainingLoan -= $loan->montant_transfert;
							break;
					}
				}
			}

			return json_encode([$account,$bank_log,$remainingLoan]);
			
		}else{
			// http_response_code(500);
			return json_encode(['error' => "Ce perso n'est pas de votre compagnie"]);
		}
    }
	
		/**
     * Display the specified bank for the treasurer
     *
     * @param  $id
     * @return view
     */
    public function treasury(int $id)
    {
		$bank = new Bank();
		$bank = $bank->select('*')->find($id);
		
		if(isset($bank) AND !empty($bank)){
			
			$id_perso = $_SESSION['id_perso'];
			$perso = new Character();
			$perso = $perso->select('perso.id_perso, clan, id_compagnie, poste_compagnie, or_perso')->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')->find($id_perso);
			
			if($perso->id_compagnie==$bank->id_compagnie AND ($perso->poste_compagnie==3 OR $perso->poste_compagnie==1)){
				
				$companyMembers = new Character();
				$companyMembers = $companyMembers->select('perso.id_perso, nom_perso, clan, id_compagnie, or_perso')->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')->where('id_compagnie',$bank->id_compagnie)->where('perso.id_perso','<>',$perso->id_perso)->get();
			
				$accounts = new Account();
				$accounts = $accounts->select('banque_compagnie.id, banque_compagnie.id_perso, banque_compagnie.bank_id, banque_compagnie.montant, banque_compagnie.demande_emprunt, banque_compagnie.montant_emprunt, perso.nom_perso')->where('bank_id',$bank->id)->leftJoin('perso','perso.id_perso','=','banque_compagnie.id_perso')->get();
				
				$loanDemands = [];
				$loansNumber = 0;
				$loansBalance = 0;
				
				foreach($accounts as $account){
					if($account->demande_emprunt==1){
						$loanDemands[] = ['id'=>$account->id,'id_perso'=>$account->id_perso,'nom_perso'=>$account->nom_perso,'montant'=>$account->montant,'montant_emprunt'=>$account->montant_emprunt];
					}
					if($account->montant_emprunt>0 AND $account->demande_emprunt==0){
						$loansNumber ++;
						$loansBalance += $account->montant_emprunt;
					}
				}
				
				$disposableIncome = $bank->montant - $loansBalance;

				$overview_limit = 15;
				$bank_log = $bank->getBankLog($overview_limit,true,null);
				
				$loans = $bank->getBankLog(10,true,null,[2,3]);
				
				$remainingLoan = 0;
				
				if($loans){
					foreach($loans as $loan){
						switch($loan->operation){
							case 2:
								$remainingLoan += $loan->montant_transfert;
								break;
							case 3:
								$remainingLoan -= $loan->montant_transfert;
								break;
						}
					}
				}
				
				$antizerk = $bank->antiZerkDeposit($perso->id_perso);
				
				return require_once('../mvc/view/bank/treasury.php');
			}else{
				$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous n'avez pas accès à la trésorerie de cette compagnie"];
				header('location:?');
				die();
			}
		}else{
				$_SESSION['flash'] = ['class'=>'warning','message'=>"Cette banque de compagnie n'existe pas ou a été supprimée"];
				header('location:?');
				die();
			}
		
    }
	
	/**
     * Store the bank in the database
     *
     * @return view or redirect
     */
    public function store()
    {
		/*
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
		*/
    }
	
	/**
     * Display the bank edit page.
     *
     * @param $id
     * @return view
     */
    public function edit(int $id)
    {	
    }
	
	/**
     * Store the updated bank in the database
     *
	 * @param $id
     * @return redirect response
     */
    public function update(int $id)
	{
		/*
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
				$_SESSION['flash'] =
					[
						'class'=>'danger',
						'message'=>"le formulaire comporte une ou plusieurs erreurs"
					];
				
				header('location:?action=edit&id='.$id);
				die();
			}else{
				
				// on nettoie les données et on les injecte dans le modèle
				$sanitizer = new formValidator();
				
				if(true){
					$_SESSION['flash'] = ['class'=>'success','message'=>'La compagnie '.$_POST['name'].' a bien été éditée'];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				header('location:?action=edit&id='.$id);
			}
			
		}else{
			header("location:?action=edit&id=$id");
			die();
		}
		*/
    }
	
	/**
     * Store the bank operations in the database
     *
	 * @param $id
     * @return redirect response
     */
    public function operation(int $id)
	{
		if($_SERVER['REQUEST_METHOD']==='POST'){

			// Validation du formulaire

			$errors =[];
			
			switch($_POST['operation']){
				case 'deposit':
					$minValue = 25;
					$opeVerb = 'déposé';
					$tab = 'operations';
					$resultSuccessClass= 'success';
					$redirect = 'action=show&id='.$id;
					break;
				case 'withdrawal':
					$minValue = 1;
					$opeVerb = 'retiré';
					$tab = 'operations';
					$resultSuccessClass= 'success';
					$redirect = 'action=show&id='.$id;
					break;
				case 'transfer':
					$minValue = 1;
					$opeVerb = 'transféré';
					$tab = 'operations';
					$resultSuccessClass= 'success';
					$redirect = 'action=show&id='.$id;
					break;
				case 'loan_demand':
					$minValue = 1;
					$opeVerb = 'demandé';
					$tab = 'loan';
					$resultSuccessClass= 'success';
					$redirect = 'action=show&id='.$id;
					break;
				case 'cancel_loan_demand':
					$loanCancelBtn = intval($_POST['loanCancel_btn']);
					$minValue = 1;
					$opeVerb = "annulé la demande d'emprunt de";
					$tab = 'loan';
					$resultSuccessClass= 'primary';
					$redirect = 'action=show&id='.$id;
					break;
				case 'loan_validation':
					$loanValidateBtn = intval($_POST['loanValidate_btn']);
					$minValue = 1;
					$tab = 'loan';
					$resultSuccessClass= 'primary';
					$redirect = 'action=treasury&id='.$id;
					break;
				default:
					$_SESSION['flash'] = ['class'=>'danger','message'=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur"];
					header('location:?action=show&id='.$id);
					die();
			}
			
			$errors = formValidator::validate([ 
				'value' => [['bail','required','numeric','min:'.$minValue.''],'Montant'],
				'id_company' => [['bail','required','numeric'],'Compagnie'],
				'id_perso' => [['bail','required','numeric'],'Perso'],
				'id_bank' => [['bail','required','numeric'],'banque'],
				'operation' => [['bail','required','in:deposit,withdrawal,transfer,loan_demand,cancel_loan_demand,loan_validation'],'opération'],
				'id_target' => [['bail','not_required','numeric'],'Bénéficiaire'],
			]);

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de formulaire

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] =
					[
						'class'=>'danger',
						'message'=>"l'opération a échouée. Erreurs détectées",
						'tab'=>$tab
					];

				header('location:?'.$redirect);
				die();
			}else{

				// on nettoie les données
				$sanitizer = new formValidator();
				$value = intval($_POST['value']);
				$companyId = intval($_POST['id_company']);
				$persoId = intval($_POST['id_perso']);
				$bankId = intval($_POST['id_bank']);
				$operation = $sanitizer->sanitize($_POST['operation']);
				$targetId = (isset($_POST['id_target']))?intval($_POST['id_target']):'';
				
				$bank = new Bank();
				$bank = $bank->select('*')->find($bankId);
				
				$accounts = new Account();
				$loansBalance = $accounts->select('SUM(montant_emprunt) as balance')->where('bank_id',$bank->id)->get();
				$loansBalance = $loansBalance[0]->balance;
				
				$disposableIncome = $bank->montant - $loansBalance;
				
				$id_perso = $_SESSION['id_perso'];
				$perso = new Character();
				$perso = $perso->select('perso.id_perso, nom_perso, id_compagnie, or_perso, poste_compagnie')->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')->find($id_perso);
				
				if($persoId==$perso->id_perso AND $perso->id_compagnie==$bank->id_compagnie){
					$account = new Account();
					$account = $account->where('id_perso',$perso->id_perso)->get();
					$account = $account[0];
					
					unset($perso->id_compagnie);
					
					switch($operation) {
						case 'deposit'://dépôt de thunes
							unset($perso->poste_compagnie);
							$antizerk = $bank->antiZerkDeposit($perso->id_perso);
							
							if($antizerk){
								$result = false;
								$message = "l'opération a échouée. Vous avez retiré de la thune il y a moins de 8h";
							}
							elseif($perso->or_perso<$value){
								$result = false;
								$message = "l'opération a échouée. Vous n'avez pas assez de thunes sur vous";
							}
							else{
								$loans = $bank->getBankLog(0,true,$perso->id_perso,[2,3]);
								$remainingLoan = 0;
								
								if($loans){
									foreach($loans as $loan){
										switch($loan->operation){
											case 2:
												$remainingLoan += $loan->montant_transfert;
												break;
											case 3:
												$remainingLoan -= $loan->montant_transfert;
												break;
										}
									}
								}
								
								$perso->or_perso -= $value;
								$bank->montant += $value;
								
								if($remainingLoan>0){
									if($value>=$remainingLoan){
										$deposit = $value-$remainingLoan;
										$account->montant += $deposit;
										$account->montant_emprunt = 0;
										
										$bank->addBanklog($bank->id_compagnie,$perso->id_perso,3,$remainingLoan,$bank->montant,'dernier versement');
										if($deposit>0){
											$bank->addBanklog($bank->id_compagnie,$perso->id_perso,0,$deposit,$bank->montant);
										}
										
										$account->montant_emprunt = 0;
										
									}else{
										$bank->addBanklog($bank->id_compagnie,$perso->id_perso,3,$value,$bank->montant);
									}
								}
								else{
									$account->montant += $value;
									$bank->addBanklog($bank->id_compagnie,$perso->id_perso,0,$value,$bank->montant);
								}
								
								$result1 = $perso->update();
								$result2 = $account->update();
								$result3 = $bank->update();
								
								if($result1 AND $result2 AND $result3){
									$result = True;
								}else{
									$result = false;
									$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
								}
							}
							break;
						case 'withdrawal'://retrait de thunes
							unset($perso->poste_compagnie);
							if($account->montant<$value){
								$result = false;
								$message = "l'opération a échouée. Vous n'avez pas assez de thunes en banque";
							}
							elseif($disposableIncome<$value){
								$result = false;
								$message = "l'opération a échouée. Il n'y a pas assez d'argent dans la banque de compagnie";
							}
							else{
								$perso->or_perso += $value;
								$account->montant -= $value;
								$bank->montant -= $value;
								
								$result1 = $perso->update();
								$result2 = $account->update();
								$result3 = $bank->update();
								
								if($result1 AND $result2 AND $result3){
									$bank->addBanklog($bank->id_compagnie,$perso->id_perso,1,$value,$bank->montant);
									$result = True;
								}else{
									$result = false;
									$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
								}
							}
							break;
						case 'transfer'://transfert à un autre joueur
							unset($perso->poste_compagnie);
							if($account->montant<$value){
								$result = false;
								$message = "l'opération a échouée. Vous n'avez pas assez de thunes en banque";
							}
							elseif($disposableIncome<$value){
								$result = false;
								$message = "l'opération a échouée. Il n'y a pas assez d'argent dans la banque de compagnie";
							}
							else{
								$target = new Character();
								$target = $target->select('perso.id_perso, nom_perso, id_compagnie, or_perso')->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')->find($targetId);
								
								if($targetId==$target->id_perso AND $target->id_compagnie==$bank->id_compagnie){
									
									$targetAccount = new Account();
									$targetAccount = $targetAccount->where('id_perso',$target->id_perso)->get();
									$targetAccount = $targetAccount[0];

									unset($target->id_compagnie);
									
									$targetAccount->montant += $value;
									$account->montant -= $value;
									
									$result1 = $targetAccount->update();
									$result2 = $account->update();
									
									if($result1 AND $result2){
										$bank->addBanklog($bank->id_compagnie,$perso->id_perso,4,$value,$bank->montant,'virement de '.$value.' thune(s) pour '.$target->nom_perso.' ['.$target->id_perso.']',1,$target->id_perso);
										$bank->addBanklog($bank->id_compagnie,$target->id_perso,4,$value,$bank->montant,'virement de '.$value.' thune(s) par '.$perso->nom_perso.' ['.$perso->id_perso.']',0,$target->id_perso);
										$result = True;
									}else{
										$result = false;
										$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
									}
								}else{
									$result = false;
									$message = "Le destinataire ne fait pas partie de votre compagnie";
								}
							}
							break;
						case 'loan_demand'://demande d'emprunt
							unset($perso->poste_compagnie);
							if($disposableIncome<$value){
								$result = false;
								$message = "l'opération a échouée. Il n'y a pas assez d'argent dans la banque de compagnie";
							}
							else{
								$account->demande_emprunt = 1;
								$account->montant_emprunt = $value;
								
								$result = $account->update();
								
								if($result){
									$result = True;
								}else{
									$result = false;
									$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
								}
							}
							break;
						case 'cancel_loan_demand'://annuler la demande d'emprunt
							unset($perso->poste_compagnie);
							if($loanCancelBtn<>1){
								$result = false;
								$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
							}
							else{
								$account->demande_emprunt = 0;
								$account->montant_emprunt = 0;
								
								$result = $account->update();
								
								if($result){
									$result = True;
								}else{
									$result = false;
									$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
								}
							}
							break;
						case 'loan_validation'://validation de la demande d'emprunt
							if($perso->poste_compagnie==1 OR $perso->poste_compagnie==3){
								unset($perso->poste_compagnie);
								$target = new Character();
								$target = $target->select('perso.id_perso, nom_perso, id_compagnie, or_perso')->leftJoin('perso_in_compagnie','perso.id_perso','=','perso_in_compagnie.id_perso')->find($targetId);

								if($targetId==$target->id_perso AND $target->id_compagnie==$bank->id_compagnie){
									
									$targetAccount = new Account();
									$targetAccount = $targetAccount->where('id_perso',$target->id_perso)->get();
									$targetAccount = $targetAccount[0];
									
									if($loanValidateBtn==-1){
										$lastDemandValue = $targetAccount->montant_emprunt;
										$targetAccount->demande_emprunt = 0;
										$targetAccount->montant_emprunt = 0;
										
										$result = $targetAccount->update();
										$notificationSubject = "Refus d'emprunt";
										$notificationMsg = "Bonjour ".$target->nom_perso.",
										J'ai le regret de t'annoncer que ta demande d'emprunt de ".$lastDemandValue." thune(s) a été refusée";
										$opeVerb = "annulé la demande d'emprunt de";
									}
									elseif($loanValidateBtn==1){
										
										$targetAccount->demande_emprunt = 0;
										$targetAccount->montant += $value;
										$opeVerb = "accepté la demande d'emprunt de";
										
										$result1 = $targetAccount->update();
										
										if($result1){
											$bank->addBanklog($bank->id_compagnie,$target->id_perso,2,$value,$bank->montant,'emprunt autorisé de '.$value.' thune(s)',0,$perso->id_perso);
											$result = True;
											$notificationSubject = "Validation d'emprunt";
											$notificationMsg = "Bonjour ".$target->nom_perso.",
											J'ai le plaisir de t'annoncer que ta demande d'emprunt de ".$value." thune(s) a été acceptée";
										}else{
											$result = false;
											$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
										}
									}else{
										$result = false;
										$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
									}
									
									if($result){

										$result = True;
										$notification = new Notification();
										$notification->id_expediteur = $perso->id_perso;
										$notification->expediteur_message = $perso->nom_perso;
										$notification->objet_message = $notificationSubject;
										$notification->contenu_message = $notificationMsg;
										$notification->date_message = new DateTime('now', new DateTimeZone('Europe/Paris'));
										$notification->date_message = $notification->date_message->format('Y-m-d H:i:s');
										
										$notification->saveWithModel();
										
										$notification->notificationHelper($notification->id_message,$target->id_perso);
									}else{
										$result = false;
										$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
									}
								}else{
									$result = false;
									$message = "Le destinataire ne fait pas partie de votre compagnie";
								}
							}else{
								$result = false;
									$message = "Vous n'avez pas l'autorisation de faire cette action";
							}
							break;
						default:
							$result = false;
							$message = "Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.";
					}
					
					if($result){
						$_SESSION['flash'] =
							[
								'class'=>$resultSuccessClass,
								'message'=>'opération réussie ! Vous avez '.$opeVerb.' '.$value.' thune(s)',
								'tab'=>$tab
							];
					}else{
						$_SESSION['old_input'] = $_POST;
						$_SESSION['errors'] = $errors;
						$_SESSION['flash'] =
							[
								'class'=>'danger',
								'message'=>$message,
								'tab'=>$tab
							];
					}

					header('location:?'.$redirect);
					die();
					
				}else{
					$_SESSION['flash'] = ['class'=>'danger','message'=>"Vous n'avez pas accès à la banque de cette compagnie"];
					header('location:?');
					die();
				}
				
				if($result){
					$_SESSION['flash'] = ['class'=>'success','message'=>'opération réussie. Vous avez '.$opeVerb.' '.$value.' thunes'];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				header('location:?'.$redirect);
			}
			
		}else{
			header("location:?action=show&id=$id");
			die();
		}
    }
	
	/**
     * delete the bank from database
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