<?php
session_start();
require_once("../fonctions.php");
require_once("../mvc/controller/companyController.php");
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Company.php");

$mysqli = db_connexion();

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);

if(isset($_SESSION["id_perso"])){

	$admin = admin_perso($mysqli, $_SESSION["id_perso"]);
	
	if($dispo==1 || $admin==1){
		
		$companyController = new companyController();
		
		$action = (empty($_GET['action'])) ? '' : $_GET['action'];
		switch ($action) {
			case "create":
				$companyController->create();
				break;
			case "store":
				$companyController->store();
				break;
			case "edit":
				if(!isset($_GET['id']) OR empty($_POST['id'])){
					$companyController->index();
				}else{
					$companyController->edit($_GET['id']);
				}
				break;
			case "update":
				if(!isset($_POST['id']) AND !isset($_GET['id'])){
					$companyController->index();
				}else{
					$companyController->update($_GET['id']);
				}
				break;
			case "show":
				if(!isset($_GET['id']) OR empty($_GET['id'])){
					$companyController->index();
				}else{
					$companyController->show($_GET['id']);
				}
				break;
			case "delete":
				if(!isset($_POST['id']) OR empty($_POST['id'])){
					$companyController->index();
				}else{
					$companyController->destroy($_POST['id']);
				}
				break;
			case "join":
				if(!isset($_POST['compId']) OR empty($_POST['compId']) OR !isset($_GET['id']) OR $_GET['id']!=$_POST['compId']){
					$companyController->index();
				}else{
					$companyController->joinComp($_POST['compId']);
				}
				break;
			case "quit":
				if(!isset($_POST['compId']) OR !isset($_GET['id']) OR $_GET['id']!=$_POST['compId'] OR !isset($_POST['memberID'])){
					$companyController->index();
				}else{
					$companyController->quitComp($_POST['compId'], $_POST['memberID']);
				}
				break;
			default:
				$companyController->index();
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