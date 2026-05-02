<?php
require_once("../mvc/model/Character.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class NotificationController extends Controller
{
    /**
     * Display the mail index.
     *
     * @return view
     */
    public function index()
    {		
		header('location:');
    }
	
	/**
     * Display the mail creation page.
     *
     * @return view
     */
    public function create()
    {
		
    }
	
	/**
     * Display the specified mail.
     *
     * @param  $id
     * @return view
     */
    public function show(int $id)
    {
		
    }
	
	/**
     * Store the mail in the database
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
					$_SESSION['flash'] = ['class'=>'success','message'=>''];
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
     * Display the mail edit page.
     *
     * @param $id
     * @return view
     */
    public function edit(int $id)
    {	
    }
	
	/**
     * Store the updated mail in the database
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
					$_SESSION['flash'] = ['class'=>'success','message'=>''];
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
    }
	
	/**
     * delete the mail from database
     *
	 * @param $id
     * @return redirect
     */
    public function destroy($id)
    {	
		
		header('location:?');
		die();
    }
}