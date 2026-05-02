<?php
session_start();
require_once('../mvc/controller/homeController.php');
require_once('../mvc/controller/userController.php');
require_once('../mvc/controller/characterController.php');
require_once('../mvc/controller/searchController.php');
require_once('../mvc/controller/authController.php');
require_once('../mvc/controller/gameBoardController.php');
require_once("../mvc/model/Administration.php");
require_once("../mvc/model/User.php");

date_default_timezone_set('Europe/Paris');

// déclaration des contrôleurs :
$homeController = new homeController();
$userController = new userController();
$characterController = new characterController();
$searchController = new searchController();
$authController = new authController();
$gameBoardController = new gameBoardController();

//Routeur
try{
	if(!empty($_SESSION) AND isset($_SESSION["ID_joueur"])){
		$administration = new Administration();
		$game_available = $administration->getMaintenanceMode();
		
		$user = new User();
		$user = $user->select('id_joueur, admin_perso, demande_perm, permission, pendu')
				->where('id_joueur',$_SESSION['ID_joueur'])
				->get();
		$user = $user[0];
		
		if($game_available['valeur_config']==1 || $user->admin_perso==1){
			$action = (empty($_GET['action'])) ? '' : $_GET['action'];
			$operation = (empty($_GET['op'])) ? '' : $_GET['op'];
			switch ($action) {
				case "faq":
					$homeController->faq();
					break;
				case "ranking":
					$homeController->ranking();
					break;
				/* fonctions de recherche en ajax */
				case "search":
					$searchController->search();
					break;
				/* gestion utilisateur */
				case "user":
					if(isset($_GET['id']) AND !empty($_GET['id'])){
						switch ($operation) {
							case "show":
								$userController->show($_GET['id']);
								break;
							case "edit":
								if($_SERVER['REQUEST_METHOD']==='POST'){
									$userController->update($_GET['id']);
								}else{
									$userController->edit($_GET['id']);
								}
								break;
							case "delete":
								$userController->destroy($_GET['id']);
								break;
							default:
								$userController->show($_GET['id']);
						}
						break;
					}else{
						if($user->admin_perso==1){
							$userController->index();
							break;
						}else{
							$gameBoardController->index();
						}
					}
					break;
				case "character":
					if(isset($_GET['id']) AND !empty($_GET['id'])){
						switch ($operation) {
							case "show":
								$characterController->show($_GET['id']);
								break;
							case "edit":
								if($_SERVER['REQUEST_METHOD']==='POST'){
									$characterController->update($_GET['id']);
								}else{
									$characterController->edit($_GET['id']);
								}
								break;
							case "delete":
								$characterController->destroy($_GET['id']);
								break;
							default:
								$characterController->show($_GET['id']);
						}
						break;
					}else{
						$characterController->index();
					}
					break;
				/* gestion du mot de passe */
				case "password":
					switch ($operation) {
						case "new":
							$authController->changePassword();
							break;
						case "forgot":
							$authController->forgotPassword();
							break;
						case "reset":
							if($_GET['token']){
								$authController->resetPassword($token);
							}else{
								$authController->forgotPassword();
							}
							break;
						default:
							$userController->show($_GET['id']);
					}
					break;
				case "logout":
					$authController->logout();
					break;
				/* défaut : renvoi à la page de jeu */
				default:
					$gameBoardController->index();
			}
		}else{
			$_SESSION['flash'] = ['class'=>'info','message'=>'Le jeu est en maintenance. Vous pourrez vous connecter ultérieurement'];
			header('location:/');
			die();
		}
	}else{
		$action = (empty($_GET['action'])) ? '' : $_GET['action'];
			switch ($action) {
				case "presentation":
					$homeController->presentation();
					break;
				case "faq":
					$homeController->faq();
					break;
				case "ranking":
					$homeController->ranking();
					break;
				case "forum":
					$homeController->forum();
					break;
				case "credits":
					$homeController->credits();
					break;
				case "nouveau_tour":
					$_SESSION['flash'] = ["slug"=>"new_turn","class" => "info","message"=>"Nouveau tour"];
					$homeController->index();
					break;
				default:
					$homeController->index();
			}
	}
}catch(Exception $e){
	switch($e->getCode()){
		case 401:
			http_response_code(401);
			return require_once('../mvc/view/errors/401.php');
			break;
		case 403:
			http_response_code(403);
			return require_once('../mvc/view/errors/403.php');
			break;
		case 404:
			http_response_code(404);
			return require_once('../mvc/view/errors/404.php');
			break;
		case 405:
			http_response_code(405);
			return require_once('../mvc/view/errors/405.php');
			break;
		default:
			echo $e->getMessage();
	}
}
?>
