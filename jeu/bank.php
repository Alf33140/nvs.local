<?php
session_start();
require_once("../fonctions.php");
require_once("../mvc/controller/bankController.php");
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Company.php");

$mysqli = db_connexion();

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);

if(isset($_SESSION["id_perso"])){

	$admin = admin_perso($mysqli, $_SESSION["id_perso"]);
	
	if($dispo==1 || $admin==1){
		
		$bankController = new bankController();
		
		$action = (empty($_GET['action'])) ? '' : $_GET['action'];
		switch ($action) {
			case "create":
				$bankController->create();
				break;
			case "store":
				$bankController->store();
				break;
			case "edit":
				if(!isset($_GET['id']) OR empty($_GET['id'])){
					$bankController->index();
				}else{
					$bankController->edit($_GET['id']);
				}
				break;
			case "update":
				if(!isset($_POST['id']) AND !isset($_GET['id'])){
					$bankController->index();
				}else{
					$bankController->update($_GET['id']);
				}
				break;
			case "show":
				if(!isset($_GET['id']) OR empty($_GET['id'])){
					$bankController->index();
				}else{
					$bankController->show($_GET['id']);
				}
				break;
			case "treasury":
				if(!isset($_GET['id']) OR empty($_GET['id'])){
					$bankController->index();
				}else{
					$bankController->treasury($_GET['id']);
				}
				break;
			case "ope":
				if(!isset($_POST['id_bank']) AND !isset($_GET['id_bank'])){
					$bankController->index();
				}else{
					$bankController->operation($_GET['id']);
				}
				break;
			case "delete":
				if(!isset($_POST['id'])){
					$bankController->index();
				}else{
					$bankController->destroy($_POST['id']);
				}
				break;
			case "AccountDetails":
					header('Content-Type: application/json');
					
					if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
						if(!isset($_GET['id'])){
							http_response_code(404);
						}else{
							// $details = json_encode('coucou');
							$details = $bankController->accountDetails($_GET['id']);
							echo $details;
							break;
						}
					}else{
						http_response_code(404);
						echo json_encode(['error' => 'Route non trouvée']);
					}
				break;
			default:
				$bankController->index();
		}

	}
	else {
		// logout
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location:../index.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous connecter. <a href='index.php'>Accueil</a></font>";
}
?>