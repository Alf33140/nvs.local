<?php
session_start();
require_once("../fonctions.php");
require_once("f_recrutement.php");
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Building.php");
require_once("../mvc/model/Camp.php");
require_once("../mvc/model/Unit.php");
require_once("../mvc/model/MailFile.php");
require_once("../mvc/model/Grade.php");
require_once("../mvc/model/Weapon.php");
require_once("../mvc/model/Skill.php");
require_once("../mvc/model/Event.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

function get_pnj_perso_id($mysqli) {
	$tab_id_existant = array();

	// récupération de la liste des id de perso libres du 3 au 99
	$sql = "SELECT id_perso FROM perso WHERE id_perso < 100 AND id_perso > 2";
	$res = $mysqli->query($sql);

	// Création tableau des id existant pour les perso PNJ
	while ($t = $res->fetch_assoc()) {
		array_push($tab_id_existant, $t['id_perso']);
	}

	for ($i = 3; $i < 100; $i++) {
		if (!in_array($i, $tab_id_existant)) {
			return (int)$i;
		}
	}

	return 0;
}

if($dispo == '1' || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			$sessionCharacter = new Character();
			$camp = new Camp();
			
			$sessionCharacter = $sessionCharacter->select('perso.idJoueur_perso, perso.nom_perso, perso.est_pnj, perso.pc_perso, perso.pa_perso, perso.chef, perso.clan, perso.bataillon, grades.point_armee_grade, grades.id_grade')
												->leftJoin('perso_as_grade','perso.id_perso','=','perso_as_grade.id_perso')
												->leftJoin('grades','grades.id_grade','=','perso_as_grade.id_grade')
												->where('perso.id_perso',$_SESSION["id_perso"])
												->get();
			$sessionCharacter = $sessionCharacter[0];
			
			$id_joueur	= $sessionCharacter->idJoueur_perso;
			$nom_perso	= $sessionCharacter->nom_perso;
			$est_pnj	= $sessionCharacter->est_pnj;
			$pc 		= $sessionCharacter->pc_perso;
			$pa_perso	= $sessionCharacter->pa_perso;
			$chef 		= $sessionCharacter->chef;
			$clan		= $sessionCharacter->clan;
			$bataillon	= $sessionCharacter->bataillon;
			$pg			= $sessionCharacter->point_armee_grade;
			$id_grade	= $sessionCharacter->id_grade;
			
			$camp = $camp->find($clan);
			
			// Seul le chef peut recruter des grouillots
			if ($chef) {
			
				// Récupération du batiment dans lequel se trouve le perso 
				$sql = "SELECT id_instanceBat FROM perso_in_batiment WHERE id_perso='$id'";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();
				$nb = $res->num_rows;
				
				$id_instance_bat = null;
				if ($nb)
					$id_instance_bat = $tab["id_instanceBat"];
			
	?>
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	
	<body>
		<div class="container-fluid mt-5">	
			<h1 class='text-center mb-4'>Recrutement des grouillots</h1>
			<nav class='nav justify-content-center mb-4'>
				<a class='btn btn-outline-secondary' href='index.php?action=character'>Retour aux persos</a>
				<a class='btn btn-outline-secondary ml-4' href='/'>Retour au jeu</a>
			</nav>
			<?php
					if ($id_instance_bat == NULL) {
						
						echo "<center><font color='red'>Vous ne pouvez recruter des grouillots que depuis un Fort ou un Fortin</font></center>";
						
					} else {
						
						// Récupération des informations sur la batiment dans lequel on se trouve 
						$sql = "SELECT pv_instance, pvMax_instance, id_batiment, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat = '$id_instance_bat'";
						$res = $mysqli->query($sql);
						$tab = $res->fetch_assoc();
						
						$id_batiment_instance 	= $tab["id_batiment"];
						$pv_instance			= $tab["pv_instance"];
						$pv_max_instance		= $tab["pvMax_instance"];
						$x_instance				= $tab["x_instance"];
						$y_instance				= $tab["y_instance"];
						
						// Fort ou Fortin
						if ($id_batiment_instance != 8 && $id_batiment_instance != 9) {
							
							echo "<center><font color='red'>Vous ne pouvez recruter des grouillots que depuis un Fort ou un Fortin</font></center>";
							
						} else {
							
							// Calcul pourcentage pv du batiment 
							$pourc_pv_instance = ($pv_instance / $pv_max_instance) * 100;
							
							// Verification si 10 persos ennemis à moins de 15 cases
							$sql = "SELECT count(id_perso) as nb_ennemi FROM perso, carte 
									WHERE perso.id_perso = carte.idPerso_carte 
									AND x_carte <= $x_instance + 15
									AND x_carte >= $x_instance - 15
									AND y_carte <= $y_instance + 15
									AND y_carte >= $y_instance - 15
									AND perso.clan != '$clan'";
							$res = $mysqli->query($sql);
							$t_e = $res->fetch_assoc();
							
							$nb_ennemis_siege = $t_e['nb_ennemi'];
							
							if ($pourc_pv_instance < 90 || $nb_ennemis_siege >= 10) {
								
								// Il reste moins de 90% des pv du batiment => siege
								echo "<center><font color='red'>Ce batiment est considéré en état de siege, il ne sera pas possible de recruter des grouillots tant que ses PV ne seront pas suffisamment remontés ou que la zone ne sera pas nettoyée des ennemis</font></center><br />";
								echo "<center>PV actuel : $pv_instance / $pv_max_instance</center>";
								
							}
							else{
								$pg_utilise = intval(calcul_pg($mysqli, $id_joueur));

								// Calcul PG restant au joueur ------

								$pg_restant = $pg - $pg_utilise;
                                //-------------------------------------------------------
								//INSERTION DE L UNITE CHARIOT  :if (isset($_POST["9"])){
                                //-------------------------------------------------------
								if($_SERVER['REQUEST_METHOD']==='POST'){

									if ($pa_perso >= 3) {
										$unitType= new Unit();
										$newCharacter = new Character();
									
										if (isset($_POST["2"])){
											// cavalier lourd
											$id_unite = 2;
											$weapons = ['Sabre','Pistolet'];
											$skills = ['resting'];
										}
										if (isset($_POST["3"])){
											// infanterie
											$id_unite = 3;
											$weapons = ['Baïonnette','Fusil'];
											$skills = ['resting','hard_walk','barricade_build'];
										}
										if (isset($_POST["4"])){
											// soigneur
											$id_unite = 4;
											$weapons = ['Seringue','Bandages'];
											$skills = ['resting','hard_walk'];
											
										}
										if (isset($_POST["5"])){
											// canon
											$id_unite = 5;
											$weapons = ['Canon'];
											$skills = ['resting'];
										}
										if (isset($_POST["6"])){
											// toutou
											$id_unite = 6;
											$weapons = ['Canines','Griffes'];
											$skills = ['resting'];
										}
										if (isset($_POST["7"])){
											// cavalier léger
											$id_unite = 7;
											$weapons = ['Sabre léger','Pistolet'];
											$skills = ['resting'];
										}
										if (isset($_POST["8"])){
											//gatling
											$id_unite = 8;
											$weapons = ['Gatling'];
											$skills = ['resting'];
										}
                                        if (isset($_POST["9"])){
											// chariot
											$id_unite = 9;
											$weapons = ['Baïonnette','Fusil'];         // armes du chariot
											$skills = ['resting','hard_walk','barricade_build'];   //sieste de recup, marche forcee des cheveaux et faire des barricades pour se proteger
										}
										// Récupérer coût PG unite
										$unitType = $unitType->find($id_unite);
										$nom_unite = $unitType->nom_unite;
										
										$cout_pg_recrutement = $unitType->cout_pg;
										
										if ($pg_restant >= $cout_pg_recrutement) {
											
											// calcul DLA
											$nowDate = new DateTimeImmutable('NOW');
											$interval = new dateInterval('PT1H');//Tour de 1h
											$DLA = $nowDate->add($interval);
											
											// nom du perso
											$nom_perso_cree	= $nom_perso."_junior";
											
											$nom_perso_tmp = $nom_perso_cree;
											$nom_pas_trouve = true;
											$i = 2;
											
											while ($nom_pas_trouve) {
												
												$nom_perso_cherche = addslashes($nom_perso_cree);
												
												$sql = "SELECT id_perso FROM perso WHERE nom_perso='$nom_perso_cherche'";
												$res = $mysqli->query($sql);
												$nb = $res->num_rows;
												
												if ($nb == 0) {
													$nom_pas_trouve = false;
												}
												else {
													$nom_perso_cree = $nom_perso_tmp.$i;
													
													$i++;
												}
											}
											
											$newCharacter->nom_perso = $nom_perso_cree;
											
											if($est_pnj==1){
												$newCharacter->est_pnj==1;
											}
											// Préparation des composants de l'image
											$suffixe  = !empty($camp->img_suffix) ? $camp->img_suffix : "";
											$extension = !empty($unitType->img_extension) ? $unitType->img_extension : ".gif";
											
											// instanciation caracs de base du perso
											$newCharacter->idJoueur_perso = $id_joueur;
											$newCharacter->bataillon = $bataillon;
											$newCharacter->type_perso = $unitType->id_unite;
											$newCharacter->x_perso = $x_instance;
											$newCharacter->y_perso = $y_instance;
											$newCharacter->id_grade = 1;
											$newCharacter->pvMax_perso = $unitType->pv_unite; 
											$newCharacter->pv_perso = $unitType->pv_unite; 
											$newCharacter->pmMax_perso = $unitType->pm_unite;
											$newCharacter->pm_perso = 0;
											$newCharacter->perception_perso = $unitType->perception_unite;
											$newCharacter->recup_perso = $unitType->recup_unite;
											$newCharacter->protec_perso = $unitType->protection_unite;
											$newCharacter->pa_perso = 0;
											$newCharacter->paMax_perso = $unitType->pa_unite;
											$newCharacter->image_perso = $unitType->image_unite . "_" . $suffixe . $extension;
											$newCharacter->or_perso = 0;
											$newCharacter->chef = 0;
											$newCharacter->clan = $camp->id;
											$newCharacter->DLA_perso = $DLA->format('Y-m-d H:i:s');
											$newCharacter->dateCreation_perso = $nowDate->format('Y-m-d H:i:s');
											
											$savedCharacter = $newCharacter->saveWithModel();
											
											// Insertion perso dans batiment
											$enterInBat = new Building();
											$enterInBat = $enterInBat->insertCharacters([$savedCharacter->id_perso],$id_instance_bat);
											
											//on crée les dossiers des messageries des persos
											$createMailfiles = new MailFile();
											$createMainMailfile = $createMailfiles->addFiles([$savedCharacter->id_perso],1);
											$createArchiveMailfile = $createMailfiles->addFiles([$savedCharacter->id_perso],2);
											
											// on ajoute le grade grouillot
											$createGrade = new Grade();
											$createCharacterGrade = $createGrade->addGrade($savedCharacter->id_perso,1);
											
											// on ajoute les armes
											foreach($weapons as $weapon){
												$getWeapon = new Weapon();
												$getWeapon = $getWeapon->select('id_arme, nom_arme')->where('nom_arme',$weapon)->get();
												$getWeapon = $getWeapon[0];
												
												$addWeapon = new Weapon();
												$addLeaderSabre = $addWeapon->addWeapon($savedCharacter->id_perso,$getWeapon->id_arme,1);
											}
											
											// on ajoute les caractéristiques
											foreach($skills as $skill){
												$getSkill = new Skill();
												$getSkill = $getSkill->select('id_competence, nom_competence')->where('slug_competence',$skill)->get();
												$getSkill = $getSkill[0];
												
												$addSkill = new Skill();
												$addLeaderSleeping = $addSkill->addSkill($savedCharacter->id_perso,$getSkill->id_competence);
											}
											
											// on crée l'évènement de création du grouillot
											$event = new Event();
											$eventDetails = "A rejoint le bataillon ".$newCharacter->bataillon;
											$eventDate = $nowDate->format('Y-m-d H:i:s');
											$addCharacterEvent = $event->addEvent($savedCharacter->id_perso, $newCharacter->nom_perso, $eventDetails, $eventDate);
											
											// MAJ des PA du chef 
											$pa_perso = $pa_perso - 3;
											$sql = "UPDATE perso SET pa_perso=pa_perso-3 WHERE id_perso='$id'";
											$mysqli->query($sql);
											
											echo "<center><font color=blue>Vous venez de recruter une $nom_unite</font></center>";
										}
										else{
											echo "<center><font color=red>Vous n'avez pas assez de point de grouillot pour pouvoir recruter cette unité. Il vous reste $pg_restant points de grouillot</font></center>";
										}
									}
									else{
										echo "<center><font color=red>Vous n'avez pas assez de point d'action pour recruter ce grouillot, il vous reste $pa_perso points d'action</font></center>";
									}
								}
								
								?>
								
								<center>Vous avez utilisé <b><?php echo $pg_utilise; ?> PG</b> sur un total de <b><?php echo $pg; ?>PG (PG restant : <?php echo $pg_restant; ?>)</b></center>
								<center>Le recrutement d'un grouillot coute <b>3PA</b>, il vous reste <b><?php echo $pa_perso; ?> PA</b></center>
								<center>Les grouillots que vous avez renvoyé par le passé peuvent être réactivés dans la page "Gérer ses grouillots".</center><br>
								
								<?php

								// Récupération des grouillots recrutable
								$sql = "SELECT * FROM type_unite WHERE id_unite != '1'";
								$res = $mysqli->query($sql);
								
								echo "<form method=\"post\" action=\"recrutement.php\">";
								
								echo "<table align='center' border='1' width='70%'>";
								echo "	<tr>";
								echo "		<th></th><th>Unité</th><th>PA</th><th>PV</th><th>PM</th><th>Recupération</th><th>Perception</th><th>Protection</th><th>Description</th><th>Cout PG</th><th>Action</th>";
								echo "	</tr>";
								
								while ($tab = $res->fetch_assoc()) {
									
									$id_unite			= $tab["id_unite"];
									$nom_unite 			= $tab["nom_unite"];
									$description_unite 	= $tab["description_unite"];
									$perception_unite 	= $tab["perception_unite"];
									$protection_unite 	= $tab["protection_unite"];
									$recup_unite 		= $tab["recup_unite"];
									$pv_unite 			= $tab["pv_unite"];
									$pa_unite 			= $tab["pa_unite"];
									$pm_unite 			= $tab["pm_unite"];
									$image_unite 		= $tab["image_unite"];
									$cout_pg_unite 		= $tab["cout_pg"];
									
									$image_affiche = $image_unite."_".$camp->img_suffix.".gif";
									
									echo "	<tr>";
									echo "		<td align='center'><img src='../images_perso/".$image_affiche."' alt='".$nom_unite."'/></td>";
									echo "		<td align='center'>$nom_unite</td>";
									echo "		<td align='center'>$pa_unite</td>";
									echo "		<td align='center'>$pv_unite</td>";
									echo "		<td align='center'>$pm_unite</td>";
									echo "		<td align='center'>$recup_unite</td>";
									echo "		<td align='center'>$perception_unite</td>";
									echo "		<td align='center'>$protection_unite</td>";
									echo "		<td align='center'>$description_unite</td>";
									echo "		<td align='center'>$cout_pg_unite PG</td>";
									
									// Conditions si Possibilité de recruter
									if ($pa_perso >= 3 && $cout_pg_unite <= $pg_restant) {
										echo "		<td align='center'><input type='submit' name=\"".$id_unite."\" class='btn btn-success' value=\">> Recruter !\"></td>";
									}
									else if ($pa_perso< 3) {
										echo "<td align='center'>PA insufisants</td>";
									}
									else if ($cout_pg_unite > $pg_restant) {
										echo "<td align='center'>PG insufisants</td>";
									}
									else {
										echo "<td align='center'>Non recrutable</td>";
									}
									echo "	</tr>";
									
								}
								
								echo "</table>";
								
								echo "</form>";
							}
						}
					}
				}
				else {
					echo "<font color=red>Seul le chef de bataillon peut accéder à cette page.</font>";
				}
			}
		}
		else{
			echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
		}
		?>
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		
	</body>
</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>