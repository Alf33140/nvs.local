<?php
require_once("../mvc/model/Character.php");
require_once("../mvc/model/User.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class SearchController extends Controller
{	
	/**
     * search data auto completion from database (AJAX request)
     *
     * @return array
     */
    public function search()
    {
		header('Content-Type: application/json');
		
		if($_SERVER['REQUEST_METHOD']==='POST'){
			$input = json_decode(file_get_contents("php://input"), true);
			$query = trim($input['query'] ?? '');
			
			if($query){
				$character = new Character();
				$list = $character->searchCharacters($query,$_SESSION['ID_joueur']);
				
				if($list){
					echo json_encode(["status" => "success", "data" => $list]);
					return;
				}
				
				echo json_encode(["status" => "error", "message" => "no data found"]);
				return;
			}else{
				echo json_encode(["status" => "error", "message" => "Données non trouvées"]);
				return;
			}
		}else{
			echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
		}
    }
}