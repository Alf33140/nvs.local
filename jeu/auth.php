<?php
session_start();
require_once('../mvc/controller/authController.php');

if(!empty($_SESSION) AND isset($_SESSION["id_perso"])){
	header('location:jeu/jouer.php');
}else{
	$authController = new authController();
	$action = (empty($_GET['action'])) ? '' : $_GET['action'];
		switch ($action) {
			case "register":
				if(isset($_POST) AND !empty($_POST)){
					$authController->store();
				}else{
					$authController->register();
				}
				break;
			case "login":
				$authController->login();
				break;
			case "logout":
				$authController->logout();
				break;
			default:
				$authController->index();
		}
}
?>
