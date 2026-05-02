<?php
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Vehicle.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class vehicleController extends Controller
{
    /**
     * Display the vehicle index.
     *
     * @return view
     */
    public function index()
    {	
    }
	
	/**
     * Display the vehicle creation page.
     *
     * @return view
     */
    public function create()
    {
		
    }
	
	/**
     * Display the specified vehicle.
     *
     * @param  $id
     * @return view
     */
    public function show($id)
    {
		
    }
	
	/**
     * Store the vehicle in the database
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
     * Display the vehicle edit page.
     *
     * @param $id
     * @return view
     */
    public function edit(int $id)
    {	
    }
	
	/**
     * Store the updated vehicle in the database
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
     * delete the vehicle from database
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
	
	/**
     * function to activate and move the vehicle.
     *
     * @return view
     */
    public function move()
    {	
		// récupérer la position du véhicule 
		// connaître la destination et le prochain rail et savoir si la case n'est pas bloquée ou occupée
		// Si bloquage, (bâtiment, autre véhicule...) immobiliser le train / Si occupée par un PNJ ou Joueur on roule dessus
		
		
    }
}