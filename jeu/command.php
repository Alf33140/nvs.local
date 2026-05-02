<?php
session_start();
require_once("../fonctions.php");
require_once("../mvc/controller/commandController.php");
require_once("../mvc/model/Character.php");

$mysqli = db_connexion();

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	$perso = new Character();
	$perso = $perso->select('id_perso, clan, etat_major')->find($id_perso);
	
	if($perso->etat_major==1){
		
		$commandController = new commandController();
		
		$action = (empty($_GET['action'])) ? '' : $_GET['action'];
		switch ($action) {
			case "vote":
				if(!isset($_POST['compVoteId']) AND !isset($_GET['id']) AND $_POST['compVoteId']!=$_GET['id']){
					$commandController->index($perso);
				}else{
					$commandController->vote($perso);
				}
				break;
			case "comp_validation":
				if(!isset($_POST['compId']) AND !isset($_GET['id']) AND $_POST['compId']!=$_GET['id']){
					$commandController->index($perso);
				}else{
					$commandController->compValidation();
				}
				break;
			case "delete_demand":
				if(!isset($_POST['compId']) AND !isset($_GET['id']) AND $_POST['compId']!=$_GET['id']){
					$commandController->index($perso);
				}else{
					$commandController->deleteDemand($_POST['compId']);
				}
				break;
			case "delete_comp":
				if(!isset($_POST['compId']) AND !isset($_GET['id']) AND $_POST['compId']!=$_GET['id']){
					$commandController->index($perso);
				}else{
					$commandController->deleteComp($_POST['compId']);
				}
				break;
			default:
				$commandController->index($perso);
		}

	}
	else {
		// moderniser le renvoi en cas de problème
			
		header("Location:jouer.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous connecter. <a href='index.php'>Accueil</a></font>";
}
?>