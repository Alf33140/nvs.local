<?php
@session_start();
require_once("config.php");// pour la gestion mecanique des ovelrlay et jour/ nuit + meteo
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once("f_popover.php");
require_once("../mvc/model/Building.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = $user->admin_perso;

if($dispo == '1' || $admin){

	if(isset($_SESSION["id_perso"])){

		$id_perso = $_SESSION['id_perso'];
		$date = time();

		$page_acces = 'index.php';
		if ($_SERVER['QUERY_STRING'] != '') {
			$page_acces .= '?'.$_SERVER['QUERY_STRING'];
		}

		$id_joueur_perso = $_SESSION['ID_joueur'];

		$sql_dla = "SELECT UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele FROM perso WHERE idJoueur_perso='$id_joueur_perso' AND chef=1";
		$res_dla = $mysqli->query($sql_dla);
		$t_dla = $res_dla->fetch_assoc();

		$dla 		= $t_dla["DLA"];
		$est_gele 	= $t_dla["est_gele"];

		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();

		$testpv = $tpv['pv_perso'];

		$config = '1';

		// verification si le perso est encore en vie
		if ($testpv <= 0) {
			// le perso est mort
			header("Location:../tour.php");
		}
		else {
			// le perso est vivant
			// verification si nouveau tour ou gele
			if(nouveau_tour($date, $dla) || $est_gele) {
				if (isset($_GET['login']) && $_GET['login'] == 'ok') {
					header("Location:../tour.php?login=ok");
				}
				else {
					header("Location:../tour.php");
				}
			}
			else {
				$erreur = "";
				$mess = "";
				$mess_bat ="";

				if(isset($_SESSION["nv_tour"]) && $_SESSION["nv_tour"] == 1){
					echo "<center><font color=red><b>Nouveau tour</b></font></center>";
					$_SESSION["nv_tour"] = 0;
				}

				// recuperation des anciennes données du perso

				$id_joueur_perso 	= $selected_Character->idJoueur_perso;
				$nom_perso 			= $selected_Character->nom_perso;
				$x_persoN 			= $selected_Character->x_perso;
				$y_persoN 			= $selected_Character->y_perso;
				$pm_perso 			= $selected_Character->pm_perso;
				$pmMax_perso		= $selected_Character->pmMax_perso;
				$dla_perso			= $selected_Character->DLA_perso;
				$image_perso 		= $selected_Character->image_perso;
				$bonusPM_perso_p 	= $selected_Character->bonusPM_perso;
				$clan_p 			= $selected_Character->clan;
				$type_perso			= $selected_Character->type_perso;
				$combat_type		= $selected_Character->type_combat;
				$pa_perso			= $selected_Character->pa_perso;
				$perception_perso	= $selected_Character->perception_perso;
				$charge_perso		= $selected_Character->charge_perso;
				$chargeMax_perso	= $selected_Character->chargeMax_perso;
				$grade_perso 		= $selected_Character->id_grade;
				$nom_grade_perso	= $selected_Character->nom_grade;

				$sql = "SELECT UNIX_TIMESTAMP(DLA_perso) as DLA_perso FROM perso WHERE idJoueur_perso='$id_joueur_perso' AND chef=1";
				$res = $mysqli->query($sql);
				$t_c = $res->fetch_assoc();

				$n_dla 				= $t_c["DLA_perso"];

				// récupération de la couleur du camp
				$couleur_clan_p = couleur_clan($clan_p);

				$dossier_img_joueur = get_dossier_image_joueur($mysqli, $id_joueur_perso);

				// affichage rosace et bousculades
				$sql = "SELECT afficher_rosace, bousculade_deplacement FROM joueur WHERE id_joueur='$id_joueur_perso'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();

				$afficher_rosace 	= $t['afficher_rosace'];
				$bousculade_dep		= $t['bousculade_deplacement'];
				$cadrillage			= 1;//$t['cadrillage'];

				$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();

				$X_MAX = $t['x_max'];
				$Y_MAX  = $t['y_max'];

				$carte = "carte";

				if(isset($_GET['erreur'])){
					if ($_GET['erreur'] == 'competence') {
						$erreur .= 'compétence indiponible pour le moment';
					}

					if ($_GET['erreur'] == 'prox_bat') {
						$erreur .= 'Vous devez vous trouver à proximité du bâtiment pour effectuer cette action';
					}

					if ($_GET['erreur'] == 'pa') {
						$erreur .= "Vous n'avez pas assez de PA";
					}

					if ($_GET['erreur'] == 'pm') {
						$erreur .= "Vous n'avez plus de PM !";
					}
				}

				if (isset($_GET['message'])) {
					$message = $_GET['message'];
					if ($message == 'gainPM') {
						$mess .= "Vous êtes en forme aujourd'hui, vous gagnez 1PM !";
					}
				}

				// calcul malus pm
				$malus_pm_charge = getMalusCharge($charge_perso, $chargeMax_perso);
				if ($malus_pm_charge == 100) {
					$malus_pm = -$pmMax_perso;
				}
				else {
					$malus_pm = $malus_pm_charge;
				}

				// traitement entrée dans un batiment
				if(isset($_GET["bat"])) {

					$id_inst = $_GET["bat"];

					// on veut sortir du batiment
					if(isset($_GET["out"]) && $_GET["out"] == "ok") {

						// verification que le perso est bien dans le batiment duquel il souhaite sortir...
						if($id_inst == in_bat($mysqli, $id_perso)){

							// verification des pm du perso
							if($pm_perso + $malus_pm >= 1){

								// Si on choisi de sortir avec une direction
								if (isset($_GET["direction"])) {

									if (isDirectionOK($_GET["direction"])) {

										$direction = $_GET["direction"];

										$sql_b = "SELECT batiment.id_batiment, nom_batiment, taille_batiment, nom_instance FROM batiment, instance_batiment
												WHERE instance_batiment.id_batiment = batiment.id_batiment
												AND instance_batiment.id_instanceBat = '$id_inst'";
										$res_b = $mysqli->query($sql_b);
										$t_b = $res_b->fetch_assoc();

										$type_bat			= $t_b['id_batiment'];
										$nom_bat 			= $t_b['nom_batiment'];
										$taille_bat			= $t_b['taille_batiment'];
										$nom_instance_bat	= $t_b['nom_instance'];

										if ($type_bat != 10) {

											$taille_case = ceil($taille_bat / 2);

											$oc = 1;

											switch($direction){
												case 1:
													// Haut gauche
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN + $taille_case";

													break;
												case 2:
													// Haut
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN AND y_carte = $y_persoN + $taille_case";

													break;
												case 3:
													// Haut droite
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN + $taille_case";

													break;
												case 4:
													// Gauche
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN";

													break;
												case 5:
													// Droite
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN";

													break;
												case 6:
													// Bas gauche
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN - $taille_case";

													break;
												case 7:
													// Bas
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN AND y_carte = $y_persoN - $taille_case";

													break;
												case 8:
													// Bas droite
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN - $taille_case";

													break;
											}

											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();

											$oc 	= $t["occupee_carte"];

											if ($oc) {
												switch($direction){
													case 1:
														// Haut gauche
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																		WHERE x_carte = $x_persoN - $i AND y_carte = $y_persoN + $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN + $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 2:
														// Haut
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $i AND y_carte = $y_persoN + $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $i AND y_carte = $y_persoN + $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 3:
														// Haut droite
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $i AND y_carte = $y_persoN + $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN + $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 4:
														// Gauche
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN + $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN - $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 5:
														// Droite
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN + $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN - $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 6:
														// Bas gauche
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $i AND y_carte = $y_persoN - $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN - $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 7:
														// Bas
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $i AND y_carte = $y_persoN - $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $i AND y_carte = $y_persoN - $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 8:
														// Bas droite
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $i AND y_carte = $y_persoN - $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN - $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
												}
											}

											if (!$oc) {

												$xs 	= $t["x_carte"];
												$ys 	= $t["y_carte"];
												$fond 	= $t["fond_carte"];

												$cout_pm = cout_pm($fond, $type_perso);

												// verification des pm du perso
												if($pm_perso + $malus_pm >= $cout_pm){

													// mise a jour des coordonnees du perso et de ses pm
													$sql = "UPDATE perso SET x_perso = '$xs', y_perso = '$ys', pm_perso=pm_perso-$cout_pm WHERE id_perso = '$id_perso'";
													$mysqli->query($sql);

													$x_persoN = $xs;
													$y_persoN = $ys;

													// mise a jour des coordonnees du perso sur la carte et changement d'etat de la case
													$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso' ,idPerso_carte='$id_perso' WHERE x_carte = '$xs' AND y_carte = '$ys'";
													$mysqli->query($sql);

													// mise a jour de la table perso_in_batiment
													$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso'";
													$mysqli->query($sql);

													// mise a jour des evenements
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est sorti du batiment',NULL,'','en $xs/$ys',NOW(),'0')";
													$mysqli->query($sql);

													// mise a jour du bonus de perception
													$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);

													if(bourre($mysqli, $id_perso)){
														if(!endurance_alcool($mysqli, $id_perso)) {
															$malus_bourre = bourre($mysqli, $id_perso) * 3;
															$bonus_visu -= $malus_bourre;
														}
													}

													$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
													$mysqli->query($sql);

													// maj carte brouillard de guerre
													$perception_final = $perception_perso + $bonus_visu;
													//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);
												}
												else {
													$erreur .= "Il faut posséder au moins ".$cout_pm." PM pour sortir de ce bâtiment dans cette direction";
												}
											}
											else {
												$erreur .= "Impossible de sortir dans cette direction, la sortie est bloquée";
											}
										}
										else {
											$erreur .= "Impossible de sortir d'un pénitencier";
										}
									}
									else {
										$erreur .= "Direction de sortie du bâtiment incorrecte";
									}
								}
								else {
									$erreur .= "Une direction est nécessaire pour sortir du bâtiment";
								}
							}
							else {
								$erreur .= "Il faut posséder au moins 1 PM pour sortir du bâtiment";
							}
						}
						else {
							$erreur .= "Vous n'êtes pas dans ce bâtiment. Vous ne pouvez pas en sortir";
						}
					}
					else {
						// on veut rentrer dans le batiment

						// traitement du cas tour de visu et de la tour de garde où il ne peut y avoir qu'un seul perso dedans !
						if(isset($_GET["bat2"]) && ($_GET["bat2"] == 2 || $_GET["bat2"] == 3) && isset($_GET["bat"]) && $_GET["bat"]!="") {

							// Vérification que le perso soit pas déjà dans un bâtiment
							if(!in_bat($mysqli, $id_perso) && !in_train($mysqli, $id_perso)){

								// verification que l'instance du batiment existe
								if (existe_instance_bat($mysqli, $_GET["bat"])){

									if(verif_bat_instance($mysqli, $_GET["bat2"],$_GET["bat"])){

										// verification qu'on soit bien à côté du batiment
										if(prox_instance_bat($mysqli, $x_persoN, $y_persoN, $_GET["bat"])){

											// verification si il y a un perso dans la tour
											$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat=".$_GET["bat"]."";
											$res = $mysqli->query($sql);
											$nbp = $res->fetch_row();

											if($nbp[0] != 0){
												// si la tour est occupee
												$erreur .= "Vous ne pouvez pas entrer, la tour est déjà occupée";
											}
											else { // la tour est vide

												// verification que le perso a encore des pm
												if($pm_perso + $malus_pm >= 1){

													if ($type_perso == '6' || $type_perso == '4' || $type_perso == '3') {

														$entre_bat_ok = 1;

														// recuperation des coordonnees et infos du batiment dans lequel le perso entre
														$sql = "SELECT nom_instance, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, id_batiment, camp_instance FROM instance_batiment WHERE id_instanceBat=".$_GET["bat"]."";
														$res = $mysqli->query($sql);
														$coordonnees_instance = $res->fetch_assoc();

														$x_bat 				= $coordonnees_instance["x_instance"];
														$y_bat 				= $coordonnees_instance["y_instance"];
														$nom_bat 			= $coordonnees_instance["nom_instance"];
														$nom_instance 		= $coordonnees_instance["nom_instance"];
														$id_bat				= $coordonnees_instance["id_batiment"];
														$camp_bat			= $coordonnees_instance["camp_instance"];
														$pv_batiment		= $coordonnees_instance["pv_instance"];
														$pvMax_batiment		= $coordonnees_instance["pvMax_instance"];
														$id_inst_bat 		= $_GET["bat"];

														// Verification si le perso est de la même nation ou non que le batiment
														if(!nation_perso_bat($mysqli, $id_perso, $id_inst_bat)) {

															$pourc_pv_instance = $pvMax_batiment == 0 ? 0 : ($pv_batiment / $pvMax_batiment) * 100;

															if ($pourc_pv_instance <= 80) {

																// Les chiens et soigneurs ne peuvent pas capturer de batiment
																if ($type_perso != '6' && $type_perso != '4') {

																	// Les hopitaux ne peuvent être capturés
																	if ($id_bat != '7') {

																		// Capture du batiment, il devient de la nation du perso
																		$sql = "UPDATE instance_batiment, perso SET camp_instance=clan WHERE id_instanceBat='$id_inst_bat' AND id_perso='$id_perso'";
																		$mysqli->query($sql);

																		$sql = "select clan from perso where id_perso='$id_perso'";
																		$res = $mysqli->query($sql);
																		$t_c = $res->fetch_assoc();

																		$camp = $t_c["clan"];

																		// MAJ camp canons
																		$sql = "UPDATE instance_batiment_canon SET camp_canon='$camp' WHERE id_instance_bat='$id_inst_bat'";
																		$mysqli->query($sql);

																		if($camp == "1"){
																			$couleur_c 		= "b";
																		}
																		else if($camp == "2"){
																			$couleur_c 		= "r";
																		}
																		else if ($camp == "3") {
																			$couleur_c 		= "g";
																		}

																		// Mise à jour de l'icone centrale sur la carte
																		$icone = "b".$id_bat."$couleur_c.png";
																		$sql = "UPDATE $carte SET image_carte='$icone' WHERE x_carte=$x_bat and y_carte=$y_bat";
																		$mysqli->query($sql);

																		// mise a jour table evenement
																		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a capturé','$id_inst_bat','le batiment $nom_bat','en $x_bat/$y_bat : Felicitation!',NOW(),'0')";
																		$mysqli->query($sql);

																		if ($camp_bat == '1') {
																			$couleur_clan_bat = 'blue';
																		}
																		else if ($camp_bat == '2') {
																			$couleur_clan_bat = 'red';
																		}
																		else if ($camp_bat == '2') {
																			$couleur_clan_bat = 'green';
																		}
																		else {
																			$couleur_clan_bat = 'black';
																		}

																		// maj CV
																		$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>', '$nom_grade_perso', '$id_inst_bat','<font color=$couleur_clan_bat>Tour de Guêt $nom_bat</font>', NULL, NOW(), 8)";
																		$mysqli->query($sql);

																		echo "<font color = red>Felicitation, vous venez de capturer un bâtiment ennemi !</font><br>";
																	}
																	else {
																		// Tentative de triche
																		$text_triche = "Tentative capture Hopital";

																		$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
																		$mysqli->query($sql);

																		$erreur .= "Les hôpitaux ne peuvent pas être capturés !";
																	}
																}
																else {
																	$entre_bat_ok = 0;

																	// Tentative de triche
																	$text_triche = "Tentative capture batiment avec type perso non autorisé";

																	$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
																	$mysqli->query($sql);

																	$erreur .= "Les chiens et les soigneurs ne peuvent pas capturer de bâtiments !";
																}
															}
															else {
																$entre_bat_ok = 0;

																$erreur .= "Le bâtiment n'est pas encore capturable, il faut descendre ses PV";
															}
														}

														if ($entre_bat_ok) {

															// mise a jour de la carte
															$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
															$res = $mysqli->query($sql);

															// mise a jour des coordonnées du perso
															$sql = "UPDATE perso SET x_perso='$x_bat', y_perso='$y_bat', pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
															$res = $mysqli->query($sql);

															// insertion du perso dans la table perso_in_batiment
															$enterInBat = new Building();
															$enterInBat = $enterInBat->insertCharacters([$id_perso],$id_inst_bat);

															$mess = "vous êtes entré(e) dans le bâtiment $id_inst_bat";

															// mise a jour table evenement
															$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est entré dans le batiment $nom_bat $id_inst_bat',NULL,'','en $x_bat/$y_bat',NOW(),'0')";
															$mysqli->query($sql);

															// calcul du bonus de perception
															if($_GET["bat2"] == 2){
																$bonus_perc = 5;
															}

															// mise a jour du bonus de perception du perso
															$bonus_visu = $bonus_perc + getBonusObjet($mysqli, $id_perso);

															if(bourre($mysqli, $id_perso)){
																if(!endurance_alcool($mysqli, $id_perso)) {
																	$malus_bourre = bourre($mysqli, $id_perso) * 3;
																	$bonus_visu -= $malus_bourre;
																}
															}
															// maj bonus perception et -1 pm pour rentrer dans le batiment
															$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
															$mysqli->query($sql);

															// mise a jour des coordonnees du perso pour les tests d'après
															$x_persoN = $x_bat;
															$y_persoN = $y_bat;
														}
													}
													else {
														// Tentative de triche
														$text_triche = "Tentative entrer batiment tour de guet avec type perso non autorisé";

														$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
														$mysqli->query($sql);

														$erreur .= "Seuls les infanteries, soigneurs et chiens peuvent monter dans la tour de guet";
													}
												}
												else {
													$erreur .= "Il faut posséder au moins 1 PM pour entrer dans le bâtiment";
												}
											}
										}
										else {
											$erreur .= "Il faut être à côté du bâtiment pour y entrer";
										}
									}
									else {
										$erreur .= "Pas bien d'essayer de tricher...";
									}
								}
								else {
									$erreur .= "Le bâtiment n'existe pas";
								}
							}
							else {
								$erreur .= "Vous êtes déjà dans un bâtiment";
							}
						}
						// traitement des autres cas
						else {
							if(isset($_GET["bat"]) && $_GET["bat"]!="" && isset($_GET["bat2"]) && $_GET["bat2"]!="" && $_GET["bat2"] != 1 && $_GET["bat2"] != 5 && $_GET["bat2"] != 10) {

								// Vérification que le perso soit pas déjà dans un bâtiment
								if(!in_bat($mysqli, $id_perso) && !in_train($mysqli, $id_perso)){

									// verification que l'instance du batiment existe
									if (existe_instance_bat($mysqli, $_GET["bat"])){

										if(verif_bat_instance($mysqli, $_GET["bat2"], $_GET["bat"])){

											// verification qu'on soit bien à côté du batiment
											if(prox_instance_bat($mysqli, $x_persoN, $y_persoN, $_GET["bat"])){

												// verification que le perso a encore des pm
												if($pm_perso + $malus_pm >= 1){

													//recuperation du nombre de persos dans le batiment
													$sql = "select id_perso from perso_in_batiment where id_instanceBat=".$_GET["bat"]."";
													$res = $mysqli->query($sql);
													$nb_perso_bat = $res->num_rows;

													// recuperation des coordonnees et des infos du batiment dans lequel le perso entre
													$sql = "SELECT nom_batiment, id_instanceBat, pv_instance, pvMax_instance, nom_instance, x_instance, y_instance, contenance_instance, instance_batiment.id_batiment, taille_batiment, camp_instance
															FROM instance_batiment, batiment
															WHERE instance_batiment.id_batiment = batiment.id_batiment
															AND id_instanceBat=".$_GET["bat"]."";
													$res = $mysqli->query($sql);
													$coordonnees_instance = $res->fetch_assoc();

													$x_bat 					= $coordonnees_instance["x_instance"];
													$y_bat 					= $coordonnees_instance["y_instance"];
													$nom_bat 				= $coordonnees_instance["nom_instance"];
													$nom_batiment			= $coordonnees_instance["nom_batiment"];
													$id_inst_bat 			= $coordonnees_instance["id_instanceBat"];
													$contenance_inst_bat 	= $coordonnees_instance["contenance_instance"];
													$camp_instance_bat		= $coordonnees_instance["camp_instance"];
													$id_bat					= $coordonnees_instance["id_batiment"];
													$taille_batiment		= $coordonnees_instance["taille_batiment"];
													$pv_batiment			= $coordonnees_instance["pv_instance"];
													$pvMax_batiment			= $coordonnees_instance["pvMax_instance"];

													// verification contenance batiment
													if($nb_perso_bat < $contenance_inst_bat){

														$entre_bat_ok = 1;

														// verification si le perso est de la même nation que le batiment
														if(!nation_perso_bat($mysqli, $id_perso, $id_inst_bat)) {

															$pourc_pv_instance = $pvMax_batiment == 0 ? 0 : ($pv_batiment / $pvMax_batiment) * 100;

															if ($pourc_pv_instance <= 80) {

																// les chiens et soigneurs ne peuvent pas capturer de batiment
																if ($type_perso != '6' && $type_perso != '4') {

																	// Les hopitaux et les gares ne peuvent être capturés
																	if ($id_bat != '7' && $id_bat != '11') {

																		// verification que le batiment est vide
																		if(batiment_vide($mysqli, $id_inst_bat)) {

																			// capture du batiment, il devient de la nation du perso
																			$sql = "UPDATE instance_batiment, perso SET camp_instance=clan WHERE id_instanceBat='$id_inst_bat' AND id_perso='$id_perso'";
																			$mysqli->query($sql);

																			$sql = "select clan from perso where id_perso='$id_perso'";
																			$res = $mysqli->query($sql);
																			$t_c = $res->fetch_assoc();

																			$camp = $t_c["clan"];

																			// MAJ camp canons
																			$sql = "UPDATE instance_batiment_canon SET camp_canon='$camp' WHERE id_instance_bat='$id_inst_bat'";
																			$mysqli->query($sql);

																			if($camp == "1"){
																				$couleur_c 		= "b";
																				$image_canon_g 	= 'canonG_nord.gif';
																				$image_canon_d 	= 'canonD_nord.gif';
																			}
																			else if($camp == "2"){
																				$couleur_c 		= "r";
																				$image_canon_g 	= 'canonG_sud.gif';
																				$image_canon_d 	= 'canonD_sud.gif';
																			}

																			$icone = "b".$id_bat."$couleur_c.png";

																			if ($taille_batiment > 1) {

																				$taille_search 	= floor($taille_batiment / 2);
																				$image_case_c	= $couleur_c.".png";

																				for ($x = $x_bat - $taille_search; $x <= $x_bat + $taille_search; $x++) {
																					for ($y = $y_bat - $taille_search; $y <= $y_bat + $taille_search; $y++) {
																						if ($x == $x_bat && $y == $y_bat) {
																							// Mise à jour de l'icone centrale
																							$sql = "UPDATE $carte SET image_carte='$icone' WHERE x_carte=$x_bat and y_carte=$y_bat";
																							$mysqli->query($sql);
																						}
																						else {
																							$sql = "UPDATE $carte SET image_carte='$image_case_c' WHERE x_carte='$x' AND y_carte='$y' AND image_carte NOT LIKE 'canon%'";
																							$mysqli->query($sql);
																						}
																					}
																				}

																				// Mise à jour des icones de canon sur la carte
																				if ($id_bat == 8) {
																					// Fortin
																					// Canons Gauche
																					$sql = "UPDATE $carte SET image_carte='$image_canon_g'
																							WHERE (x_carte=$x_bat - 1 AND y_carte=$y_bat - 1)
																							OR (x_carte=$x_bat - 1 AND y_carte=$y_bat + 1)";
																					$mysqli->query($sql);

																					// Canons Droit
																					$sql = "UPDATE $carte SET image_carte='$image_canon_d'
																							WHERE (x_carte=$x_bat + 1 AND y_carte=$y_bat - 1)
																							OR (x_carte=$x_bat + 1 AND y_carte=$y_bat + 1)";
																					$mysqli->query($sql);
																				}
																				else if ($id_bat == 9) {
																					// Fort
																					// Canons Gauche
																					$sql = "UPDATE $carte SET image_carte='$image_canon_g'
																							WHERE (x_carte=$x_bat - 2 AND y_carte=$y_bat + 2)
																							OR (x_carte=$x_bat - 2 AND y_carte=$y_bat)
																							OR (x_carte=$x_bat - 2 AND y_carte=$y_bat - 2)";
																					$mysqli->query($sql);

																					// Canons Droit
																					$sql = "UPDATE $carte SET image_carte='$image_canon_d'
																							WHERE (x_carte=$x_bat + 2 AND y_carte=$y_bat + 2)
																							OR (x_carte=$x_bat + 2 AND y_carte=$y_bat)
																							OR (x_carte=$x_bat + 2 AND y_carte=$y_bat - 2)";
																					$mysqli->query($sql);
																				}

																				// Mise à jour des respawn
																				$sql = "DELETE FROM perso_as_respawn WHERE id_instance_bat='$id_inst_bat'";
																				$mysqli->query($sql);
																			}
																			else {
																				// Mise à jour de l'icone centrale
																				$sql = "UPDATE $carte SET image_carte='$icone' WHERE x_carte=$x_bat and y_carte=$y_bat";
																				$mysqli->query($sql);
																			}

																			// mise a jour table evenement
																			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a capturé le batiment','$id_inst_bat','$nom_bat','en $x_bat/$y_bat : Felicitation!',NOW(),'0')";
																			$mysqli->query($sql);

																			// Gain points de victoire
																			if ($id_bat == 9) {
																				// FORT -> 500
																				$gain_pvict = 500;
																				$nom_b = "FORT";
																			}
																			else if ($id_bat == 8) {
																				// FORTIN -> 100
																				$gain_pvict = 100;
																				$nom_b = "FORTIN";
																			}
																			else if ($id_bat == 11) {
																				// GARE -> 50
																				$gain_pvict = 50;
																				$nom_b = "GARE";
																			}
																			else if ($id_bat == 7) {
																				// HOPITAL -> 0
																				$gain_pvict = 0;
																				$nom_b = "HOPITAL";
																			}
                                                                             else if ($id_bat == 15) {
																				// MINE D OR -> 20
																				$gain_pvict = 20;
																				$nom_b = "MINE";
																			}
                                                                            else if ($id_bat == 16) {
																				// SCIERIE -> 20
																				$gain_pvict = 20;
																				$nom_b = "SCIERIE";
																			}
                                                                            else if ($id_bat == 17) {
																				// MINE DE FER -> 20
																				$gain_pvict = 20;
																				$nom_b = "MINE";
																			}
																			else {
																				$gain_pvict = 0;
																			}

																			if ($gain_pvict > 0) {

																				// C'est une capture, gains X 1.5
																				$gain_pvict = floor($gain_pvict * 1.5);

																				// MAJ stats points victoire
																				$sql = "UPDATE stats_camp_pv SET points_victoire = points_victoire + ".$gain_pvict." WHERE id_camp='$clan_p'";
																				$mysqli->query($sql);

																				// Ajout de l'historique
																				$date = time();
																				$texte = addslashes("Pour la capture du bâtiment ".$nom_batiment." ".$nom_bat." [".$id_inst_bat."] par ".$nom_perso." [".$id_perso."]");
																				$sql = "INSERT INTO histo_stats_camp_pv (date_pvict, id_camp, gain_pvict, texte) VALUES (FROM_UNIXTIME($date), '$clan_p', '$gain_pvict', '$texte')";
																				$mysqli->query($sql);

																			}

																			if ($camp_instance_bat == '1') {
																				$couleur_clan_bat = 'blue';
																			}
																			else if ($camp_instance_bat == '2') {
																				$couleur_clan_bat = 'red';
																			}
																			else if ($camp_instance_bat == '2') {
																				$couleur_clan_bat = 'green';
																			}
																			else {
																				$couleur_clan_bat = 'black';
																			}

																			// maj CV
																			$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>', '$nom_grade_perso', '$id_inst_bat','<font color=$couleur_clan_bat>$nom_b $nom_bat</font>', NULL, NOW(), 8)";
																			$mysqli->query($sql);

																			echo "<font color = red>Félicitation, vous venez de capturer un bâtiment ennemi !</font><br>";
																		}
																		else {
																			$entre_bat_ok = 0;

																			$erreur .= "Le bâtiment n'est pas vide et ne peut donc pas être capturé";
																		}
																	}
																	else {
																		$entre_bat_ok = 0;

																		// Tentative de triche
																		$text_triche = "Tentative capture Hopital ou Gare";

																		$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
																		$mysqli->query($sql);

																		$erreur .= "Les hôpitaux et les gares ne peuvent pas être capturés !";
																	}
																}
																else {
																	$entre_bat_ok = 0;

																	// Tentative de triche
																	$text_triche = "Tentative capture batiment avec type perso non autorisé";

																	$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
																	$mysqli->query($sql);

																	$erreur .= "Les chiens et les soigneurs ne peuvent pas capturer de bâtiment";
																}
															}
															else {
																$entre_bat_ok = 0;

																$erreur .= "Le bâtiment n'est pas encore capturable, il faut réduire ses PV";
															}
														}

														if ($entre_bat_ok) {

															// mise a jour des coordonnées du perso sur la carte
															$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
															$res = $mysqli->query($sql);

															// mise a jour des coordonnées du perso
															$sql = "UPDATE perso SET x_perso='$x_bat', y_perso='$y_bat' WHERE id_perso='$id_perso'";
															$res = $mysqli->query($sql);

															// insertion du perso dans la table perso_in_batiment
															$enterInBat = new Building();
															$enterInBat = $enterInBat->insertCharacters([$id_perso],$id_inst_bat);
															// $sql = "INSERT INTO `perso_in_batiment` VALUES ('$id_perso','$id_inst_bat')";
															// $mysqli->query($sql);

															$mess = "vous êtes entré(e) dans le bâtiment $nom_bat";

															// mise a jour table evenement
															$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est entré dans le batiment $nom_bat $id_inst_bat',NULL,'','en $x_bat/$y_bat',NOW(),'0')";
															$mysqli->query($sql);

															// Partie Passage de grade chef
															if ($type_perso == 1 && ($id_bat == 8 || $id_bat == 9)) {

																// recup grade / pc chef
																$sql = "SELECT pc_perso, perso_as_grade.id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND perso.id_perso='$id_perso'";
																$res = $mysqli->query($sql);
																$t_chef = $res->fetch_assoc();

																$pc_perso_chef = $t_chef["pc_perso"];
																$id_grade_chef = $t_chef["id_grade"];

																// Verification passage de grade
																$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
																$res = $mysqli->query($sql);
																$t_grade = $res->fetch_assoc();

																$id_grade_final 	= $t_grade["id_grade"];
																$nom_grade_final	= $t_grade["nom_grade"];

																if ($id_grade_chef < $id_grade_final) {

																	// Passage de grade
																	$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso'";
																	$mysqli->query($sql);

																	// mise a jour des evenements
																	$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été promu <b>$nom_grade_final</b> !',NULL,'','',NOW(),'0')";
																	$mysqli->query($sql);

																	// maj CV
																	$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>', '$nom_grade_final', NULL, NULL, NULL, NOW(), 9)";
																	$mysqli->query($sql);
																}
															}

															$bonus_perc = 0;

															// mise a jour du bonus de perception du perso
															$bonus_visu = $bonus_perc + getBonusObjet($mysqli, $id_perso);

															if(bourre($mysqli, $id_perso)){
																if(!endurance_alcool($mysqli, $id_perso)) {
																	$malus_bourre = bourre($mysqli, $id_perso) * 3;
																	$bonus_visu -= $malus_bourre;
																}
															}

															// maj bonus perception et -1 pm pour l'entrée dans le batiment
															$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu, pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
															$mysqli->query($sql);

															// mise a jour des coordonnees du perso pour le test d'après
															$x_persoN = $x_bat;
															$y_persoN = $y_bat;
														}
													}
													else {
														$erreur .= "Le bâtiment est déjà rempli au maximum de sa capacité";
													}
												}
												else {
													$erreur .= "Il faut posséder au moins 1 PM pour entrer dans le bâtiment";
												}
											}
											else {
												// Tentative de triche
												$text_triche = "Tentative pour entrer dans un bâtiment sans être à côté de celui-ci";

												$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
												$mysqli->query($sql);

												$erreur .= "Il faut être à côté du bâtiment pour y entrer";
											}
										}
										else {
											$erreur .= "Pas bien d'essayer de tricher...";
										}
									}
									else {
										// Tentative de triche
										$text_triche = "Tentative entrer dans un natiment qui n existe pas...";

										$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
										$mysqli->query($sql);

										$erreur .= "Le bâtiment n'existe pas";
									}
								}
								else {
									$erreur .= "Vous êtes déjà dans un bâtiment";
								}
							}
						}
					}
				}

				// Traitement sortie
				if (isset($_GET['sortie'])) {

					// verification que le perso a encore des pm
					if($pm_perso + $malus_pm >= 1){

						$instance_bat = in_bat($mysqli, $id_perso);

						if($instance_bat){

							$coord_sortie = $_GET['sortie'];

							$t_coord = explode(',',$coord_sortie);

							if (count($t_coord) == 2) {

								$x_sortie = $t_coord[0];
								$y_sortie = $t_coord[1];

								$verif_x = preg_match("#^[0-9]*[0-9]$#i","$x_sortie");
								$verif_y = preg_match("#^[0-9]*[0-9]$#i","$y_sortie");

								if ($verif_x && $verif_y) {

									if (in_map($x_sortie, $y_sortie, $X_MAX, $Y_MAX)) {

										// Récupération x, y et taille batiment
										$sql = "SELECT x_instance, y_instance, taille_batiment, batiment.id_batiment FROM instance_batiment, batiment
												WHERE instance_batiment.id_batiment = batiment.id_batiment
												AND id_instanceBat = '$instance_bat'";
										$res = $mysqli->query($sql);
										$t = $res->fetch_assoc();

										$id_bat		= $t['id_batiment'];
										$x_instance = $t['x_instance'];
										$y_instance = $t['y_instance'];
										$taille_bat = $t['taille_batiment'];

										// Cas particulier pénitencier
										if ($id_bat != 10) {

											$nb_case_bat = ceil($taille_bat / 2);

											if (($x_sortie == $x_instance + $nb_case_bat && $y_sortie >= $y_instance - $nb_case_bat && $y_sortie <= $y_instance + $nb_case_bat)
												|| ($x_sortie == $x_instance - $nb_case_bat && $y_sortie >= $y_instance - $nb_case_bat && $y_sortie <= $y_instance + $nb_case_bat)
												|| ($y_sortie == $y_instance + $nb_case_bat && $x_sortie >= $x_instance - $nb_case_bat && $x_sortie <= $x_instance + $nb_case_bat)
												|| ($y_sortie == $y_instance - $nb_case_bat && $x_sortie >= $x_instance - $nb_case_bat && $x_sortie <= $x_instance + $nb_case_bat)) {


												// recuperation des fonds
												$sql = "SELECT fond_carte, occupee_carte FROM $carte WHERE x_carte='$x_sortie' AND y_carte='$y_sortie'";
												$res_map = $mysqli->query ($sql);
												$t_carte1 = $res_map->fetch_assoc();

												$fond = $t_carte1["fond_carte"];
												$oc_c = $t_carte1["occupee_carte"];

												// On vérifie que la case n'est pas déjà occupée
												if (!$oc_c) {

													$cout_pm = cout_pm($fond, $type_perso);

													if ($pm_perso + $malus_pm >= $cout_pm) {

														// mise a jour des coordonnees du perso et de ses pm
														$sql = "UPDATE perso SET x_perso = '$x_sortie', y_perso = '$y_sortie', pm_perso=pm_perso-$cout_pm WHERE id_perso = '$id_perso'";
														$mysqli->query($sql);

														$x_persoN = $x_sortie;
														$y_persoN = $y_sortie;

														// mise a jour des coordonnees du perso sur la carte et changement d'etat de la case
														$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso' ,idPerso_carte='$id_perso' WHERE x_carte = '$x_sortie' AND y_carte = '$y_sortie'";
														$mysqli->query($sql);

														// mise a jour de la table perso_in_batiment
														$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso'";
														$mysqli->query($sql);

														// mise a jour des evenements
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est sorti du batiment',NULL,'','en $x_sortie/$y_sortie',NOW(),'0')";
														$mysqli->query($sql);

														// mise a jour du bonus de perception
														$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);

														if(bourre($mysqli, $id_perso)){
															if(!endurance_alcool($mysqli, $id_perso)) {
																$malus_bourre = bourre($mysqli, $id_perso) * 3;
																$bonus_visu -= $malus_bourre;
															}
														}

														$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
														$mysqli->query($sql);

														// maj carte brouillard de guerre
														$perception_final = $perception_perso + $bonus_visu;
														//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);

													}
													else {
														$erreur .= "Vous n'avez pas assez de PM pour sortir du bâtiment sur cette case !";
													}
												}
												else {
													$erreur .= "La case de sortie est déjà occupée !";
												}
											}
											else {
												// Tentative de triche
												$text_triche = "Les coordonnées de sortie en paramètre ne correspondent pas à la sortie du batiment";

												$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
												$mysqli->query($sql);

												$erreur .= "Paramètre incorrect !";
											}
										}
										else {
											// Tentative de triche
											$text_triche = "Tentative de sortie de pénitencier";

											$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
											$mysqli->query($sql);

											$erreur .= "Votre tentative d'évasion s'est soldée par un échec, les gardes vous ont rattrapés et remis au cachot !";
										}
									}
									else {
										$erreur .= "Les coordonnées sont en dehors de la carte !";
									}
								}
								else {
									// Tentative de triche
									$text_triche = "Tentative modification parametre sortie, paramètre x ou y incorrect";

									$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
									$mysqli->query($sql);

									$erreur .= "Paramètre incorrect !";
								}
							}
							else {
								// Tentative de triche
								$text_triche = "Tentative modification parametre sortie, nombre paramètres incorrect";

								$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
								$mysqli->query($sql);

								$erreur .= "Paramètre incorrect !";
							}
						}
						else {
							// Tentative de triche
							$text_triche = "Tentative utilisation sortie alors que non dans batiment";

							$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
							$mysqli->query($sql);

							$erreur .= "Vous ne pouvez pas utiliser cette fonction si vous n'êtes pas dans un bâtiment !";
						}
					}
					else {
						$erreur .= "Vous n'avez pas assez de PM pour sortir du bâtiment !";
					}
				}

				// On se trouve dans un batiment
				if(in_bat($mysqli, $id_perso)){

					// Récupération des infos sur l'instance du batiment dans lequel le perso se trouve
					$sql = "SELECT id_instanceBat, id_batiment, nom_instance, pv_instance, pvMax_instance FROM instance_batiment WHERE x_instance='$x_persoN' AND y_instance='$y_persoN'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();

					$id_bat 	= $t["id_instanceBat"];
					$bat 		= $t["id_batiment"];
					$nom_ibat 	= $t["nom_instance"];
					$pv_bat		= $t['pv_instance'];
					$pvMax_bat	= $t['pvMax_instance'];

					//recuperation du nom du batiment
					$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
					$res_n = $mysqli->query($sql_n);
					$t_n = $res_n->fetch_assoc();

					$nom_bat = $t_n["nom_batiment"];

					// Les chiens ne peuvent pas réparer les bâtiments
					if ($pv_bat < $pvMax_bat && $type_perso != '6') {
						$mess_bat .= "<a href=\"action.php?bat=$id_bat&reparer=ok\" >~~ réparer $nom_bat $nom_ibat [$id_bat] (5 PA) ~~</a><br>";
					}

					$mess_bat .= "<a href=\"batiment.php?bat=$id_bat\" target='_blank'>~~ accéder à la page du bâtiment $nom_bat $nom_ibat ~~</a><br>";

					$bonus_perc = 0;

					// calcul du bonus/malus de perception
					if($bat == 2){
						// Tour de guet
						$bonus_perc += 5;
					}
					else if ($bat == 8 || $bat == 9 || $bat == 11) {
						// Fort / Fortin / Gare
						$bonus_perc += -1;
					}
					else if ($bat == 7 || $bat == 10) {
						// Hopital / Pénitencier
						$bonus_perc += -2;
					}

					// mise a jour du bonus de perception du perso
					$bonus_visu = $bonus_perc + getBonusObjet($mysqli, $id_perso);

				} else if (in_train($mysqli, $id_perso)) {
					$bonus_perc = -1;
					$bonus_visu = $bonus_perc + getBonusObjet($mysqli, $id_perso);
				} else {

					$sql = "SELECT fond_carte FROM $carte WHERE x_carte=$x_persoN AND y_carte=$y_persoN";
					$res_map = $mysqli->query($sql);
					$t_carte1 = $res_map->fetch_assoc();

					$fond 			= $t_carte1["fond_carte"];

					$malus_fond = get_malus_visu($fond);

					// Les chiens ne perdent pas de perception en foret
					if ($malus_fond < 0 && $type_perso == 6) {
						$malus_fond = 0;
					}

					$bonus_visu = $malus_fond + getBonusObjet($mysqli, $id_perso);
				}

				if(bourre($mysqli, $id_perso)){
					if(!endurance_alcool($mysqli, $id_perso)) {
						$malus_bourre = bourre($mysqli, $id_perso) * 3;
						$bonus_visu -= $malus_bourre;
					}
				}

				$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
				$mysqli->query($sql);

				// On se trouve dans un train
				if (in_train($mysqli, $id_perso)) {
					$mess_bat .= "Vous êtes dans un train";

					if (isset($_GET['train']) && isset($_GET['direction'])) {

						// on veut sortir du batiment
						if(isset($_GET["out"]) && $_GET["out"] == "ok") {

							$id_instance_train 	= $_GET['train'];
							$direction_saut		= $_GET['direction'];

							if (isDirectionOK($direction_saut)) {

								switch($direction_saut){
									case 1:
										// Haut gauche
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN - 2 AND y_carte >= $y_persoN + 2";
										break;
									case 2:
										// Haut
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN AND y_carte = $y_persoN + 2";
										break;
									case 3:
										// Haut droite
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN + 2 AND y_carte = $y_persoN + 2";
										break;
									case 4:
										// Gauche
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN - 2 AND y_carte = $y_persoN";
										break;
									case 5:
										// Droite
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN + 2 AND y_carte = $y_persoN";
										break;
									case 6:
										// Bas gauche
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN - 2 AND y_carte = $y_persoN - 2";
										break;
									case 7:
										// Bas
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN AND y_carte = $y_persoN - 2";
										break;
									case 8:
										// Bas droite
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN + 2 AND y_carte = $y_persoN - 2";
										break;
								}

								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();

								$oc 	= $t["occupee_carte"];
								$xs 	= $t["x_carte"];
								$ys 	= $t["y_carte"];
								$fond_c = $t["fond_carte"];

								if (!$oc && in_map($xs, $ys, $X_MAX, $Y_MAX) && !is_eau_p($fond_c)) {
									// On peut sauter

									// mise a jour du bonus de perception
									$bonus_visu = get_malus_visu($fond_c) + getBonusObjet($mysqli, $id_perso);

									if(bourre($mysqli, $id_perso)){
										if(!endurance_alcool($mysqli, $id_perso)) {
											$malus_bourre = bourre($mysqli, $id_perso) * 3;
											$bonus_visu -= $malus_bourre;
										}
									}

									// On supprime le perso du train
									$sql = "DELETE FROM perso_in_train WHERE id_train='$id_instance_train' AND id_perso='$id_perso'";
									$mysqli->query($sql);

									// MAJ perso
									$sql = "UPDATE perso SET x_perso='$xs', y_perso='$ys', bonusPerception_perso=$bonus_visu, pv_perso=pv_perso/2 WHERE id_perso='$id_perso'";
									$mysqli->query($sql);

									// mise a jour des coordonnees du perso sur la carte et changement d'etat de la case
									$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso' ,idPerso_carte='$id_perso' WHERE x_carte = '$xs' AND y_carte = '$ys'";
									$mysqli->query($sql);

									// mise a jour des evenements
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est sauté du train ',NULL,'','[<a href=\"evenement.php?infoid=$id_instance_train\">$id_instance_train</a>] en $xs/$ys | PV/2',NOW(),'0')";
									$mysqli->query($sql);

									// maj carte brouillard de guerre
									$perception_final = $perception_perso + $bonus_visu;
									//maj_visu($mysqli, $clan_p, $carte, $xs, $ys, $perception_final, $id_perso);

								}
								else {
									// On ne peux pas sauter
									$erreur .= "Impossible de sauter du train dans cette direction";
								}
							}
							else {
								// TRICHE
							}
						}
					}

				}

				// Traitement ramasser objets à terre
				if(isset($_GET['ramasser']) && $_GET['ramasser'] == "ok"){

					if ($pa_perso >= 1) {

						// MAJ pa perso
						$sql = "UPDATE perso SET pa_perso=pa_perso-1 WHERE id_perso='$id_perso'";
						$mysqli->query($sql);

						$liste_ramasse = "";

						// récupération de la liste des objets à terre
						$sql = "SELECT type_objet, id_objet, nb_objet FROM objet_in_carte WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
						$res = $mysqli->query($sql);

						while ($t = $res->fetch_assoc()) {

							$type_objet = $t['type_objet'];
							$id_objet	= $t['id_objet'];
							$nb_objet	= $t['nb_objet'];

							// Si perso n'est pas un chef on empeche de ramasser un étendard
							if($type_perso != 1 && $type_objet == 2 && ($id_objet == 8 || $id_objet == 9)){
								$erreur .= "Vous n'avez pas le droit de ramasser un étendard.";
							} else {
								// Suppression de l'objet par terre
								$sql_d = "DELETE FROM objet_in_carte WHERE type_objet='$type_objet' AND id_objet='$id_objet' AND x_carte='$x_persoN' AND y_carte='$y_persoN'";
								$mysqli->query($sql_d);

								// Récupération poid objet
								// Thunes
								if ($type_objet == 1) {
									$poid_objet = 0;

									// Ajout de la thune au perso
									$sql_t = "UPDATE perso SET or_perso=or_perso+$nb_objet WHERE id_perso='$id_perso'";
									$mysqli->query($sql_t);

									$liste_ramasse .= $nb_objet . " Thune";
									if ($nb_objet > 1) {
										$liste_ramasse .= "s";
									}
								}

								// Objet
								if ($type_objet == 2) {
									$sql_obj = "SELECT nom_objet, poids_objet FROM objet WHERE id_objet='$id_objet'";
									$res_obj = $mysqli->query($sql_obj);
									$t_obj = $res_obj->fetch_assoc();

									$nom_objet	= $t_obj['nom_objet'];
									$poid_objet = $t_obj['poids_objet'];

									for ($i = 0; $i < $nb_objet; $i++) {
									// Ajout de l'objet dans l'inventaire du perso
									$sql_o = "INSERT INTO perso_as_objet (id_perso, id_objet) VALUES ('$id_perso', '$id_objet')";
									$mysqli->query($sql_o);
									}

									// calcul charge objets
									$charge_objets_total = $poid_objet * $nb_objet;

									// MAJ charge perso
									$sql_c = "UPDATE perso SET charge_perso = charge_perso + $charge_objets_total WHERE id_perso='$id_perso'";
									$mysqli->query($sql_c);

									$liste_ramasse .= " -- ". $nb_objet . " " . $nom_objet;
								}

								// Arme
								if ($type_objet == 3) {
									$sql_obj = "SELECT nom_arme, poids_arme FROM arme WHERE id_arme='$id_objet'";
									$res_obj = $mysqli->query($sql_obj);
									$t_obj = $res_obj->fetch_assoc();

									$nom_arme	= $t_obj['nom_arme'];
									$poid_objet = $t_obj['poids_arme'];

									for ($i = 0; $i < $nb_objet; $i++) {
										// Ajout de l'arme dans l'inventaire du perso
										$sql_a = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso', '$id_objet', '0')";
										$mysqli->query($sql_a);
									}

									// calcul charge armes
									$charge_objets_total = $poid_objet * $nb_objet;

									// MAJ charge perso
									$sql_c = "UPDATE perso SET charge_perso = charge_perso + $charge_objets_total WHERE id_perso='$id_perso'";
									$mysqli->query($sql_c);

									$liste_ramasse .= " -- ". $nb_objet . " " . $nom_arme;
								}
							}
						}

						// mise a jour des evenements
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a ramassé des objets par terre ',NULL,'','en $x_persoN/$y_persoN : $liste_ramasse',NOW(),'0')";
						$mysqli->query($sql);

						$mess = "Vous avez ramassé les objets suivants : ". $liste_ramasse;
					}
					else {
						$erreur .= "Vous n'avez pas assez de PA pour rammasser les objets à terre.";
					}
				}

				// traitement des deplacements
				if (isset($_GET["mouv"])) {

					$mouv = $_GET["mouv"];

					$x_persoE = $selected_Character->x_perso;
					$y_persoE = $selected_Character->y_perso;
					$pm_perso = $selected_Character->pm_perso;

					if (!in_bat($mysqli, $id_perso) && !in_train($mysqli, $id_perso)) {

						if (reste_pm($pm_perso + $malus_pm)) {

							//on modifie les coordonnées du perso suivant le deplacement qu'il a effectué
							switch($mouv){
								case 1: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE+1; break;
								case 2: $x_persoN=$x_persoE; $y_persoN=$y_persoE+1; break;
								case 3: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE+1; break;
								case 4: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE; break;
								case 5: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE; break;
								case 6: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE-1; break;
								case 7: $x_persoN=$x_persoE; $y_persoN=$y_persoE-1; break;
								case 8: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE-1; break;
							}

							$in_map = in_map($x_persoN, $y_persoN, $X_MAX, $Y_MAX);

							if ($in_map) {

								$sql = "SELECT occupee_carte, fond_carte, image_carte FROM $carte WHERE x_carte=$x_persoN AND y_carte=$y_persoN";
								$res_map = $mysqli->query($sql);
								$t_carte1 = $res_map->fetch_assoc();

								$case_occupee 	= $t_carte1["occupee_carte"];
								$fond 			= $t_carte1["fond_carte"];

								$cout_pm 	= cout_pm($fond, $type_perso);

								if (!is_eau_p($fond)) {

									if (!$case_occupee){

										if($pm_perso  + $malus_pm >= $cout_pm){

											$chance = rand(1,1000);

											if ($chance == 1) {

												// échec critique, le perso trébuche, perd 1PM et reste sur place
												$sql = "UPDATE perso SET pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												$erreur .= "Vous avez trébuché, vous perdez 1 PM !";
												// mise a jour des évènements
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'$nom_perso','a trébuché',NULL,'','en $x_persoN/$y_persoN',NOW(),'0')";
												$mysqli->query($sql);
											}
											else {

												// maj perso : mise à jour des pm et du bonus de perception
												$sql = "UPDATE perso SET pm_perso =$pm_perso-$cout_pm, bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												//mise à jour des coordonnées du perso
												$dep = "UPDATE perso SET x_perso=$x_persoN, y_perso=$y_persoN WHERE id_perso ='$id_perso'";
												$mysqli->query($dep);

												// maj carte perso
												$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoE' AND y_carte='$y_persoE'";
												$mysqli->query($sql);

												$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
												$mysqli->query($sql);

												// maj carte brouillard de guerre
												$perception_final = $perception_perso + $bonus_visu;

												//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);

												// maj evenement
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p><b>$nom_perso</b></font>','s\'est deplacé',NULL,'','en $x_persoN/$y_persoN',NOW(),'0')";
												$mysqli->query($sql);

												if ($chance == 1000) {
													// réussite critique : gain de 1PM
													$sql = "UPDATE perso SET pm_perso=pm_perso+1 WHERE id_perso='$id_perso'";
													$mysqli->query($sql);

													// mise a jour des évènements
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'$nom_perso','est en forme aujourd\'hui !',NULL,'','',NOW(),'0')";
													$mysqli->query($sql);
													header("location:index.php?message=gainPM");
												}
												else {
													header("location:index.php");
												}
											}
										}
										else{

											$erreur .= "Vous n'avez pas assez de PM !";

											// verification si il y a un batiment a proximite du perso
											$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso);
										}
									}
									else {

										// Verification de qui / quoi occupe la case pour voir si on peut le bousculer
										$sql = "SELECT idPerso_carte FROM $carte WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
										$res = $mysqli->query($sql);
										$t = $res->fetch_assoc();

										$idPerso_carte = $t['idPerso_carte'];

										// Batiment
										if ($idPerso_carte < 200000 && $idPerso_carte >= 50000) {
											$erreur .= "Cette case est déjà occupée par un batiment !";
										}
										else if ($idPerso_carte >= 200000) {
											// PNJ
											$erreur .= "Cette case est déjà occupée par un pnj !";
										} else {
											if ($bousculade_dep || isset($_GET['action_popup'])) {

												// Perso
												// Récupération des informations du perso
												$sql = "SELECT clan, pm_perso, pa_perso, type_perso, image_perso, nom_perso FROM perso WHERE id_perso='$idPerso_carte'";
												$res = $mysqli->query($sql);
												$t = $res->fetch_assoc();

												$camp_perso_b 	= $t['clan'];
												$pm_perso_b		= $t['pm_perso'];
												$pa_perso_b		= $t['pa_perso'];
												$type_perso_b	= $t['type_perso'];
												$image_perso_b	= $t['image_perso'];
												$nom_perso_b	= $t['nom_perso'];
												$id_perso_b 	= $idPerso_carte;

												$couleur_clan_p_b = couleur_clan($camp_perso_b);

												// Calcul case cible bousculade
												switch($mouv){
													case 1: $x_persoB=$x_persoE-2; $y_persoB=$y_persoE+2; break;
													case 2: $x_persoB=$x_persoE; $y_persoB=$y_persoE+2; break;
													case 3: $x_persoB=$x_persoE+2; $y_persoB=$y_persoE+2; break;
													case 4: $x_persoB=$x_persoE-2; $y_persoB=$y_persoE; break;
													case 5: $x_persoB=$x_persoE+2; $y_persoB=$y_persoE; break;
													case 6: $x_persoB=$x_persoE-2; $y_persoB=$y_persoE-2; break;
													case 7: $x_persoB=$x_persoE; $y_persoB=$y_persoE-2; break;
													case 8: $x_persoB=$x_persoE+2; $y_persoB=$y_persoE-2; break;
												}

												// Est ce que le perso peut être bousculer par mon perso

												// types perso compatible pour bousculade ?
												if (isTypePersoBousculable($type_perso, $type_perso_b)) {

													// Ai-je suffisamment de PA / PM pour effectuer la bousculade ?
													if($pm_perso  + $malus_pm >= $cout_pm && $pa_perso >= 3){

														// Case cible de la bousculade est-elle hors carte ?
														if (in_map($x_persoB, $y_persoB, $X_MAX, $Y_MAX)) {

															$sql = "SELECT occupee_carte, fond_carte, image_carte FROM $carte WHERE x_carte=$x_persoB AND y_carte=$y_persoB";
															$res_map = $mysqli->query($sql);
															$t_carteB = $res_map->fetch_assoc();

															$case_occupeeB 	= $t_carteB["occupee_carte"];
															$fondB 			= $t_carteB["fond_carte"];

															$cout_pmB 		= cout_pm($fondB, $type_perso_b);
															$bonus_visuB 	= get_malus_visu($fondB) + getBonusObjet($mysqli, $id_perso);

															// Case cible de la bousculade est-elle déjà occupée ?
															if (!$case_occupeeB) {
																// Case cible eau profonde ?
																if (!is_eau_p($fondB)) {

																	// Même camp ou non ?
																	if ($camp_perso_b == $clan_p) {
																		// Même camp
																		// Si allié, mon allié possède t-il encore 1PA ?
																		if ($pa_perso_b >= 1) {

																			// OK => On bouscule !

																			//-------------------------------------
																			// On déplace en premier le bousculé
																			$sql = "UPDATE perso SET pa_perso = $pa_perso_b-1, bonusPerception_perso=$bonus_visuB WHERE id_perso='$id_perso_b'";
																			$mysqli->query($sql);

																			//mise à jour des coordonnées du perso
																			$dep = "UPDATE perso SET x_perso=$x_persoB, y_perso=$y_persoB WHERE id_perso ='$id_perso_b'";
																			$mysqli->query($dep);

																			// maj carte
																			$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
																			$mysqli->query($sql);

																			$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso_b', idPerso_carte='$id_perso_b' WHERE x_carte='$x_persoB' AND y_carte='$y_persoB'";
																			$mysqli->query($sql);

																			//-----------------------
																			// On se déplace ensuite
																			$sql = "UPDATE perso SET pm_perso =$pm_perso-$cout_pm, pa_perso = $pa_perso-3, bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
																			$mysqli->query($sql);

																			//mise à jour des coordonnées du perso
																			$dep = "UPDATE perso SET x_perso=$x_persoN, y_perso=$y_persoN WHERE id_perso ='$id_perso'";
																			$mysqli->query($dep);

																			// maj carte
																			$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoE' AND y_carte='$y_persoE'";
																			$mysqli->query($sql);

																			$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
																			$mysqli->query($sql);

																			// maj carte brouillard de guerre
																			$perception_final = $perception_perso + $bonus_visu;
																			//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);

																			// maj evenement
																			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p><b>$nom_perso</b></font>','a bousculé ',$id_perso_b,'<font color=$couleur_clan_p_b><b>$nom_perso_b</b></font>','en $x_persoB/$y_persoB',NOW(),'0')";
																			$mysqli->query($sql);

																			header("location:index.php");

																		} else {
																			$erreur .= "Votre allié ne possède pas les PA suffisants pour être bousculé (1 PA)!";
																		}
																	} else {
																		// Camps différents

																		// -------------
																		// - ANTI ZERK -
																		// -------------
																		$verif_anti_zerk = gestion_anti_zerk($mysqli, $id_perso);

																		if ($verif_anti_zerk) {

																			$chance_bouculade = mt_rand(0,100);

																			$date_log = time();

																			$sql = "INSERT INTO log (date_log, id_perso, type_action, pourcentage, message_log)
																					VALUES (FROM_UNIXTIME($date_log), '$id_perso', 'Bousculade', '$chance_bouculade', '$id_perso a bousculé $id_perso_b')";
																			$mysqli->query($sql);

																			if ($chance_bouculade <= 66) {

																				// OK => On bouscule !

																				//-------------------------------------
																				// On déplace en premier le bousculé
																				// maj perso : mise à jour des pm et du bonus de perception
																				$sql = "UPDATE perso SET pm_perso = pm_perso-$cout_pmB, bonusPerception_perso=$bonus_visuB WHERE id_perso='$id_perso_b'";
																				$mysqli->query($sql);

																				//mise à jour des coordonnées du perso
																				$dep = "UPDATE perso SET x_perso=$x_persoB, y_perso=$y_persoB WHERE id_perso ='$id_perso_b'";
																				$mysqli->query($dep);

																				// maj carte
																				$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
																				$mysqli->query($sql);

																				$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso_b', idPerso_carte='$id_perso_b' WHERE x_carte='$x_persoB' AND y_carte='$y_persoB'";
																				$mysqli->query($sql);

																				//-----------------------
																				// On se déplace ensuite
																				$sql = "UPDATE perso SET pm_perso =$pm_perso-$cout_pm, pa_perso = $pa_perso-3, bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
																				$mysqli->query($sql);

																				//mise à jour des coordonnées du perso
																				$dep = "UPDATE perso SET x_perso=$x_persoN, y_perso=$y_persoN WHERE id_perso ='$id_perso'";
																				$mysqli->query($dep);

																				// maj carte
																				$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoE' AND y_carte='$y_persoE'";
																				$mysqli->query($sql);

																				$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
																				$mysqli->query($sql);

																				// maj carte brouillard de guerre
																				$perception_final = $perception_perso + $bonus_visu;
																				//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);

																				// maj evenement
																				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p><b>$nom_perso</b></font>','a bousculé ',$id_perso_b,'<font color=$couleur_clan_p_b><b>$nom_perso_b</b></font>','en $x_persoB/$y_persoB',NOW(),'0')";
																				$mysqli->query($sql);

																				//header("location:index.php");
																			}
																			else {
																				// MAJ pa perso
																				$sql = "UPDATE perso SET pa_perso = $pa_perso-3 WHERE id_perso='$id_perso'";
																				$mysqli->query($sql);

																				// maj evenement
																				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p><b>$nom_perso</b></font>','a raté sa bousculade sur ',$id_perso_b,'<font color=$couleur_clan_p_b><b>$nom_perso_b</b></font>','',NOW(),'0')";
																				$mysqli->query($sql);

																				$erreur .= "Vous avez raté votre bousculade et perdez 3 PA";
																			}
																		}
																		else {
																			$erreur .= "Loi anti-zerk non respectée !";
																		}
																	}
																} else {
																	$erreur .= "Impossible de bousculer un perso dans de l'eau profonde !";
																}
															} else {
																$erreur .= "La case cible de la bousculade est déjà occupée !";
															}
														} else {
															$erreur .= "Impossible de bousculer un perso hors carte !";
														}
													}
													else {
														$erreur .= "Vous n'avez pas assez de PA/PM pour bousculer un perso !";
													}
												} else {
													$erreur .= "Impossible de bousculer ce type de perso !";
												}
											}
											else {
												$erreur .= "Cette case des déjà occupée par un autre perso !";
											}
										}

										// verification si il y a un batiment a proximite du perso
										$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso);
									}
								}
								else if (is_eau_p($fond)) {

									$erreur .= "Vous ne pouvez pas vous déplacer en eau profonde !";

									// verification si il y a un batiment a proximite du perso
									$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso);
								}
							}
							else if (!in_map($x_persoN, $y_persoN, $X_MAX, $Y_MAX)){

								$erreur .= "Déplacement impossible. Case hors limites !";

								// verification si il y a un batiment a proximite du perso
								$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso);
							}
						}
						else if(!reste_pm($pm_perso + $malus_pm)){

							header("Location:index.php?erreur=pm");
						}
						else {
							// normalement impossible
							$erreur .= "Veuillez contacter l'administrateur si vous voyez ce message, merci";
						}
					}
					else {
						$erreur .= "Vous ne pouvez pas vous déplacer si vous êtes dans un bâtiment ou un train";
					}
				}
				else {
					if (!in_train($mysqli, $id_perso)) {
						// verification si il y a un batiment a proximite du perso
						$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoN, $y_persoN, $id_perso, $type_perso);
					}
				}

				

				
				// Récupération du nombre de missions actives
				$sql_ma = "SELECT id_mission, nom_mission, texte_mission, recompense_thune, recompense_xp, recompense_pc, nombre_participant, date_debut_mission, date_fin_mission
						FROM missions WHERE date_debut_mission IS NOT NULL AND (date_fin_mission IS NULL OR date_fin_mission >= CURDATE())
						AND camp_mission='$clan_p'";
				$res_ma = $mysqli->query($sql_ma);
				$nb_missions_actives = $res_ma->num_rows;
				
				// récupération du personnage
				$sql_info = "SELECT * FROM perso WHERE ID_perso ='$id_perso'";
				$res_info = $mysqli->query($sql_info);
				$t_perso2 = $res_info->fetch_assoc();

				$x_perso 				= $t_perso2["x_perso"];
				$y_perso 				= $t_perso2["y_perso"];
				$image_perso 			= $t_perso2["image_perso"];
				$perc 					= $t_perso2["perception_perso"] + $t_perso2["bonusPerception_perso"];
				$pa_perso 				= $t_perso2["pa_perso"];
				$paMax_perso 			= $t_perso2["paMax_perso"];
				$pi_perso 				= $t_perso2["pi_perso"];
				$xp_perso 				= $t_perso2["xp_perso"];
				$pc_perso 				= $t_perso2["pc_perso"];
				$pv_perso 				= $t_perso2["pv_perso"];
				$pvMax_perso 			= $t_perso2["pvMax_perso"];
				$pm_perso_tmp			= $t_perso2["pm_perso"];
				$pmMax_perso_tmp 		= $t_perso2["pmMax_perso"];
				$perception_perso 		= $t_perso2["perception_perso"];
				$bonusPerception_perso 	= $t_perso2["bonusPerception_perso"];
				$bonusPA_perso			= $t_perso2["bonusPA_perso"];
				$recup_perso 			= $t_perso2["recup_perso"];
				$bonusRecup_perso		= $t_perso2["bonusRecup_perso"];
				$bonusPM_perso			= $t_perso2["bonusPM_perso"];
				$protec_perso 			= $t_perso2["protec_perso"];
				$bonus_perso 			= $t_perso2["bonus_perso"];
				$type_perso 			= $t_perso2["type_perso"];
				$bataillon_perso 		= $t_perso2["bataillon"];
				$message_perso			= $t_perso2["message_perso"];
				$charge_perso			= $t_perso2["charge_perso"];
				$chargeMax_perso		= $t_perso2["chargeMax_perso"];
				$nb_em					= $t_perso2["etat_major"];

				// Bonus recup batiment
				$bonus_recup_bat 		= get_bonus_recup_bat_perso($mysqli, $id_perso);
				$bonus_recup_terrain 	= get_bonus_recup_terrain_perso($mysqli, $x_perso, $y_perso);

				$bonusRecup_perso += $bonus_recup_bat;
				$bonusRecup_perso += $bonus_recup_terrain;
				
				// Bonus de récup 
				$paMax_final_perso = $paMax_perso + $bonusPA_perso;
				
				
				// si le perso est dans un bâtiment
				if (in_bat($mysqli, $id_perso)) {

					$id_instance_bat_perso = in_bat($mysqli, $id_perso);

					$sql_b = "SELECT batiment.id_batiment, nom_batiment, taille_batiment, nom_instance FROM batiment, instance_batiment
							WHERE instance_batiment.id_batiment = batiment.id_batiment
							AND instance_batiment.id_instanceBat = '$id_instance_bat_perso'";
					$res_b = $mysqli->query($sql_b);
					$t_b = $res_b->fetch_assoc();

					$id_bat_perso 			= $t_b['id_batiment'];
					$nom_bat_perso			= $t_b['nom_batiment'];
					$taille_bat_perso		= $t_b['taille_batiment'];
					$nom_instance_bat_perso	= $t_b['nom_instance'];
				}
				
				// Si perso chien
				if ($type_perso == 6) {
					if(is_chien_eloigne_chef($mysqli, $id_joueur_perso, $x_perso, $y_perso )){
						$bonusPerception_perso -= 3;
						$perc -= 3;
					}
				}
				// calcul malus pm
				$malus_pm_charge = getMalusCharge($charge_perso, $chargeMax_perso);
				if ($malus_pm_charge == 100) {
					$malus_pm = -$pmMax_perso;
				}
				else {
					$malus_pm = $malus_pm_charge;
				}

				$pmMax_perso 	= $pmMax_perso_tmp + $bonusPM_perso;
				$pm_perso 		= $pm_perso_tmp + $malus_pm;

				$clan_perso = $t_perso2["clan"];

				if($clan_perso == 1){
					$clan = 'rond_b.png';
					$couleur_clan_perso = 'blue';

					$image_profil 		= "battalion_n.png";
					$image_sac 			= "bag_n.png";
					$image_compagnie 	= "company_n_icon.png";
					$image_evenement 	= "events_n.png";
					$image_messagerie 	= "msg_n.png";
					$image_em 			= "em_nord.png";

				}else if($clan_perso == 2){
					$clan = 'rond_r.png';
					$couleur_clan_perso = 'red';

					$image_profil 		= "battalion_s.png";
					$image_sac 			= "bag_s.png";
					$image_compagnie 	= "company_s_icon.png";
					$image_evenement 	= "events_s.png";
					$image_messagerie 	= "msg_s.png";
					$image_em 			= "em_sud.png";

				}else if($clan_perso == 0){
					$clan = 'rond_r.png';
					$couleur_clan_perso = 'black';

					$image_profil 		= "profil_sud4.png";
					$image_sac 			= "sac_sud2.png";
					$image_compagnie 	= "compagnie_sud2.png";
					$image_evenement 	= "evenement_sud.png";
					$image_messagerie 	= "messagerie_sud.png";
					$image_em 			= "em_sud2.png";
				}
				
				maj_visu($mysqli, $clan_p, $carte, $x_perso, $y_perso, $perc, $id_perso, $type_perso, $id_joueur_perso);

				// récupération du grade du perso
				$sql_grade = "SELECT perso_as_grade.id_grade, nom_grade FROM perso_as_grade, grades WHERE perso_as_grade.id_grade = grades.id_grade AND id_perso='$id_perso'";
				$res_grade = $mysqli->query($sql_grade);
				$t_grade = $res_grade->fetch_assoc();

				$id_grade_perso 	= $t_grade["id_grade"];
				$nom_grade_perso 	= $t_grade["nom_grade"];

				// --- Récupération et calcul du grade cohérent ---
                    $sql_grade = "SELECT pag.id_grade, g.nom_grade, g.pc_grade
                                                FROM perso_as_grade pag, grades g
                                                WHERE pag.id_grade = g.id_grade AND pag.id_perso='$id_perso'";
                    $res_grade = $mysqli->query($sql_grade);
                    $t_grade = $res_grade->fetch_assoc();

                    $id_grade_actuel = $t_grade["id_grade"];
                    $nom_grade_perso = $t_grade["nom_grade"];
                    $pc_grade_perso  = $t_grade["pc_grade"];

                    // Logic de calcul automatique pour les Grouillots (si pc_grade == 0)
                    if ($pc_grade_perso == 0) {
                            $paliers_xp = [
                                    1   => 0,
                                    101 => 300,
                                    102 => 600,
                                    103 => 900,
                                    104 => 1200,
                                    105 => 1500,
                                    106 => 1800,
                                    107 => 2100
                            ];

                            $id_grade_theo = 1;
                            foreach ($paliers_xp as $id_p => $xp_req) {
                                    if ($xp_perso >= $xp_req) {
                                            $id_grade_theo = $id_p;
                                    }
                            }

                            // Si le grade doit changer, on met à jour pour l'affichage (et le nom)
                            if ($id_grade_theo != $id_grade_actuel) {
                                    $id_grade_perso = $id_grade_theo;
                                    $sql_n = "SELECT nom_grade FROM grades WHERE id_grade = '$id_grade_theo'";
                                    $res_n = $mysqli->query($sql_n);
                                    $t_n = $res_n->fetch_assoc();
                                    $nom_grade_perso = $t_n['nom_grade'];
                            } else {
                                    $id_grade_perso = $id_grade_actuel;
                            }

                            // Détermination de l'image avec suffixe clan
                            $suffixe = ($clan_perso == 1) ? '_nord' : '_sud';
                            $image_grade_perso = $id_grade_perso . $suffixe . ".gif";

                    } else {
                            // C'est un Chef (ID 2 à 18), on utilise l'ID brut sans suffixe clan
                            $id_grade_perso = $id_grade_actuel;
                            $image_grade_perso = $id_grade_perso . ".gif";
                    }

				$nom_compagnie_perso = "";
				$nb_demandes_adhesion_compagnie = 0;
				$nb_demandes_emprunt_compagnie	= 0;
				$nb_demandes_depart_compagnie	= 0;

				// recuperation de l'id de la compagnie du perso
				$sql_groupe = "SELECT id_compagnie from perso_in_compagnie where id_perso='$id_perso' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
				$res_groupe = $mysqli->query($sql_groupe);
				$t_groupe = $res_groupe->fetch_assoc();
				$nb = $res_groupe->num_rows;

				$id_compagnie = $nb ? $t_groupe['id_compagnie'] : 0;
				$genie_compagnie_perso	= 0;

				if($id_compagnie){

					// Recuperation des infos sur la compagnie (dont le nom)
					$sql_groupe2 = "SELECT * FROM compagnies WHERE id_compagnie='$id_compagnie'";
					$res_groupe2 = $mysqli->query($sql_groupe2);
					$t_groupe2 = $res_groupe2->fetch_assoc();

					$nom_compagnie_perso 		= addslashes($t_groupe2['nom_compagnie']);
					$image_compagnie_perso		= $t_groupe2['image_compagnie'];
					$genie_compagnie_perso		= $t_groupe2['genie_civil'];
					$id_parent_compagnie_perso	= $t_groupe2['id_parent'];

					if (isset($id_parent_compagnie_perso)) {

						$sql_p = "SELECT nom_compagnie FROM compagnies WHERE id_compagnie='$id_parent_compagnie_perso'";
						$res_p = $mysqli->query($sql_p);
						$t_p = $res_p->fetch_assoc();

						$nom_compagnie_mere = addslashes($t_p['nom_compagnie']);

						$nom_compagnie_perso = $nom_compagnie_mere." - ".$nom_compagnie_perso;

					}

					// Quel est le poste du perso dans la compagnie ?
					$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie' AND id_perso='$id_perso'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();

					$poste_perso_compagnie = $t['poste_compagnie'];

					// Chef ou Recruteur
					if ($poste_perso_compagnie == 1 || $poste_perso_compagnie == 4) {

						// Vérifier nouvelles demandes d'adhésion
						$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie' AND attenteValidation_compagnie='1'";
						$res = $mysqli->query($sql);
						$nb_demandes_adhesion_compagnie = $res->num_rows;

						// Vérifier nouvelles demandes de départ
						$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie' AND attenteValidation_compagnie='2'";
						$res = $mysqli->query($sql);
						$nb_demandes_depart_compagnie = $res->num_rows;
					}

					// Chef ou Trésorier
					if ($poste_perso_compagnie == 1 || $poste_perso_compagnie == 3) {

						// Vérifier nouvelles demandes d'emprunt
						$sql = "SELECT banque_compagnie.id_perso FROM banque_compagnie, perso, perso_in_compagnie
								WHERE banque_compagnie.id_perso = perso.id_perso
								AND perso.id_perso = perso_in_compagnie.id_perso
								AND perso_in_compagnie.id_compagnie='$id_compagnie'
								AND demande_emprunt='1'";
						$res = $mysqli->query($sql);
						$nb_demandes_emprunt_compagnie = $res->num_rows;

					}
				}
				else {
					$image_compagnie_perso = "";
				}
				
				// Le perso est-il membre de l'etat major de son camp ?
				if ($nb_em) {
					$pourc_icone = "12%";

					// Verifier nombre compagnies en attente de validation
					$sql = "SELECT * FROM em_creer_compagnie WHERE camp='$clan_perso' AND soft_delete IS NULL AND (votes_result=0 OR votes_result IS NULL)";
					$res = $mysqli->query($sql);
					$nb_compagnie_attente_em = $res->num_rows;

				}

				// Récupération de tous les persos du joueur
				$sql = "SELECT id_perso, nom_perso, chef FROM perso WHERE idJoueur_perso='$id_joueur_perso' AND est_renvoye=0 ORDER BY id_perso";
				$battalion = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
				
				// init vide
				$nom_perso_chef = "";
				
				$sql_mes = "SELECT count(id_message) as nb_mes from message_perso where id_perso='$id_perso' and lu_message='0' AND supprime_message='0'";
				$res_mes = $mysqli->query($sql_mes);
				$t_mes = $res_mes->fetch_assoc();

				$nb_nouveaux_mes = $t_mes["nb_mes"];
				
				// Traitement voir objets à terre
				if(isset($_GET['ramasser']) && $_GET['ramasser'] == "voir"){
					if (!empty($_GET['x']) && !empty($_GET['y'])) {
						
						$x = (int) $_GET['x'];
						$y = (int) $_GET['y'];
						
						// verif si le perso est bien à côté
						$verif_prox = prox_coffre($mysqli, $x, $y, $x_perso, $y_perso);

						if ($verif_prox) {						

							$sql = "SELECT type_objet, id_objet, nb_objet FROM objet_in_carte WHERE x_carte='$x' AND y_carte='$y'";
							$itemsOnMap = $mysqli->query($sql);
							$itemsOnMap->fetch_assoc();
						}
						else {
							$erreur = "vous êtes trop loin ou il n'y a pas d'objet ici";

							// Tentative de triche !
							$text_triche = "Le perso $id_perso a essayé de jouer avec les paramètres pour voir les objets à ramasser !";

							$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
							$mysqli->query($sql);
						}
					}
				}
				
				// Récupération de l'arme de CaC équipé sur le perso
				$sql = "SELECT arme.id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme, degatZone_arme, building_damage
						FROM arme, perso_as_arme
						WHERE arme.id_arme = perso_as_arme.id_arme
						AND porteeMax_arme = 1
						AND perso_as_arme.est_portee = '1'
						AND id_perso = '$id_perso' ORDER BY arme.id_arme DESC";
				$CcWeapon = $mysqli->query($sql);
				$nb_arme_cac = $CcWeapon->num_rows;

				if ($nb_arme_cac > 1) {
					$i = 1;

					while ($t_cac = $CcWeapon->fetch_assoc()) {

						if ($i == 1) {
							$id_arme_cac			= $t_cac["id_arme"];
							$nom_arme_cac 			= $t_cac["nom_arme"];
							$porteeMin_arme_cac 	= $t_cac["porteeMin_arme"];
							$porteeMax_arme_cac 	= $t_cac["porteeMax_arme"];
							$coutPa_arme_cac 		= $t_cac["coutPa_arme"];
							$degatMin_arme_cac 		= $t_cac["degatMin_arme"];
							$valeur_des_arme_cac 	= $t_cac["valeur_des_arme"];
							$precision_arme_cac 	= $t_cac["precision_arme"];
							$degatZone_arme_cac 	= $t_cac["degatZone_arme"];
							$building_damage_cac 	= $t_cac["building_damage"];

							$degats_arme_cac = $degatMin_arme_cac."D".$valeur_des_arme_cac;
						}
						else {
							$id_arme_cac2			= $t_cac["id_arme"];
							$nom_arme_cac2 			= $t_cac["nom_arme"];
							$porteeMin_arme_cac2 	= $t_cac["porteeMin_arme"];
							$porteeMax_arme_cac2 	= $t_cac["porteeMax_arme"];
							$coutPa_arme_cac2		= $t_cac["coutPa_arme"];
							$degatMin_arme_cac2 	= $t_cac["degatMin_arme"];
							$valeur_des_arme_cac2 	= $t_cac["valeur_des_arme"];
							$precision_arme_cac2 	= $t_cac["precision_arme"];
							$degatZone_arme_cac2 	= $t_cac["degatZone_arme"];
							$building_damage_cac2 	= $t_cac["building_damage"];

							$degats_arme_cac2 = $degatMin_arme_cac2."D".$valeur_des_arme_cac2;
						}

						$i++;
					}
				}
				else {
					$t_cac = $CcWeapon->fetch_assoc();

					if ($t_cac != NULL) {
						$id_arme_cac			= $t_cac["id_arme"];
						$nom_arme_cac 			= $t_cac["nom_arme"];
						$porteeMin_arme_cac 	= $t_cac["porteeMin_arme"];
						$porteeMax_arme_cac 	= $t_cac["porteeMax_arme"];
						$coutPa_arme_cac 		= $t_cac["coutPa_arme"];
						$degatMin_arme_cac 		= $t_cac["degatMin_arme"];
						$valeur_des_arme_cac 	= $t_cac["valeur_des_arme"];
						$precision_arme_cac 	= $t_cac["precision_arme"];
						$degatZone_arme_cac 	= $t_cac["degatZone_arme"];
						$building_damage_cac 	= $t_cac["building_damage"];
					} else {
						$id_arme_cac			= 1000;
						$nom_arme_cac 			= "Poings";
						$porteeMin_arme_cac 	= 1;
						$porteeMax_arme_cac 	= 1;
						$coutPa_arme_cac 		= 3;
						$degatMin_arme_cac 		= 4;
						$valeur_des_arme_cac 	= 6;
						$precision_arme_cac 	= 30;
						$degatZone_arme_cac 	= 0;
					}

					$degats_arme_cac = $degatMin_arme_cac."D".$valeur_des_arme_cac;
				}
				
				// Récupération de la liste des persos à portée d'attaque arme CaC
				$perc_att = $perc;
				if ($perc_att <= 0) {
					$perc_att = 1;
				}
				$res_portee_cac = resource_liste_cibles_a_portee_attaque($mysqli, 'carte', $id_perso, $porteeMin_arme_cac, $porteeMax_arme_cac, $perc_att, 'cac');
				$targets_cac = $res_portee_cac->fetch_all(MYSQLI_ASSOC);
				
				// Récupération de l'arme à distance sur le perso
				$sql = "SELECT arme.id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme, degatZone_arme, building_damage
						FROM arme, perso_as_arme
						WHERE arme.id_arme = perso_as_arme.id_arme
						AND porteeMax_arme > 1
						AND perso_as_arme.est_portee = '1'
						AND id_perso = '$id_perso'";
				$res = $mysqli->query($sql);
				$t_dist = $res->fetch_assoc();

				if ($t_dist != NULL) {
					$id_arme_dist 			= $t_dist["id_arme"];
					$nom_arme_dist 			= $t_dist["nom_arme"];
					$porteeMin_arme_dist 	= $t_dist["porteeMin_arme"];
					$porteeMax_arme_dist 	= $t_dist["porteeMax_arme"];
					$coutPa_arme_dist 		= $t_dist["coutPa_arme"];
					$degatMin_arme_dist 	= $t_dist["degatMin_arme"];
					$valeur_des_arme_dist 	= $t_dist["valeur_des_arme"];
					$precision_arme_dist 	= $t_dist["precision_arme"];
					$degatZone_arme_dist 	= $t_dist["degatZone_arme"];
					$building_damage_dist	= $t_dist["building_damage"];
				} else {
					$id_arme_dist			= 2000;
					$nom_arme_dist 			= "Cailloux";
					$porteeMin_arme_dist 	= 1;
					$porteeMax_arme_dist 	= 2;
					$coutPa_arme_dist 		= 3;
					$degatMin_arme_dist 	= 5;
					$valeur_des_arme_dist 	= 6;
					$precision_arme_dist 	= 25;
					$degatZone_arme_dist 	= 0;
				}

				$degats_arme_dist = $degatMin_arme_dist."D".$valeur_des_arme_dist;

				// Récupération de la liste des persos à portée d'attaque arme dist
				$res_portee_dist = resource_liste_cibles_a_portee_attaque($mysqli, 'carte', $id_perso, $porteeMin_arme_dist, $porteeMax_arme_dist, $perc_att, 'dist');
				$targets_dist = $res_portee_dist->fetch_all(MYSQLI_ASSOC);

				//<!--Génération de la carte-->
				$perc_carte = $perc;
				if ($perc_carte < 0) {
					$perc_carte = 0;
				}
				?>
                <?php
                            // --- 1. CONNEXION INDÉPENDANTE ---
                            $mysqlienv = @new mysqli(BDD_HOST, BDD_LOGIN, BDD_PASSWORD, BDD_NAME);

                            // --- 2. RÉCUPÉRATION DES DONNÉES  ---
                            // On utilise des variables temporaires avec le préfixe m_
                            //$m_is_night = $is_night_now ?? false;
                            $m_is_night = true;
                            $m_saison   = $saison ?? "Printemps";
                            $m_date_h   = $date_historique_complete ?? "01 Mars 1861";

                            $meteo_test_x = $x_perso ?? 0;
                            $meteo_test_y = $y_perso ?? 0;

                            // Configuration visuelle du bandeau
                            if ($m_is_night) {
                                $m_src_icone   = "nuit.gif";
                                $m_texte_cycle = "Nuit";
                                $m_couleur_txt = "#1e90ff";
                            } else {
                                $m_src_icone   = "jour.gif";
                                $m_texte_cycle = "Jour";
                                $m_couleur_txt = "#ff8c00";
                            }

                            // Sécurité fonction météo
                            if (!function_exists('getInfoMeteoCase')) {
                                function getInfoMeteoCase($db, $x, $y) {
                                    if (!$db || $db->connect_error) return null;
                                    $sql = "SELECT type_meteo FROM meteo
                                            WHERE (POWER(x_center - ".(int)$x.", 2) + POWER(y_center - ".(int)$y.", 2) <= POWER(rayon, 2))
                                            AND NOW() BETWEEN date_debut AND date_fin LIMIT 1";
                                    $res = $db->query($sql);
                                    if ($res && $row = $res->fetch_assoc()) {
                                        return $row['type_meteo'];
                                    }
                                    return null;
                                }
                            }

                            $nom_meteo_actuelle = getInfoMeteoCase($mysqlienv, $meteo_test_x, $meteo_test_y);
                            ?>

                            <style>
                                .game-status-banner {
                                    background-color: #ebf2fb;
                                    border: 1px solid #7ea7d7;
                                    padding: 10px;
                                    display: flex;
                                    align-items: center;
                                    margin-bottom: 5px;
                                    border-radius: 4px;
                                    color: #000;
                                    font-family: Arial, sans-serif;
                                }
                                .cycle-icon-container {
                                    margin-right: 15px;
                                    border: 1px solid #7ea7d7;
                                    background-color: #fff;
                                    width: 62px;
                                    height: 62px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                }
                                .info-container {
                                    display: flex;
                                    flex-direction: column;
                                    text-align: left;
                                }
                                .cycle-title {
                                    font-weight: bold;
                                    font-size: 18px;
                                }
                                .date-history {
                                    font-family: 'Courier New', Courier, monospace;
                                    font-size: 16px;
                                    color: #333;
                                    margin-top: 3px;
                                    font-weight: bold;
                                }
                            </style>
<!DOCTYPE html>
<html lang="fr">
    <head>
		<title>Nord VS Sud</title>

		<!-- Required meta tags -->
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Le jeu au tour par tour sur la guerre de sécession">

		<!-- Bootstrap CSS -->
		<!--<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">-->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
		<link rel="stylesheet" href="../public/css/app.css">

		<link href="../style2.css" rel="stylesheet" type="text/css">

		<!-- Scripts -->
		<!-- Bunddle Popper.js & Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" defer></script>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
		<script type="text/javascript" src="../public/js/app.js" defer></script>
        <style>

        </style>
	</head>
	<body class='game'>
		<div class='background-img homepage-bg'>
		</div>
		<div class="toast-container position-fixed top-0 end-0 p-3">
			<?php if(isset($erreur) && !empty($erreur)): ?>
			<div class="toast bg-danger-subtle text-danger-emphasis fade show" role="alert" aria-live="assertive" aria-atomic="true">
				<div class="d-flex">
					<svg xmlns="http://www.w3.org/2000/svg" class="size-10 m-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<div class="toast-body">
						<?= $erreur ?>
					</div>
					<button type="button" class="btn-close m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
				</div>
			</div>
			<?php endif ?>
			<?php if(isset($mess) && !empty($mess)): ?>
			<div class="toast bg-primary-subtle text-primary-emphasis fade show" role="alert" aria-live="assertive" aria-atomic="true">
				<div class="d-flex">
					<svg xmlns="http://www.w3.org/2000/svg" class="size-10 m-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<div class="toast-body">
						<?= $mess ?>
					</div>
					<button type="button" class="btn-close m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
				</div>
			</div>
			<?php endif ?>
		</div>
        <header class='container-fluid p-0'>
			<?php if(!empty($permissionMsg)):?>
			<div class="row">
				<div class='col'>
					<div class='p-4 m-0 alert alert-warning' role="alert">
						<!--<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>-->
						<span class='align-middle fw-semibold'><?= $permissionMsg?></span>
					</div>
				</div>
			</div>
			<?php endif;?>
			<div class="offcanvas offcanvas-start bg-main shadow-lg" tabindex="-1" id="offcanvasMainMenu" aria-labelledby="offcanvasMainMenuLabel">
				<div class="offcanvas-header">
					<h5 class="offcanvas-title" id="offcanvasMainMenuLabel">Menu</h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
					<ul class="nav flex-column navMainMenu">
						<li class="nav-item">
							<a class="nav-link" href="?action=character">
								<img src="../public/img/icons/<?php echo $image_profil; ?>" class='size-12' alt="profil">
								<span class='title-ribbon title-ribbon-sm d-inline-block text-center w-50'>Mon bataillon</span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="evenement.php">
								<img src="../public/img/icons/<?php echo $image_evenement; ?>" class='size-12' alt="évènements">
								<span class='title-ribbon title-ribbon-sm d-inline-block text-center w-50'>Évènements</span>
							</a>
						</li>
						<li class="nav-item d-md-none">
							<a class="nav-link" href="sac.php">
								<img src="../public/img/icons/<?php echo $image_sac; ?>" class='size-12' alt="sac">
								<span class='title-ribbon title-ribbon-sm d-inline-block text-center w-50'>Sac</span>
							</a>
						</li>
						<li class="nav-item d-none">
							<a class="nav-link" href="carte/carte.php">
								<img src="../public/img/icons/map_icon.png" class='size-12' alt="mini map">
								<span class='title-ribbon title-ribbon-sm d-inline-block text-center w-50'>Carte</span>
							</a>
						</li>
						<?php if ($type_perso != 6): ?>
						<li class="nav-item d-md-none">
							<a class="nav-link" href="messagerie.php">
								<img src="../public/img/icons/<?php echo $image_messagerie; ?>" class='size-12' alt="messagerie">
								<span class='title-ribbon title-ribbon-sm d-inline-block text-center w-50 position-relative'>Messagerie<?php if($nb_nouveaux_mes) { echo "<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger'>$nb_nouveaux_mes</span>"; }?></span>
							</a>
						</li>
						<?php endif; ?>
						<li class="nav-item">
							<a class="nav-link" href="?action=ranking">
								<img src="../public/img/icons/ranking_icon.png" class='size-12' alt="classement">
								<span class='title-ribbon title-ribbon-sm d-inline-block text-center w-50'>Classements</span>
							</a>
						</li>
						<?php if ($type_perso != 6): ?>
						<li class="nav-item">
							<a class="nav-link" href="compagnie.php">
								<img src="../public/img/icons/<?php echo $image_compagnie; ?>" class='size-12' alt="compagnie">
								<span class='title-ribbon title-ribbon-sm d-inline-block text-center w-50 position-relative'>Compagnie
								<?php if ($nb_demandes_adhesion_compagnie || $nb_demandes_depart_compagnie || $nb_demandes_emprunt_compagnie) { ?>
									<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill p-2 bg-danger border border-light rounded-circle'><span class="visually-hidden">Demandes en attente</span></span>
								<?php }?>
								</span>
							</a>
						</li>
						<?php endif;?>
						<?php if($nb_em): ?>
						<li class="nav-item">
							<a class="nav-link" href="command.php">
								<img src="../public/img/icons/<?php echo $image_em; ?>" class='size-12' alt="etat major">
								<span class='title-ribbon title-ribbon-sm d-inline-block text-center w-50 position-relative'>État Major
									<?php if ($nb_compagnie_attente_em) {?>
										<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill p-2 bg-danger border border-light rounded-circle'><span class="visually-hidden">Compagnies en attente</span></span>
									<?php	}?>
								</span>
							</a>
						</li>
						<?php endif;?>
					</ul>
				</div>
			</div>
			<div class="offcanvas offcanvas-end bg-main shadow" tabindex="-1" id="offcanvasAdditionnalMenu" aria-labelledby="offcanvasAdditionnalMenuLabel">
				<div class="offcanvas-header">
					<h5 class="offcanvas-title" id="offcanvasAdditionnalMenuLabel">Communauté et paramétrages</h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
					<ul class="nav flex-column text-end">
						<li class="nav-item mb-2">
							<a class="btn btn-lg btn-light w-100" href="?action=user&op=show&id=<?=$_SESSION["ID_joueur"]?>">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
								  <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
								  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
								</svg>
								Gérer mon Compte
							</a>
						</li>
						<li class="nav-item my-2 dropdown">
							<a class="dropdown-toggle btn btn-lg btn-info w-100" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 me-1 align-text-top">
									<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								Aide
							</a>
							<ul class="dropdown-menu w-100">
								<li><a class="dropdown-item" href="https://www.encyclopedienvs.nord-vs-sud.fr/index.php/Accueil">Règles</a></li>
								<li><a class="dropdown-item" href="?action=faq">FAQ</a></li>
								<li><a class="dropdown-item" href="question_anim.php">Questions aux anims</a></li>
								<li><hr class="dropdown-divider"></li>
								<li><a class="dropdown-item" href="https://discord.gg/JTYKxfdJ6B">DISCORD du jeu</a></li>
							</ul>
						</li>
						<li class="nav-item my-2 dropdown">
							<a class="btn btn-lg w-100 fw-semibold text-light" href="https://discord.gg/JTYKxfdJ6B" style='background:#5865F2'>
								<img class="img-fluid size-8" src="../public/img/icons/Discord-Symbol-White.png" alt="Discord">
								DISCORD du jeu
							</a>
							
						</li>
						<li class="nav-item my-2 d-none">
							<a class='btn btn-lg btn-primary w-100' href="capture.php">Déclarer une capture</a>
						</li>
						<li class="nav-item my-2">
							<a class='btn btn-lg btn-warning w-100' href="missions.php">
								Missions
								<?php if ($nb_missions_actives > 0) : ?>				
								<span class='badge badge-success'><?= $nb_missions_actives ?></span>
								<?php endif; ?>
							</a>
						</li>
						<?php if(redac_perso($mysqli, $id_perso) || $user->animateur || $admin): ?>
						<li class="nav-item dropdown my-2">
							<a class="dropdown-toggle btn btn-lg btn-warning w-100" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							Administration
							<?php if ($nb_demande_a_traiter > 0 ): ?>
									<span class='badge text-bg-danger'>
										?
									</span>
							<?php endif; ?>
							</a>
							<ul class="dropdown-menu w-100">
								<?php // Redacteur
								if(redac_perso($mysqli, $id_perso)): ?>
								<li class="nav-item">
									<a class='dropdown-item' href='redacteur.php'>Rédaction</a>
								</li>
								<?php endif; ?>
								<?php // Animation
								if($user->animateur):?>
									<li class="nav-item">
										<a class='dropdown-item' href='animation.php'>
											Animation
											<?php if ($nb_demande_a_traiter > 0): ?>
											<span class='badge text-bg-danger' title='<?=$nb_demande_a_traiter?> demandes en attente'>
													<?= $nb_demande_a_traiter?>
												</span>
											<?php endif; ?>
										</a>
									</li>
								<?php endif; ?>
								<?php // Admin
								if($admin):?>
									<li class="nav-item">
										<a class='dropdown-item' href='admin_nvs.php'>Admin</a>
									</li>
								<?php endif; ?>
							</ul>
						</li>
						<?php endif; ?>
						<li class="nav-item mt-2">
							<a class='btn btn-danger w-100 fw-semibold' href="../logout.php">Déconnexion</a>
						</li>
					</ul>
				</div>
			</div>
			<nav class='container-fluid bg-main bg-main-var shadow p-0'>
				<div class='row align-items-center bg-body-secondary'>
					<div class='col-3 ps-4'>
						<a class='fw-bold' href='/'>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
							</svg>
							rafraîchir
						</a>
					</div>
					<div class='col p-1'>
						<div class='d-flex flex-row justify-content-center'>
							<div class='pe-5'>
								<!-- date du serveur -->
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
								</svg>
								<span id='date'><?=$date_serveur->format('d-m-Y')?></span>
								<span id='clock'><?=$date_serveur->format('H:i:s')?></span> 
							</div>
							<div>
								<!-- DLA -->
								<img class="img-fluid size-6" src="../public/img/icons/hourglass.png" alt="messagerie">
								<span class='fw-semibold'><?=date('d-m-Y',$n_dla)?></span>
								<span class='fw-semibold'><?=date('H:i:s',$n_dla)?></span>
							</div>
						</div>
					</div>
					<div class='col-1 col-sm-3'>
					</div>
				</div>
				<div class='row align-items-center'>
					<div class='col-2 col-md order-first text-start pe-0'>
						<button class="btn btn-dark btn-lg px-3 py-4 m-0 rounded-0 h-100" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMainMenu" aria-controls="offcanvasMainMenu">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
								<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
							</svg>
						</button>
					</div>
					<div class='col col-md-6 order-md-3'>
						<div class='d-flex flex-row justify-content-center navMainMenu'>
							<div class='px-3 text-center my-1 d-none d-sm-block'>
								<a class="main-menu-button" href="sac.php">
									<img class="img-fluid size-10 mb-1" src="../public/img/icons/<?= $image_sac; ?>" alt="sac"><br>
									<span class='cat-title'>Sac</span>
								</a>
							</div>
							<div class='px-3 text-center my-1'>
								<a class="main-menu-button" href="carte/carte.php">
									<img class="img-fluid size-10 mb-1" src="../public/img/icons/map_icon.png" alt="mini map"><br>
									<span class='cat-title'>Carte</span>
								</a>
							</div>
							<div class='px-3 text-center my-1'>
								<a class="main-menu-button" href="evenement.php">
									<img class="img-fluid size-10 mb-1" src="../public/img/icons/<?php echo $image_evenement; ?>" alt="évènements"><br>
									<span class='cat-title'>Évènements</span>
								</a>
							</div>
							<?php if ($type_perso != 6): ?>
							<div class='px-3 text-center my-1 d-none d-md-block'>
								<a class="main-menu-button" href="messagerie.php">
									<img class="img-fluid size-10 mb-1" src="../public/img/icons/<?= $image_messagerie; ?>" alt="messagerie"><br>
									<span class='cat-title position-relative'>Messagerie<?php if($nb_nouveaux_mes) { echo "<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger'>$nb_nouveaux_mes</span>"; }?></span>
								</a>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<div class='col-2 col-md order-4 text-end ps-0'>
						<button class="btn btn-dark btn-lg px-3 py-4 m-0 rounded-0 h-100" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAdditionnalMenu" aria-controls="offcanvasAdditionnalMenu">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
								<path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
								<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
							</svg>
						</button>
					</div>
				</div>
			</nav>
		</header>
		<main class='container-fluid main-page overflow-scroll'>
			<div class="row flex-nowrap">
				<!-- interface joueur -->
				<div class='col-12 col-md-6 col-lg-3 col-xl-2 bg-body-tertiary bg-main p-3'>
					<div class='row'>
						<div class='col-2'>
							<div class="position-relative icon-character">
								<button type="button" class="btn btn-sm btn-primary position-absolute top-0 start-100 translate-middle p-0 rounded-circle" data-bs-toggle="collapse" data-bs-target="#collapseInfoCharac" aria-expanded="false" aria-controls="collapseInfoCharac">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
									</svg>
								</button>
								<span class='fw-bold position-absolute top-50 ms-1'><?= $id_perso ?></span>
								<img src="../images_perso/<?= "$image_perso"?>" width=40 height=40>
							</div>
						</div>
						<form class='col' method='post' action='index.php'>
							<label class="fw-semibold form-label visually-hidden" for="liste_perso">Nom : </label>
							<select class='form-select' id='liste_perso' name='liste_perso' onchange="this.form.submit()">
							<?php foreach($battalion as $t_liste_perso):

								$id_perso_liste 	= $t_liste_perso["id_perso"];
								$nom_perso_liste 	= $t_liste_perso["nom_perso"];
								$chef_perso			= $t_liste_perso["chef"];

								if($chef_perso){$nom_perso_chef = $nom_perso_liste;}
							?>
								<option value="<?= $id_perso_liste?>" <?php if($id_perso == $id_perso_liste): ?> selected<?php endif;?>>
									<?= $nom_perso_liste?> [<?= $id_perso_liste?>]
								</option>
							<?php endforeach;?>
							</select>
							
						</form>
					</div>
					<div class="row pt-2 collapse" id="collapseInfoCharac">
						<div class='col-12'>

							<span class='fw-semibold'>Grade : </span>
                            <a href="grades.php">
							<?php
								echo htmlspecialchars($nom_grade_perso);

								// 1. On prépare l'ID et le suffixe
								$id_check = (int)$id_grade_perso;
								$suffixe = "";

								// 2. La règle pour les grouillots (1 et 101 à 107)
								if ($id_check === 1 || ($id_check >= 101 && $id_check <= 107)) {
									$suffixe = ($clan_perso == 1) ? '_nord' : '_sud';
								}

								// 3. Mise en place de l'image finale calculée
								$image_nom = $id_check . $suffixe . ".gif";
								$mon_image_calculee = "../images/grades/" . $image_nom;
							?>
                            <img alt="<?php echo htmlspecialchars($nom_grade_perso); ?>" title="<?php echo htmlspecialchars($nom_grade_perso); ?>" src="<?php echo $mon_image_calculee; ?>" width="40" height="40"></a><br>
							<span class='fw-semibold'>Chef : </span><?= $nom_perso_chef; ?><br>
							<span class='fw-semibold'>Bataillon : </span><a href="bataillon.php?id_bataillon=<?=$id_joueur_perso?>"><?=$bataillon_perso?></a><br>
							<span class='fw-semibold'>Compagnie : </span><a href="compagnie.php"><?=stripslashes($nom_compagnie_perso)?></a>
						</div>
					</div>
					<div class='row'>
						<?php
							$percents_PV = ($pvMax_perso > 0) ? round($pv_perso/$pvMax_perso*100):0;
							$percents_PM = ($pmMax_perso > 0) ? round($pm_perso/$pmMax_perso*100):0;
							$percents_PA = ($paMax_final_perso > 0) ? round($pa_perso/$paMax_final_perso*100):0;
							
							$percents_PV_style = ($percents_PV<40) ? ' text-success-emphasis ps-2':'';
							$percents_PM_style = ($percents_PM<50) ? ' text-primary-emphasis ps-2':'';
							$percents_PA_style = ($percents_PA<50) ? ' ps-2':'';
						?>

                            <div class='col-12 mt-2'>
                                <div class="progress bg-success-subtle shadow" role="progressbar" aria-label="points de vie" aria-valuenow="<?=$pv_perso?>" aria-valuemin="0" aria-valuemax="<?=$pvMax_perso?>" style="height: 2rem">
    								<div class="progress-bar progress-bar-striped text-bg-success fw-semibold fs-6 overflow-visible<?= $percents_PV_style?>" style="width: <?= $percents_PV ?>%">
    									PV : <?= $percents_PV ?>% (<?=$pv_perso?>/<?=$pvMax_perso?>)
    								</div>
    							</div>
    						</div>
									
    						<div class='col-6 mt-2'>
    							<div class="progress bg-primary-subtle shadow" role="progressbar" aria-label="points de mouvement" aria-valuenow="<?=$pm_perso?>" aria-valuemin="0" aria-valuemax="<?=$pmMax_perso?>" style="height: 2rem">
    								<div class="progress-bar progress-bar-striped text-bg-primary fw-semibold fs-6 overflow-visible<?= $percents_PM_style?>" style="width: <?= $percents_PM ?>%">
    									PM : <?= $percents_PM ?>% (<?=$pm_perso?>/<?=$pmMax_perso?>)
    								</div>
    							</div>
							</div>
        						<div class='col-6 mt-2'>
        							<div class="progress bg-danger-subtle shadow" role="progressbar" aria-label="points d'action" aria-valuenow="<?=$pa_perso?>" aria-valuemin="0" aria-valuemax="<?= $paMax_final_perso?>" style="height: 2rem">
        								<div class="progress-bar progress-bar-striped text-warning-emphasis text-bg-warning fw-semibold fs-6 overflow-visible<?= $percents_PA_style?>" style="width: <?= round($pa_perso/$paMax_final_perso*100)?>%">PA : <?=$pa_perso?>/<?=$paMax_final_perso?></div>
        							</div>
        						</div>
        					

                    </div>
                    <br />
                    <div class="game-status-banner">
                                <div class="cycle-icon-container">
                                    <img id="cycle-icone" src="/fond_carte/<?php echo $m_src_icone; ?>" alt="Cycle" style="width:60px; height:60px;">
                                </div>

                                <div class="info-container">
                                    <span class="cycle-title">
                                        <strong id="cycle-texte" style="color: <?php echo $m_couleur_txt; ?>;">
                                            <?php echo $m_texte_cycle; ?>
                                        </strong>
                                        (<span id="saison-texte"><?php echo $m_saison; ?></span>)

                                        <span id="zone-meteo-dynamique" style="font-size: 14px; color: #666; <?php echo empty($nom_meteo_actuelle) ? 'display:none;' : ''; ?>">
                                             - Météo : <span id="meteo-nom"><?php echo htmlspecialchars($nom_meteo_actuelle ?? ''); ?></span>
                                        </span>
                                    </span>

                                    <span class="date-history">
                                        Nord-vs-Sud : <span id="date-historique"><?php echo $m_date_h; ?></span>
                                    </span>

                                    <div id="alerte-meteo-js" style="display:none; margin-top: 5px; background: #fff3cd; color: #856404; padding: 5px; border: 1px solid #ffeeba; border-radius: 3px; font-size: 13px; width: 100%;"></div>
                                </div>
                            </div>
					<div class='col-12 mt-4'>
						<!-- caractéristiques perso -->
						<div class='row d-none d-md-flex'>
							<div class='col'>
								<table class='table table-striped shadow'>
									<tbody>
										<tr>
											<td><b>XP</b></td>
											<td><?= $xp_perso; ?></td>
										</tr>
										<tr>
											<td><b>XPI</b></td>
											<td><?= $pi_perso; ?></td>
										</tr>
										<tr>
											<td><b>PC</b></td>
											<td><?= $pc_perso; ?></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class='col'>
								<table class='table table-striped shadow'>
									<tr>
										<td><b>Perception</b></td>
										<td>
                                        <?php
                                                // 1. On récupère la variable de config.php
                                                global $malus_nuit_perception;
                                                if (!isset($malus_nuit_perception)) { $malus_nuit_perception = 0; }

                                                // 2. Préparation de l'infobulle (Tooltip)
                                                $texte_tooltip = "Base : " . $perception_perso;

                                                // 3. Ajout des bonus/malus d'objets (Longue-vue, etc.)
                                                if ($bonusPerception_perso != 0) {
                                                    $signe = ($bonusPerception_perso > 0) ? "+" : "";
                                                    $texte_tooltip .= " <br><b>Objets : (" . $signe . $bonusPerception_perso . ")</b>";
                                                }

                                                // 4. Ajout visuel du malus de nuit dans l'infobulle
                                                if ($malus_nuit_perception > 0) {
                                                    $texte_tooltip .= " <br><b style='color:red'>Nuit : -" . $malus_nuit_perception . "</b>";
                                                }

                                                // 5. CALCUL FINAL (Addition de tout)
                                                // Ex: 10 (base) + 2 (longue-vue) - 2 (nuit) = 10
                                                $perception_final_perso = $perception_perso + $bonusPerception_perso - $malus_nuit_perception;

                                                // Sécurité : la perception ne peut pas tomber sous 0
                                                if ($perception_final_perso < 0) { $perception_final_perso = 0; }

                                                // 6. AFFICHAGE FINAL
                                                echo '<a tabindex="0" href="#" data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" data-content="'.$texte_tooltip.'">'.$perception_final_perso.'</a>';
                                            ?></td>
									</tr>
									<tr>
										<td><b>Protection</b></td>
										<td><?=$protec_perso; ?></td>
									</tr>
									<tr>
										<td><b>Récupération</b></td>
										<td>
										<?php
										$texte_tooltip = $recup_perso;
										$bonus_tooltip ='';
										
										if($bonusRecup_perso != 0) {
											$bonus_tooltip = '('.$bonusRecup_perso.')';
											if($bonusRecup_perso > 0){
												$bonus_tooltip = '(+'.$bonusRecup_perso.')';
											}
										}

										$recup_final = $recup_perso + $bonusRecup_perso;
										?>
											<a tabindex="0" href="#" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-html="true" data-bs-content="Base : <?=$texte_tooltip?> <span class='fw-bold'><?=$bonus_tooltip?></span>"><?=$recup_final?></a>
										</td>
									</tr>
									<tr>
										<td><b>Défense</b></td>
										<td>
										<?php
										$texte_tooltip = "Base : ".$bonus_perso."";

										$bonus_defense = getBonusDefenseObjet($mysqli, $id_perso);
										$bonus_defense_bat = get_bonus_defense_instance_bat($mysqli, $id_perso);

										// recuperation des données de la carte
										$sql = "SELECT fond_carte FROM $carte
												WHERE x_carte = $x_perso
												AND y_carte = $y_perso";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();

										$fond_carte_perso = $tab['fond_carte'];

										$bonus_defense_terrain_cac = get_bonus_defense_terrain($fond_carte_perso, 1);
										$bonus_defense_terrain_dist = get_bonus_defense_terrain($fond_carte_perso, 2);

										$bonus_final_cac = $bonus_perso + $bonus_defense + $bonus_defense_terrain_cac + $bonus_defense_bat;
										$bonus_final_dist = $bonus_perso + $bonus_defense + $bonus_defense_terrain_dist + $bonus_defense_bat;
										?>
											<a tabindex="0" href="#" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-html="true" data-bs-content="<?=$texte_tooltip?>">
										<?php
										if ($bonus_final_cac == $bonus_final_dist) {
											echo $bonus_final_cac;
										}
										else {
											echo 'Cac : '.$bonus_final_cac.' - Dist : '.$bonus_final_dist.'</a>';
										}
										?></td>
									</tr>
								</table>
							</div>
						</div>

						<!-- actions de combat pour grands écrans -->
						<div class='row d-none d-md-flex'>
							<h3 class='fs-5'>Actions de combat</h3>
							<!-- bouton pour faire une charge -->
							<?php if (verif_charge_pm($type_perso, $pm_perso) && !in_train($mysqli, $id_perso) && !in_bat($mysqli, $id_perso)) {?>
							<div>
								<form method='post' action='action.php' class='text-center'>
									<input type="hidden" name="liste_action" value="999">
									<button class='btn btn-warning my-2' type='submit' name='action' value='ok'>
										<img class='size-11 float-start mt-1 me-3' src='../public/img/icons/cavalry_charge.png' alt='charge de soldats'>
										CHARGER<br><span class='fs-6'>(consomme tous les PA)</span>
									</button>
								</form>
							</div>
							<?php } ?>
							<!-- compétence d'attaque rapprochée ou soin 1 -->
							<?php if($combat_type != 'distance'){ ?>
							<div class='col-12 table-responsive'>
								<table class='table table-sm table-striped shadow'>
									<thead>
										<th scope="col" colspan='2'><?php if($combat_type == 'heal'){ echo 'soins 1';}else{ echo 'Combat rapproché';};?></th>
									</thead>
									<tbody>
										<tr>
											<td>Arme</td>
											<td><?= $nom_arme_cac; ?></td>
										</tr>
										<tr>
											<td>Coût en PA</td>
											<td><?=	$coutPa_arme_cac; ?></td>
										</tr>
										<tr>
											<td>Dégâts</td>
											<td><?= $degats_arme_cac; ?></td>
										</tr>
										<tr>
											<td>Portée</td>
											<td><?= $porteeMax_arme_cac; ?></td>
										</tr>
										<tr>
											<td>Précision</td>
											<td><?= $precision_arme_cac . " %"; ?></td>
										</tr>
										<?php if(!empty($degatZone_arme_cac) OR !empty($building_damage_cac)){?>
										<tr>
											<td class='fw-bold'>Spécial</td>
											<td>
												<?php if(!empty($degatZone_arme_cac)){ echo 'Dégâts de zone<br>';}?>
												<?php if(!empty($building_damage_cac)){ echo 'Bonus de dégâts sur bâtiments';}?>
											</td>
										</tr>
										<?php } ?>
										<tr>
											<form method="post" action="agir.php" target='_main'>
											<td><input type="submit" value="<?php if($combat_type == 'heal'){ echo 'Soigner';}else{ echo 'Attaquer';} ?>"></td>
											<td>
												<select name='id_attaque_cac'>
													<option value="personne">Qui ?</option>
													<?php
													if ($combat_type == 'heal') {
														foreach($targets_cac as $t_cible_portee_cac){

															$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

															if ($id_cible_cac < 50000) {

																// Un autre perso
																$sql = "SELECT nom_perso, pv_perso, pvMax_perso, bonus_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
																$res = $mysqli->query($sql);
																$tab = $res->fetch_assoc();

																$nom_cible_cac 		= $tab["nom_perso"];
																$pv_cible_cac		= $tab["pv_perso"];
																$pv_max_cible_cac	= $tab["pvMax_perso"];
																$bonus_cible_cac	= $tab["bonus_perso"];
																$camp_cible_cac		= $tab["clan"];

																$couleur_clan_cible = couleur_clan($camp_cible_cac);

																if ($id_arme_cac == 10) {
																	// seringue
																	// On affiche que les persos blessés
																	if ($pv_cible_cac < $pv_max_cible_cac) {
																		echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																	}
																} else if ($id_arme_cac == 11) {
																	// bandage
																	// On affiche que les persos avec malus
																	if ($bonus_cible_cac < 0) {
																		echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																	}
																}
															} else if ($id_cible_cac >= 200000) {

																// Un PNJ
																$sql = "SELECT nom_pnj, pv_i, pvMax_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
																$res = $mysqli->query($sql);
																$tab = $res->fetch_assoc();

																$nom_cible_cac 		= $tab["nom_pnj"];
																$pv_cible_cac		= $tab["pv_i"];
																$pv_max_cible_cac	= $tab["pvMax_pnj"];

																if ($pv_cible_cac < $pv_max_cible_cac) {
																	echo "<option style=\"color:grey\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																}
															} else {
																// Un Batiment => on ne veut pas l'afficher !
															}
														}
													}
													else {
														// Impossible d'attaquer au CaC quand on est dans un train
														if (!in_train($mysqli, $id_perso)) {

															foreach($targets_cac as $t_cible_portee_cac){

																$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

																if ($id_cible_cac < 50000) {

																	// Un autre perso
																	$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
																	$res = $mysqli->query($sql);
																	$tab = $res->fetch_assoc();

																	$nom_cible_cac 	= $tab["nom_perso"];
																	$camp_cible_cac	= $tab["clan"];

																	$couleur_clan_cible = couleur_clan($camp_cible_cac);

																} else if ($id_cible_cac >= 200000) {

																	// Un PNJ
																	$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
																	$res = $mysqli->query($sql);
																	$tab = $res->fetch_assoc();

																	$nom_cible_cac = $tab["nom_pnj"];

																	$couleur_clan_cible = "grey";

																} else {

																	// Un Batiment
																	$sql = "SELECT nom_batiment, nom_instance, camp_instance, pv_instance FROM batiment, instance_batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_cac'";
																	$res = $mysqli->query($sql);
																	$tab = $res->fetch_assoc();

																	$nom_cible_cac = $tab["nom_batiment"];
																	if ($tab["nom_instance"] != "") {
																		$nom_cible_cac .= " ".$tab["nom_instance"];
																	}

																	$camp_cible_cac	= $tab["camp_instance"];

																	$couleur_clan_cible = couleur_clan($camp_cible_cac);
																	$pv_instance	= $tab["pv_instance"];
																	if ($pv_instance <= 0)
																		continue;
																}

																echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
															}
														}
													}
													?>
												</select>
											</td>
											</form>
										</tr>
									</tbody>
								</table>
							</div>
							<?php } ?>
							<!-- compétence d'attaque à distance -->
							<?php if($combat_type != 'close' AND $combat_type != 'heal'){ ?>
							<div class='col-12 table-responsive'>
								<table class='table table-sm table-striped shadow'>
									<thead>
										<th scope="col" colspan='2' >Combat à distance</th>
									</thead>
									<tbody>
										<tr>
											<td>Arme</td>
											<td><?= $nom_arme_dist; ?></td>
										</tr>
										<tr>
											<td>Coût en PA</td>
											<td><?=	$coutPa_arme_dist; ?><?php if (possede_lunette_visee($mysqli, $id_perso)) { echo " (+2)"; } ?></td>
										</tr>
										<tr>
											<td>Dégâts</td>
											<td><?= $degats_arme_dist; ?></td>
										</tr>
										<tr>
											<td>Portée</td>
											<td><?= $porteeMax_arme_dist; ?></td>
										</tr>
										<tr>
											<td>Précision</td>
											<td><?= $precision_arme_dist . " %"; ?></td>
										</tr>
										<?php if(!empty($degatZone_arme_dist) OR !empty($building_damage_dist)){?>
										<tr>
											<td class='fw-bold'>Spécial</td>
											<td>
												<?php if(!empty($degatZone_arme_dist)){ echo 'Dégâts de zone<br>';}?>
												<?php if(!empty($building_damage_dist)){ echo 'Bonus de dégâts sur bâtiments';}?>
											</td>
										</tr>
										<?php } ?>
										<tr>
											<form method="post" action="agir.php" target='_main'>
											<td><input type="submit" value="Attaquer"></td>
											<td>
												<select name='id_attaque_dist'>
													<option value="personne">Qui ?</option>
													<?php
													if (!isset($id_bat_perso) || (isset($id_bat_perso) && $id_bat_perso != 10)) {
														foreach($targets_dist as $t_cible_portee_dist){

															$id_cible_dist = $t_cible_portee_dist["idPerso_carte"];
															$id_instance_in_bat = in_bat($mysqli,$id_perso);

															if ($id_cible_dist != $id_instance_in_bat) {

																if ($id_cible_dist < 50000) {

																	// Un autre perso
																	$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible_dist'";
																	$res = $mysqli->query($sql);
																	$tab = $res->fetch_assoc();

																	$nom_cible_dist = $tab["nom_perso"];
																	$camp_cible_cac	= $tab["clan"];

																	$couleur_clan_cible = couleur_clan($camp_cible_cac);

																} else if ($id_cible_dist >= 200000) {

																	// Un PNJ
																	$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_dist'";
																	$res = $mysqli->query($sql);
																	$tab = $res->fetch_assoc();

																	$nom_cible_dist = $tab["nom_pnj"];

																	$couleur_clan_cible = "grey";

																} else {

																	// Un Batiment
																	$sql = "SELECT nom_batiment, nom_instance, camp_instance, pv_instance FROM batiment, instance_batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_dist'";
																	$res = $mysqli->query($sql);
																	$tab = $res->fetch_assoc();

																	$nom_cible_dist = $tab["nom_batiment"];
																	if ($tab["nom_instance"] != "") {
																		$nom_cible_dist .= " ".$tab["nom_instance"];
																	}

																	$camp_cible_dist	= $tab["camp_instance"];

																	$couleur_clan_cible = couleur_clan($camp_cible_dist);
																	$pv_instance	= $tab["pv_instance"];
																	if ($pv_instance <= 0)
																		continue;
																}

																echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_dist.",".$id_arme_dist."'>".$nom_cible_dist." (mat. ".$id_cible_dist.")</option>";
															}
														}
													}
													?>
												</select>
											</td>
											</form>
										</tr>
									</tbody>
								</table>
							</div>
							<?php } ?>
							<!-- compétence de soins 2 --> 
							<?php if($combat_type == 'heal'){ ?>
							<div class='col-12 table-responsive'>
								<table class='table table-sm table-striped shadow'>
									<thead>
										<th scope="col" colspan='2'>Soins 2</th>
									</thead>
									<tbody>
										<tr>
											<td>Arme</td>
											<td><?= $nom_arme_cac2; ?></td>
										</tr>
										<tr>
											<td>Coût en PA</td>
											<td><?=	$coutPa_arme_cac2; ?></td>
										</tr>
										<tr>
											<td>Dégâts</td>
											<td><?= $degats_arme_cac2; ?></td>
										</tr>
										<tr>
											<td>Portée</td>
											<td><?= $porteeMax_arme_cac2; ?></td>
										</tr>
										<tr>
											<td>Précision</td>
											<td><?= $precision_arme_cac2 . " %"; ?></td>
										</tr>
										<?php if(!empty($degatZone_arme_cac2) OR !empty($building_damage_cac2)){?>
										<tr>
											<td class='fw-bold'>Spécial</td>
											<td>
												<?php if(!empty($degatZone_arme_cac2)){ echo 'Soins de zone<br>';}?>
												<?php if(!empty($building_damage_cac2)){ echo 'Bonus de dégâts sur bâtiments';}?>
											</td>
										</tr>
										<?php } ?>
										<tr>
											<form method="post" action="agir.php" target='_main'>
											<td><input type="submit" value="Soigner"></td>
											<td>
												<select name='id_attaque_cac2'>
													<option value="personne">Qui ?</option>
													<?php 
													
													foreach($targets_cac as $t_cible_portee_cac){

														$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

														if ($id_cible_cac < 50000) {

															// Un autre perso
															$sql = "SELECT nom_perso, pv_perso, pvMax_perso, bonus_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_cac 		= $tab["nom_perso"];
															$pv_cible_cac		= $tab["pv_perso"];
															$pv_max_cible_cac	= $tab["pvMax_perso"];
															$bonus_cible_cac	= $tab["bonus_perso"];
															$camp_cible_cac		= $tab["clan"];

															$couleur_clan_cible = couleur_clan($camp_cible_cac);

															if ($id_arme_cac2 == 10) {
																// seringue
																// On affiche que les persos blessés
																if ($pv_cible_cac < $pv_max_cible_cac) {
																	echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																}
															} else if ($id_arme_cac2 == 11) {
																// bandage
																// On affiche que les persos avec malus
																if ($bonus_cible_cac < 0) {
																	echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																}
															}
														} else if ($id_cible_cac >= 200000) {

															// Un PNJ
															$sql = "SELECT nom_pnj, pv_i, pvMax_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_cac 		= $tab["nom_pnj"];
															$pv_cible_cac		= $tab["pv_i"];
															$pv_max_cible_cac	= $tab["pvMax_pnj"];

															if ($pv_cible_cac < $pv_max_cible_cac) {
																echo "<option style=\"color:grey\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
															}
														}
													}
													?>
												</select>
											</td>
											</form>
										</tr>
									</tbody>
								</table>
							</div>
							<?php } ?>
						</div>
						<!-- caractéristiques perso petits écrans -->
						<div class='row d-md-none'>
							<div class='col-12'>
								<button class="btn btn-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCaracPerso" aria-expanded="false" aria-controls="collapseCaracPerso">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
									</svg>
									Caractéristiques perso 
								</button>
								<div class="collapse" id="collapseCaracPerso">
									<div class="card card-body">
										<div class='row'>
											<div class='col'>
												<table class='table table-striped shadow'>
													<tbody>
														<tr>
															<td><b>XP</b></td>
															<td><?= $xp_perso; ?></td>
														</tr>
														<tr>
															<td><b>XPI</b></td>
															<td><?= $pi_perso; ?></td>
														</tr>
														<tr>
															<td><b>PC</b></td>
															<td><?= $pc_perso; ?></td>
														</tr>
													</tbody>
												</table>
											</div>
											<div class='col'>
												<table class='table table-striped shadow'>
													<tr>
														<td><b>Perception</b></td>
														<td>
														<?php
                                                        // 1. On récupère la variable de config.php
                                                        global $malus_nuit_perception;
                                                        if (!isset($malus_nuit_perception)) { $malus_nuit_perception = 0; }

                                                        // 2. Préparation de l'infobulle (Tooltip)
                                                        $texte_tooltip = "Base : " . $perception_perso;

                                                        // 3. Ajout des bonus/malus d'objets (Longue-vue, etc.)
                                                        if ($bonusPerception_perso != 0) {
                                                            $signe = ($bonusPerception_perso > 0) ? "+" : "";
                                                            $texte_tooltip .= " <br><b>Objets : (" . $signe . $bonusPerception_perso . ")</b>";
                                                        }

                                                        // 4. Ajout visuel du malus de nuit dans l'infobulle
                                                        if ($malus_nuit_perception > 0) {
                                                            $texte_tooltip .= " <br><b style='color:red'>Nuit : -" . $malus_nuit_perception . "</b>";
                                                        }

                                                        // 5. CALCUL FINAL (Addition de tout)
                                                        // Ex: 10 (base) + 2 (longue-vue) - 2 (nuit) = 10
                                                        $perception_final_perso = $perception_perso + $bonusPerception_perso - $malus_nuit_perception;

                                                        // Sécurité : la perception ne peut pas tomber sous 0
                                                        if ($perception_final_perso < 0) { $perception_final_perso = 0; }

                                                        // 6. AFFICHAGE FINAL
                                                        echo '<a tabindex="0" href="#" data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" data-content="'.$texte_tooltip.'">'.$perception_final_perso.'</a>';
                                                    ?></td>
													</tr>
													<tr>
														<td><b>Protection</b></td>
														<td><?=$protec_perso; ?></td>
													</tr>
													<tr>
														<td><b>Récupération</b></td>
														<td>
														<?php
														$texte_tooltip = $recup_perso;
														$bonus_tooltip ='';
														
														if($bonusRecup_perso != 0) {
															$bonus_tooltip = '('.$bonusRecup_perso.')';
															if($bonusRecup_perso > 0){
																$bonus_tooltip = '(+'.$bonusRecup_perso.')';
															}
														}

														$recup_final = $recup_perso + $bonusRecup_perso;
														?>
															<a tabindex="0" href="#" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-html="true" data-bs-content="Base : <?=$texte_tooltip?> <span class='fw-bold'><?=$bonus_tooltip?></span>"><?=$recup_final?></a>
														</td>
													</tr>
													<tr>
														<td><b>Défense</b></td>
														<td>
														<?php
														$texte_tooltip = "Base : ".$bonus_perso."";

														$bonus_defense = getBonusDefenseObjet($mysqli, $id_perso);
														$bonus_defense_bat = get_bonus_defense_instance_bat($mysqli, $id_perso);

														// recuperation des données de la carte
														$sql = "SELECT fond_carte FROM $carte
																WHERE x_carte = $x_perso
																AND y_carte = $y_perso";
														$res = $mysqli->query($sql);
														$tab = $res->fetch_assoc();

														$fond_carte_perso = $tab['fond_carte'];

														$bonus_defense_terrain_cac = get_bonus_defense_terrain($fond_carte_perso, 1);
														$bonus_defense_terrain_dist = get_bonus_defense_terrain($fond_carte_perso, 2);

														$bonus_final_cac = $bonus_perso + $bonus_defense + $bonus_defense_terrain_cac + $bonus_defense_bat;
														$bonus_final_dist = $bonus_perso + $bonus_defense + $bonus_defense_terrain_dist + $bonus_defense_bat;
														?>
															<a tabindex="0" href="#" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-html="true" data-bs-content="<?=$texte_tooltip?>">
														<?php
														if ($bonus_final_cac == $bonus_final_dist) {
															echo $bonus_final_cac;
														}
														else {
															echo 'Cac : '.$bonus_final_cac.' - Dist : '.$bonus_final_dist.'</a>';
														}
														?></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class='col-12'>
								<!-- Actions de combat petits écrans -->
								<button class="btn btn-primary w-100 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCombatActions" aria-expanded="false" aria-controls="collapseCombatActions">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
									</svg>
									Actions de combat
								</button>
								<div class="collapse" id="collapseCombatActions">
									<div class="card card-body table-responsive">
										<!-- bouton pour faire une charge -->
										<?php if (verif_charge_pm($type_perso, $pm_perso) && !in_train($mysqli, $id_perso) && !in_bat($mysqli, $id_perso)) {?>
										<div>
											<form method='post' action='action.php' class='text-center'>
												<input type="hidden" name="liste_action" value="999">
												<button class='btn btn-warning my-2' type='submit' name='action' value='ok'>
													<img class='size-11 float-start mt-1 me-3' src='../public/img/icons/cavalry_charge.png' alt='charge de soldats'>
													CHARGER<br><span class='fs-6'>(consomme tous les PA)</span>
												</button>
											</form>
										</div>
										<?php } ?>
										<!-- compétence d'attaque rapprochée ou soin 1 -->
										<?php if($combat_type != 'distance'){ ?>
										<div class='col-12 table-responsive'>
											<table class='table table-sm table-striped shadow-sm'>
												<thead>
													<th scope="col" colspan='2'><?php if($combat_type == 'heal'){ echo 'soins 1';}else{ echo 'Combat rapproché';};?></th>
												</thead>
												<tbody>
													<tr>
														<td>Arme</td>
														<td><?= $nom_arme_cac; ?></td>
													</tr>
													<tr>
														<td>Coût en PA</td>
														<td><?=	$coutPa_arme_cac; ?></td>
													</tr>
													<tr>
														<td>Dégâts</td>
														<td><?= $degats_arme_cac; ?></td>
													</tr>
													<tr>
														<td>Portée</td>
														<td><?= $porteeMax_arme_cac; ?></td>
													</tr>
													<tr>
														<td>Précision</td>
														<td><?= $precision_arme_cac . " %"; ?></td>
													</tr>
													<?php if(!empty($degatZone_arme_cac) OR !empty($building_damage_cac)){?>
													<tr>
														<td class='fw-bold'>Spécial</td>
														<td>
															<?php if(!empty($degatZone_arme_cac)){ echo 'Dégâts de zone<br>';}?>
															<?php if(!empty($building_damage_cac)){ echo 'Bonus de dégâts sur bâtiments';}?>
														</td>
													</tr>
													<?php } ?>
													<tr>
														<form method="post" action="agir.php" target='_main'>
														<td><input type="submit" value="<?php if($combat_type == 'heal'){ echo 'Soigner';}else{ echo 'Attaquer';} ?>"></td>
														<td>
															<select name='id_attaque_cac'>
																<option value="personne">Qui ?</option>
																<?php
																if ($combat_type == 'heal') {
																	foreach($targets_dist as $t_cible_portee_dist){

																		$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

																		if ($id_cible_cac < 50000) {

																			// Un autre perso
																			$sql = "SELECT nom_perso, pv_perso, pvMax_perso, bonus_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
																			$res = $mysqli->query($sql);
																			$tab = $res->fetch_assoc();

																			$nom_cible_cac 		= $tab["nom_perso"];
																			$pv_cible_cac		= $tab["pv_perso"];
																			$pv_max_cible_cac	= $tab["pvMax_perso"];
																			$bonus_cible_cac	= $tab["bonus_perso"];
																			$camp_cible_cac		= $tab["clan"];

																			$couleur_clan_cible = couleur_clan($camp_cible_cac);

																			if ($id_arme_cac == 10) {
																				// seringue
																				// On affiche que les persos blessés
																				if ($pv_cible_cac < $pv_max_cible_cac) {
																					echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																				}
																			} else if ($id_arme_cac == 11) {
																				// bandage
																				// On affiche que les persos avec malus
																				if ($bonus_cible_cac < 0) {
																					echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																				}
																			}
																		} else if ($id_cible_cac >= 200000) {

																			// Un PNJ
																			$sql = "SELECT nom_pnj, pv_i, pvMax_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
																			$res = $mysqli->query($sql);
																			$tab = $res->fetch_assoc();

																			$nom_cible_cac 		= $tab["nom_pnj"];
																			$pv_cible_cac		= $tab["pv_i"];
																			$pv_max_cible_cac	= $tab["pvMax_pnj"];

																			if ($pv_cible_cac < $pv_max_cible_cac) {
																				echo "<option style=\"color:grey\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																			}
																		} else {
																			// Un Batiment => on ne veut pas l'afficher !
																		}
																	}
																}
																else {
																	// Impossible d'attaquer au CaC quand on est dans un train
																	if (!in_train($mysqli, $id_perso)) {

																		foreach($targets_cac as $t_cible_portee_cac){

																			$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

																			if ($id_cible_cac < 50000) {

																				// Un autre perso
																				$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
																				$res = $mysqli->query($sql);
																				$tab = $res->fetch_assoc();

																				$nom_cible_cac 	= $tab["nom_perso"];
																				$camp_cible_cac	= $tab["clan"];

																				$couleur_clan_cible = couleur_clan($camp_cible_cac);

																			} else if ($id_cible_cac >= 200000) {

																				// Un PNJ
																				$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
																				$res = $mysqli->query($sql);
																				$tab = $res->fetch_assoc();

																				$nom_cible_cac = $tab["nom_pnj"];

																				$couleur_clan_cible = "grey";

																			} else {

																				// Un Batiment
																				$sql = "SELECT nom_batiment, nom_instance, camp_instance, pv_instance FROM batiment, instance_batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_cac'";
																				$res = $mysqli->query($sql);
																				$tab = $res->fetch_assoc();

																				$nom_cible_cac = $tab["nom_batiment"];
																				if ($tab["nom_instance"] != "") {
																					$nom_cible_cac .= " ".$tab["nom_instance"];
																				}

																				$camp_cible_cac	= $tab["camp_instance"];

																				$couleur_clan_cible = couleur_clan($camp_cible_cac);
																				$pv_instance	= $tab["pv_instance"];
																				if ($pv_instance <= 0)
																					continue;
																			}

																			echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																		}
																	}
																}
																?>
															</select>
														</td>
														</form>
													</tr>
												</tbody>
											</table>
										</div>
										<?php } ?>
										<!-- compétence d'attaque à distance -->
										<?php if($combat_type != 'close' AND $combat_type != 'heal'){ ?>
										<div class='col-12 table-responsive'>
											<table class='table table-sm table-striped shadow-sm'>
												<thead>
													<th scope="col" colspan='2' >Combat à distance</th>
												</thead>
												<tbody>
													<tr>
														<td>Arme</td>
														<td><?= $nom_arme_dist; ?></td>
													</tr>
													<tr>
														<td>Coût en PA</td>
														<td><?=	$coutPa_arme_dist; ?><?php if (possede_lunette_visee($mysqli, $id_perso)) { echo " (+2)"; } ?></td>
													</tr>
													<tr>
														<td>Dégâts</td>
														<td><?= $degats_arme_dist; ?></td>
													</tr>
													<tr>
														<td>Portée</td>
														<td><?= $porteeMax_arme_dist; ?></td>
													</tr>
													<tr>
														<td>Précision</td>
														<td><?= $precision_arme_dist . " %"; ?></td>
													</tr>
													<?php if(!empty($degatZone_arme_dist) OR !empty($building_damage_dist)){?>
													<tr>
														<td class='fw-bold'>Spécial</td>
														<td>
															<?php if(!empty($degatZone_arme_dist)){ echo 'Dégâts de zone<br>';}?>
															<?php if(!empty($building_damage_dist)){ echo 'Bonus de dégâts sur bâtiments';}?>
														</td>
													</tr>
													<?php } ?>
													<tr>
														<form method="post" action="agir.php" target='_main'>
														<td><input type="submit" value="Attaquer"></td>
														<td>
															<select name='id_attaque_dist'>
																<option value="personne">Qui ?</option>
																<?php
																if (!isset($id_bat_perso) || (isset($id_bat_perso) && $id_bat_perso != 10)) {
																	foreach($targets_dist as $t_cible_portee_dist){

																		$id_cible_dist = $t_cible_portee_dist["idPerso_carte"];
																		$id_instance_in_bat = in_bat($mysqli,$id_perso);

																		if ($id_cible_dist != $id_instance_in_bat) {

																			if ($id_cible_dist < 50000) {

																				// Un autre perso
																				$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible_dist'";
																				$res = $mysqli->query($sql);
																				$tab = $res->fetch_assoc();

																				$nom_cible_dist = $tab["nom_perso"];
																				$camp_cible_cac	= $tab["clan"];

																				$couleur_clan_cible = couleur_clan($camp_cible_cac);

																			} else if ($id_cible_dist >= 200000) {

																				// Un PNJ
																				$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_dist'";
																				$res = $mysqli->query($sql);
																				$tab = $res->fetch_assoc();

																				$nom_cible_dist = $tab["nom_pnj"];

																				$couleur_clan_cible = "grey";

																			} else {

																				// Un Batiment
																				$sql = "SELECT nom_batiment, nom_instance, camp_instance, pv_instance FROM batiment, instance_batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_dist'";
																				$res = $mysqli->query($sql);
																				$tab = $res->fetch_assoc();

																				$nom_cible_dist = $tab["nom_batiment"];
																				if ($tab["nom_instance"] != "") {
																					$nom_cible_dist .= " ".$tab["nom_instance"];
																				}

																				$camp_cible_dist	= $tab["camp_instance"];

																				$couleur_clan_cible = couleur_clan($camp_cible_dist);
																				$pv_instance	= $tab["pv_instance"];
																				if ($pv_instance <= 0)
																					continue;
																			}

																			echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_dist.",".$id_arme_dist."'>".$nom_cible_dist." (mat. ".$id_cible_dist.")</option>";
																		}
																	}
																}
																?>
															</select>
														</td>
														</form>
													</tr>
												</tbody>
											</table>
										</div>
										<?php } ?>
										<!-- compétence de soins 2 --> 
										<?php if($combat_type == 'heal'){ ?>
										<div class='col-12 table-responsive'>
											<table class='table table-sm table-striped shadow'>
												<thead>
													<th scope="col" colspan='2'>Soins 2</th>
												</thead>
												<tbody>
													<tr>
														<td>Arme</td>
														<td><?= $nom_arme_cac2; ?></td>
													</tr>
													<tr>
														<td>Coût en PA</td>
														<td><?=	$coutPa_arme_cac2; ?></td>
													</tr>
													<tr>
														<td>Dégâts</td>
														<td><?= $degats_arme_cac2; ?></td>
													</tr>
													<tr>
														<td>Portée</td>
														<td><?= $porteeMax_arme_cac2; ?></td>
													</tr>
													<tr>
														<td>Précision</td>
														<td><?= $precision_arme_cac2 . " %"; ?></td>
													</tr>
													<?php if(!empty($degatZone_arme_cac2) OR !empty($building_damage_cac2)){?>
													<tr>
														<td class='fw-bold'>Spécial</td>
														<td>
															<?php if(!empty($degatZone_arme_cac2)){ echo 'Soins de zone<br>';}?>
															<?php if(!empty($building_damage_cac2)){ echo 'Bonus de dégâts sur bâtiments';}?>
														</td>
													</tr>
													<?php } ?>
													<tr>
														<form method="post" action="agir.php" target='_main'>
														<td><input type="submit" value="Soigner"></td>
														<td>
															<select name='id_attaque_cac2'>
																<option value="personne">Qui ?</option>
																<?php 
																foreach($targets_cac as $t_cible_portee_cac){
																	$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

																	if ($id_cible_cac < 50000) {

																		// Un autre perso
																		$sql = "SELECT nom_perso, pv_perso, pvMax_perso, bonus_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
																		$res = $mysqli->query($sql);
																		$tab = $res->fetch_assoc();

																		$nom_cible_cac 		= $tab["nom_perso"];
																		$pv_cible_cac		= $tab["pv_perso"];
																		$pv_max_cible_cac	= $tab["pvMax_perso"];
																		$bonus_cible_cac	= $tab["bonus_perso"];
																		$camp_cible_cac		= $tab["clan"];

																		$couleur_clan_cible = couleur_clan($camp_cible_cac);

																		if ($id_arme_cac2 == 10) {
																			// seringue
																			// On affiche que les persos blessés
																			if ($pv_cible_cac < $pv_max_cible_cac) {
																				echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																			}
																		} else if ($id_arme_cac2 == 11) {
																			// bandage
																			// On affiche que les persos avec malus
																			if ($bonus_cible_cac < 0) {
																				echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																			}
																		}
																	} else if ($id_cible_cac >= 200000) {

																		// Un PNJ
																		$sql = "SELECT nom_pnj, pv_i, pvMax_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
																		$res = $mysqli->query($sql);
																		$tab = $res->fetch_assoc();

																		$nom_cible_cac 		= $tab["nom_pnj"];
																		$pv_cible_cac		= $tab["pv_i"];
																		$pv_max_cible_cac	= $tab["pvMax_pnj"];

																		if ($pv_cible_cac < $pv_max_cible_cac) {
																			echo "<option style=\"color:grey\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
																		}
																	}
																}
																?>
															</select>
														</td>
														</form>
													</tr>
												</tbody>
											</table>
										</div>
										<?php } ?>
									</div>
								</div>
							</div>
							<div class='col'>
								<!-- autres actions réduit -->
								<button class="btn btn-primary w-100 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOthersActions" aria-expanded="false" aria-controls="collapseOthersActions">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
									</svg>
									Autres actions
								</button>
								<div class="collapse" id="collapseOthersActions">
									<div class="card card-body bg-main">
										<div class='container-fluid'>
											<h3 class='fs-5'>Actions</h3>
											<form method='post' action='action.php' class='text-center row'>
												<select name='liste_action' class="form-select col">
													<option value="invalide" selected>-- -- - Choisir une action - -- --</option>
													<?php // Action d'entrainement
													if($pa_perso >= 10){ ?>
													<option value="65">Entrainement (10 PA)</option>
													<?php } ?>
													<?php // Action Déposer Objet
													if($pa_perso >= 1){ ?>
														<option value="110">Deposer objet (1 PA)</option>
														<option value="139">Donner objet (1 PA)</option>
													<?php } ?>
													<?php 
													// Actions selon le type d'unité

													// Cavalerie et cavalerie lourde
													if (verif_charge_pm($type_perso, $pm_perso) && !in_train($mysqli, $id_perso) && !in_bat($mysqli, $id_perso)) {
														// Charge = 999
														echo '<option value="999">Charger (tous les PA)</option>';
													}

													$sql = "SELECT action.id_action, nom_action, coutPa_action, reflexive_action
															FROM perso_as_competence, competence_as_action, action
															WHERE id_perso='$id_perso'
															AND perso_as_competence.id_competence=competence_as_action.id_competence
															AND competence_as_action.id_action=action.id_action
															AND passif_action = '0'
															ORDER BY nom_action";
													$res = $mysqli->query($sql);

													while ($t_ac = $res->fetch_assoc()) {

														$id_ac 		= $t_ac["id_action"];
														$cout_PA 	= $t_ac["coutPa_action"];
														$nom_ac 	= $t_ac["nom_action"];
														$ref_ac		= $t_ac["reflexive_action"];

														if ($cout_PA == -1){
															$cout_PA = $paMax_perso;
														}

														if (!in_train($mysqli, $id_perso) && !in_bat($mysqli, $id_perso)) {
															if ($cout_PA <= $pa_perso){
																if ($id_ac == 1 && $pm_perso >= $pmMax_perso) {
																	echo "<option value=\"$id_ac\">".$nom_ac." (Tous les PA/PM)</option>";;
																}
																else if ($id_ac == 147) {
																	echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "PA à 8PA)</option>";;
																}
																else {
																	echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "PA)</option>";;
																}
															}
														}
														else {
															if ($ref_ac) {
																if ($cout_PA <= $pa_perso){
																	if ($id_ac == 1 && $pm_perso >= $pmMax_perso) {
																		echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "pa)</option>";;
																	}
																	else if ($id_ac != 1) {
																		echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "pa)</option>";;
																	}
																}
															}
														}
													}
													?>
													<option value="invalide">-- -- -- -- -- -- -- -- -- -- --</option>
												</select>
												<input class='ms-2 col-2' type='submit' name='action' value='ok' />
											</form>
											<?php if($mess_bat){ ?>
											<div class='row mt-4'>
												<div class='col'><?=$mess_bat?></div>
											</div>
											<?php } ?>
											<div class='row'>
												<?php if (is_objet_a_terre($mysqli, $x_perso, $y_perso)) { ?>
												<p class='text-center'>
													<a href="index.php?ramasser=ok">~~ Ramasser les objets à terre (1 PA) ~~</a><br>
													<a href="index.php?ramasser=voir&x=<?= $x_perso?>&y=<?= $y_perso?>">~~ Voir la liste des objets à terre ~~</a>
												</p>
												<?php } ?>			
												<?php 
												// recuperation des données de la carte
												$sql = "SELECT fond_carte FROM $carte
														WHERE x_carte = $x_perso
														AND y_carte = $y_perso";
												$res = $mysqli->query($sql);
												$tab = $res->fetch_assoc();

												$fond_carte_perso = $tab['fond_carte'];

												afficher_liens_rail_genie($genie_compagnie_perso, $fond_carte_perso);
												?>
												<hr class="">
												<p>
													<a class='fw-semibold' href="nouveau_message.php?visu=ok&camp=<?=$clan_perso?>"><img class='size-11' src='../public/img/icons/plume-full.png' data-bs-toggle='tooltip' data-bs-placement='top' title='Envoyer un message aux persos de son camp dans sa visu' border=0 /> Envoyer un MP à sa visu</a><br>
												</p>
												<p>
													<a class='fw-semibold' href="nouveau_message.php?visu=ok"><img class='size-11' src='../public/img/icons/megaphone.png' data-bs-toggle='tooltip' data-bs-placement='top' title='Envoyer un message à tous les persos dans sa visu' border=0 width='100' height='80' /> Crier très fort</a>
												</p>
												<hr class="">
											</div>
											<?php 
											if($afficher_rosace) : 
											if (in_train($mysqli, $id_perso)) {
												$id_train = in_train($mysqli, $id_perso);
											}
											
											if(in_bat($mysqli, $id_perso)){
												$directionUpLeft = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=1";
												$directionUpCenter = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=2";
												$directionUpRight = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=3";
												$directionCenterLeft = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=4";
												$directionCenterRight = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=5";
												$directionDownLeft = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=6";
												$directionDownCenter = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=7";
												$directionDownRight = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=8";
												$text_move = "Sortir";
											}else if (isset($id_train) && $id_train > 0) {
												$directionUpLeft = "?train=".$id_train."&out=ok&direction=1";
												$directionUpCenter = "?train=".$id_train."&out=ok&direction=2";
												$directionUpRight = "?train=".$id_train."&out=ok&direction=3";
												$directionCenterLeft = "?train=".$id_train."&out=ok&direction=4";
												$directionCenterRight = "?train=".$id_train."&out=ok&direction=5";
												$directionDownLeft = "?train=".$id_train."&out=ok&direction=6";
												$directionDownCenter = "?train=".$id_train."&out=ok&direction=7";
												$directionDownRight = "?train=".$id_train."&out=ok&direction=8";
												$text_move = "Sauter";
											}else {
												$directionUpLeft = "?mouv=1";
												$directionUpCenter = "?mouv=2";
												$directionUpRight = "?mouv=3";
												$directionCenterLeft = "?mouv=4";
												$directionCenterRight = "?mouv=5";
												$directionDownLeft = "?mouv=6";
												$directionDownCenter = "?mouv=7";
												$directionDownRight = "?mouv=8";
												$text_move = "Se déplacer";
											}
											?>
											<div class="moving-arrows text-center">
												<div>
													<a href="<?= $directionUpLeft ?>"><img src="../fond_carte/fleche1.png" alt='flèche haut gauche'></a>
												</div>
												<div>
													<a href="<?= $directionUpCenter?>"><img src="../fond_carte/fleche2.png" alt='flèche haut centre'></a>
												</div>
												<div>
													<a href="<?= $directionUpRight?>"><img src="../fond_carte/fleche3.png" alt='flèche haut droit'></a>
												</div>
												<div>
													<a href="<?= $directionCenterLeft?>"><img src="../fond_carte/fleche4.png"></a>
												</div>
												<div class='g-col-4 m-auto'>
													<h3 class='fs-5 d-inline'><?= $text_move?></h3>
												</div>
												<div>
													<a href="<?= $directionCenterRight?>"><img src="../fond_carte/fleche5.png"></a>
												</div>
												<div>
													<a href="<?= $directionDownLeft ?>"><img src="../fond_carte/fleche6.png"></a>
												</div>
												<div>
													<a href="<?= $directionDownCenter ?>"><img src="../fond_carte/fleche7.png"></a>
												</div>
												<div>
													<a href="<?= $directionDownRight ?>"><img src="../fond_carte/fleche8.png"></a>
												</div>
											</div>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<<div class='col col-md-6 col-lg-9 col-xl-10'>
					<?php if (!empty($itemsOnMap)):?>
					<div class='row bg-body-tertiary bg-main'>
						<div class='col-6 m-auto mt-2'>
							<h3>Liste des objets à terre</h3>
							<table class='table table-striped table-hover'>
								<thead>
									<tr class='text-center'>
										<th scope='col'>Nom objet</th>
										<th scope='col'>Quantité</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach($itemsOnMap as $item):

									$type_objet = $item['type_objet'];
									$id_objet 	= $item['id_objet'];
									$nb_objet	= $item['nb_objet'];

									// Récupération du nom de l'objet
									// Thunes
									if ($type_objet == '1') {
										$nom_objet = "Thune(s)";
									}
									// Objets
									if ($type_objet == '2') {
										$sql_obj = "SELECT nom_objet FROM objet WHERE id_objet='$id_objet'";
										$res_obj = $mysqli->query($sql_obj);
										$t_obj = $res_obj->fetch_assoc();

										$nom_objet = $t_obj['nom_objet'];
									}
									// Armes
									if ($type_objet == '3') {
										$sql_obj = "SELECT nom_arme FROM arme WHERE id_arme='$id_objet'";
										$res_obj = $mysqli->query($sql_obj);
										$t_obj = $res_obj->fetch_assoc();

										$nom_objet = $t_obj['nom_arme'];
									}?>
									<tr class='text-center'>
										<td><?= $nom_objet ?></td>
										<td><?= $nb_objet ?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
					<?php endif;?>
	<!--------------------------------->
	<!-- DEBUT affichage de la carte -->
    <!--------------------------------->
					<div id="wrapper-carte"  style="position: relative; "class='row'>
						<div id="container-carte-meteo" style="position: relative;" class='col-12 col-lg table-responsive p-0'>

                            <div id="weather-overlay" style="position: absolute; z-index: 10; pointer-events: none; display: none;"></div>


                            <?php
							// recuperation des données de la carte
							$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte FROM $carte
									WHERE x_carte >= $x_perso - $perc_carte
									AND x_carte <= $x_perso + $perc_carte
									AND y_carte <= $y_perso + $perc_carte
									AND y_carte >= $y_perso - $perc_carte
									ORDER BY y_carte DESC, x_carte";
							$res = $mysqli->query($sql);
							$tabCarte = $res->fetch_assoc();

							// calcul taille table
							$taille_table = ($perception_perso + $bonusPerception_perso) * 2 + 2;
                            $centerX = 40 + ($perc_carte * 40) + 20;
                            $centerY = 40 + ($perc_carte * 40) + 20;
                            $nb_cases_cote = ($perc_carte * 2) + 1;
                            $taille_table = $nb_cases_cote * 40;
							$class_cadrillage = '';
							if($cadrillage){
								$class_cadrillage = 'table-bordered border-dark';
							}
							?>


							<table id="ma-carte-rpg" class='<?= $class_cadrillage?> text-center' width='<?=$taille_table?>' height='<?=$taille_table?>' cellspacing='0' cellpadding='0' style="margin: 0 auto;">
								<tr class='bg-main'>
									<td width='40' heigth='40' align='center'>y \ x</td>

                                    <?php if (!empty($overlayClass)): ?>

                                    <?php endif; ?>
									<?php
									for ($i = $x_perso - $perc_carte; $i <= $x_perso + $perc_carte; $i++) {
										if ($i == $x_perso){ ?>
											<th style='min-width:40px;' height='40' class="bg-main-var"><?= $i ?></th>
									<?php }else{ ?>
											<th style='min-width:40px;' height='40' background=\"../images/background.jpg\"><?= $i ?></th>
									<?php }
									} ?>
								</tr>
								<?php
								for ($y = $y_perso + $perc_carte; $y >= $y_perso - $perc_carte; $y--) {

									echo "<tr>";

									if ($y == $y_perso) {
										echo "<th style='min-width:40px;' height='40' class='bg-main bg-main-var'>$y</th>";
									}
									else {
										echo "<th style='min-width:40px;' height='40' class='bg-main'>$y</th>";
									}

									for ($x = $x_perso - $perc_carte; $x <= $x_perso + $perc_carte; $x++) {

										//les coordonnées sont dans les limites
										if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) {

											//--------------------------
											//coordonnées du perso
											if ($x == $x_perso && $y == $y_perso){

												// verification s'il y a un objet sur cette case
												$sql_o = "SELECT id_objet FROM objet_in_carte WHERE x_carte='$x' AND y_carte='$y'";
												$res_o = $mysqli->query($sql_o);
												$nb_o = $res_o->num_rows;

												if($clan_perso == '1'){
													$image_profil 	= "Nord.gif";
												}
												if($clan_perso == '2'){
													$image_profil 	= "Sud.gif";
												}

												$fond_im = $tabCarte["fond_carte"];
												$nom_terrain = get_nom_terrain($fond_im);

												echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
												echo "	<div width=40 height=40 style=\"position: relative;\">";
												echo "		<div tabindex='0' style=\"position: absolute;bottom: -2px;text-align: center; width: 100%;font-weight: bold;\"
																	data-bs-toggle='popover'
																	data-bs-trigger='focus'
																	data-bs-html='true'
																	data-bs-placement='bottom' ";

												// TITLE POPOVER
												// 1. REGLE D AFFICHAGE GRADE POPOVER pour les chefs et les grades es grouiltots
                    							$id_check = (int)$id_grade_perso;
                    							$suffixe = "";

                    							// Règle Grouillots (1, 101-107)
                    							if ($id_check === 1 || ($id_check >= 101 && $id_check <= 107)) {
                    								$suffixe = ($clan_perso == 1) ? '_nord' : '_sud';
                    							}
                    							// Règle Pour les chefs (2-18), $suffixe vide "" On garde ainsi la meme regle pour les deux categories Chefs et grouillots

                    							$image_grade_pop = $id_check . $suffixe . ".gif";


                								// TITLE POPOVER
                								// on utilise $image_grade_pop pour l'affichage du grade dans le popover, mais on garde $id_grade_perso pour la logique d'affichage de la pastille de marquage
                								echo "			title=\"<div><img src='../images/".$image_profil."' width='20' height='20'><img alt='".$nom_grade_perso."' title='".$nom_grade_perso."' src='../images/grades/" . $image_grade_pop . "' width='20' height='20'> <a href='evenement.php?infoid=".$id_perso."' target='_blank'>".$nom_perso." [".$id_perso."]</a></div> ";


												afficher_infos_compagnie($nom_compagnie_perso, $image_compagnie_perso);

												if (!in_bat($mysqli,$id_perso)) {

													if (!in_train($mysqli,$id_perso)) {
														afficher_infos_non_bat_non_train($fond_im, $nom_terrain, $nb_o);
													}
													else {
														afficher_infos_in_train($mysqli, $id_perso);
													}
												}
												else {
													afficher_infos_in_bat($mysqli, $id_perso);
												}
												echo "<div><u>Message du jour</u> :<br />".$message_perso."</div>";

												echo "\" ";

												// DATA CONTENT POPOVER
												echo "			data-bs-content=\"";

												afficher_liens_objet($nb_o, $x, $y);
												afficher_liens_rail_genie($genie_compagnie_perso, $fond_im);

												if (in_bat($mysqli,$id_perso)) {

													afficher_liens_in_bat($mysqli, $id_perso);

												}
												else if (prox_bat($mysqli, $x_perso, $y_perso, $id_perso)) {

													afficher_liens_prox_bat($mysqli, $id_perso, $x_perso, $y_perso, $type_perso);

												}
												echo "\" >" ;

												// Affichage pastille de marquage 
												$marquages = marquage_joueur($mysqli, $id_perso);
												
												if($marquages){
													foreach($marquages as $marquage){
														affichage_pastille_marquage($marquage['pastille']);
														echo '<br>';
													}
												}

												echo  $id_perso . "</div>";

												echo "		<img tabindex='0' class=\"\" border=0 src=\"../images_perso/$dossier_img_joueur/$image_perso\" width=40 height=40
																	data-bs-toggle='popover'
																	data-bs-trigger='focus'
																	data-bs-html='true'
																	data-bs-placement='bottom' ";
												// TITLE POPOVER
												echo "			title=\"<div><img src='../images/".$image_profil."' width='20' height='20'><img alt='".$nom_grade_perso."' title='".$nom_grade_perso."' src='../images/grades/" . $id_grade_perso . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_perso."' target='_blank'>".$nom_perso." [".$id_perso."]</a></div>";

												afficher_infos_compagnie($nom_compagnie_perso, $image_compagnie_perso);

												if (!in_bat($mysqli,$id_perso)) {

													if (!in_train($mysqli,$id_perso)) {
														afficher_infos_non_bat_non_train($fond_im, $nom_terrain, $nb_o);
													}
													else {
														afficher_infos_in_train($mysqli, $id_perso);
													}
												}
												else {
													afficher_infos_in_bat($mysqli, $id_perso);
												}
												echo "<div><u>Message du jour</u> :<br />".$message_perso."</div>";

												echo "\" ";
												// DATA CONTENT POPOVER
												echo "			data-bs-content=\"";

												afficher_liens_objet($nb_o, $x, $y);
												afficher_liens_rail_genie($genie_compagnie_perso, $fond_im);

												if (in_bat($mysqli,$id_perso)) {

													afficher_liens_in_bat($mysqli, $id_perso);

												}
												else if (prox_bat($mysqli, $x_perso, $y_perso, $id_perso)) {

													afficher_liens_prox_bat($mysqli, $id_perso, $x_perso, $y_perso, $type_perso);

												}
												echo "\" ";
												echo " />";
												echo "	</div>";
												echo "</td>";
											}
											else {
												if ($tabCarte["occupee_carte"]){

													//------------------------------------
													// Traitement PNJ
													if($tabCarte['idPerso_carte'] >= 200000){

														$idI_pnj = $tabCarte['idPerso_carte'];
														$fond_im = $tabCarte["fond_carte"];

														$nom_terrain = get_nom_terrain($fond_im);

														// recuperation du type de pnj
														$sql_im = "SELECT instance_pnj.id_pnj, nom_pnj FROM instance_pnj, pnj WHERE instance_pnj.id_pnj = pnj.id_pnj AND idInstance_pnj='$idI_pnj'";
														$res_im = $mysqli->query($sql_im);
														$t_im = $res_im->fetch_assoc();

														$id_pnj_im 	= $t_im["id_pnj"];
														$nom_pnj_im	= $t_im["nom_pnj"];

														$im_pnj="pnj".$id_pnj_im."t.png";

														$dossier_pnj = "images/pnj";

														echo "	<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "		<img tabindex='0' border=0 src=\"../".$dossier_pnj."/".$im_pnj."\" width=40 height=40
																			data-bs-toggle='popover'
																			data-bs-trigger='focus'
																			data-bs-html='true'
																			data-bs-placement='bottom'
																			title=\"<div><a href='evenement.php?infoid=".$idI_pnj."' target='_blank'>".$nom_pnj_im." [".$idI_pnj."]</a></div><div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain."</div>\" >";
														echo "	</td>";
													}
													else {
                                                    //-------------------------
                                                    // Traitement Batiment
                                                    if($tabCarte['idPerso_carte'] >= 50000 && $tabCarte['idPerso_carte'] < 200000){

                                                        $idI_bat = $tabCarte['idPerso_carte'];

                                                        // Récupération du type de bat et du camp
                                                        $sql_im = "SELECT instance_batiment.id_batiment, camp_instance, nom_instance, nom_batiment
                                                                   FROM instance_batiment, batiment
                                                                   WHERE instance_batiment.id_batiment = batiment.id_batiment
                                                                   AND id_instanceBat='$idI_bat'";
                                                        $res_im = $mysqli->query($sql_im);
                                                        $t_im = $res_im->fetch_assoc();

                                                        $type_bat   = $t_im["id_batiment"];
                                                        $camp_bat   = $t_im["camp_instance"];
                                                        $nom_i_bat  = $t_im["nom_instance"];
                                                        $nom_bat    = $t_im["nom_batiment"];

                                                        switch($camp_bat){
                                                            case '1':
                                                                $camp_bat2    = 'bleu';
                                                                $image_profil = "Nord.gif";
                                                                $img_folder   = 'nord';
                                                                break;
                                                            case '2':
                                                                $camp_bat2    = 'rouge';
                                                                $image_profil = "Sud.gif";
                                                                $img_folder   = 'sud';
                                                                break;
                                                            default:
                                                                $camp_bat2    = 'neutre';
                                                                $image_profil = "neutre.gif";
                                                                $img_folder   = 'neutre';
                                                        }

                                                        $blason = "mini_blason_".$camp_bat2.".gif";

                                                            echo "<td width=40 height=40 background=\"../fond_carte/".$tabCarte["fond_carte"]."\">";
															// --- CORRECTION NOUVEAUX BATIMENTS (6, 15, 16, 17) ---
                                                                $image_a_tracer = $tabCarte["image_carte"];
                                                                // Correction spécifique pour l'Entrepôt (ID 6)
                                                                if ($type_bat == 6) {
                                                                    if (strpos($image_a_tracer, 'Entrepot_') === false) {
                                                                        $image_a_tracer = str_replace('Entrepot', 'Entrepot_', $image_a_tracer);
                                                                    }
                                                                }
                                                                // Calcul du chemin de l'image selon le type de bâtiment
                                                                if ($type_bat == 6 || $type_bat == 15 || $type_bat == 16 || $type_bat == 17 || strpos($image_a_tracer, 'public/') === 0) {
                                                                    if (strpos($image_a_tracer, 'public/') === false) {
                                                                        $src_finale = "../public/img/buildings/" . $img_folder . "/" . $image_a_tracer;
                                                                    } else {
                                                                        $src_finale = "../" . $image_a_tracer;
                                                                    }
                                                                } else {
                                                                    // Comportement original inchangé pour tous les autres bâtiments
                                                                    $src_finale = "../public/img/buildings/" . $img_folder . "/" . $image_a_tracer;
                                                                }
                                                                echo "	<img tabindex='0' border=0 src=\"" . $src_finale . "\" width=40 height=40
																		data-bs-toggle='popover'
																		data-bs-trigger='focus'
																		data-bs-html='true'
																		data-bs-placement='bottom' ";
															echo "		title=\"<div><img src='../images/".$image_profil."' width='20' height='20'> <a href='evenement.php?infoid=".$idI_bat."' target='_blank'>".$nom_bat." ".$nom_i_bat." [".$idI_bat."]</a></div>\"";
															echo "		data-bs-content=\"";
															if (in_bat($mysqli,$id_perso)) {

																$id_instance_in_bat = in_bat($mysqli,$id_perso);

																if ($idI_bat == $id_instance_in_bat) {

																	echo "<div><a href='batiment.php?bat=".$id_instance_in_bat."' target='_blank'>Accéder à la page du bâtiment</a></div> ";
																	echo "<div><a href='action.php?bat=".$idI_bat."&reparer=ok'>Réparer ce bâtiment (5PA)</a></div> ";
																}
															}
															else if(prox_instance_bat($mysqli, $x_perso, $y_perso, $idI_bat) && $type_bat != 12) {

																echo "<div><a href='action.php?bat=".$idI_bat."&reparer=ok'>Réparer ce bâtiment (5PA)</a></div> ";

																if (!nation_perso_bat($mysqli, $id_perso, $idI_bat)) {
																	if(batiment_vide($mysqli, $idI_bat) && batiment_pv_capturable($mysqli, $idI_bat)&& $type_bat != 1 && $type_bat != 5 && $type_bat != 7 && $type_bat != 10 && $type_bat != 11 && $type_perso == 3){
																		echo "<div><a href='index.php?bat=".$idI_bat."&bat2=".$type_bat."'>Capturer ce bâtiment</a></div>";
																	}
																}
																else {
																	if($type_bat != 1 && $type_bat != 5 && $type_bat != 10){
																		if (($type_bat == 2 && ($type_perso == 3 || $type_perso == 4 || $type_perso == 6)) || $type_bat != 2 ) {
																			echo "<div><a href='index.php?bat=".$idI_bat."&bat2=".$type_bat."'>Entrer dans ce bâtiment</a></div>";
																		}
																	}
																}
															}
															echo "\">";
															echo "</td>";
														}
														else {
															if($tabCarte['image_carte'] == "murt.png"){
																//positionement du mur
																echo "<td width=40 height=40 background=\"../fond_carte/".$tabCarte["fond_carte"]."\"> <img border=0 src=\"../images_perso/".$tabCarte["image_carte"]."\" width=40 height=40 onMouseOver=\"AffBulle('<img src=../images/murs/mur.jpeg>')\" onMouseOut=\"HideBulle()\" title=\"mur\"></td>";
															}
															else {

																$id_perso_im 	= $tabCarte['idPerso_carte'];
																$fond_im 		= $tabCarte["fond_carte"];

																$nom_terrain 	= get_nom_terrain($fond_im);
																$cout_pm 		= cout_pm($fond_im, $type_perso);

																//recuperation du type de perso (image)
																$sql_perso_im = "SELECT * FROM perso WHERE id_perso='$id_perso_im'";
																$res_perso_im = $mysqli->query($sql_perso_im);
																$t_perso_im = $res_perso_im->fetch_assoc();

																$im_perso 	= $t_perso_im["image_perso"];
																$nom_ennemi = $t_perso_im['nom_perso'];
																$id_ennemi 	= $t_perso_im['id_perso'];
																$clan_e 	= $t_perso_im['clan'];
																$message_e	= $t_perso_im['message_perso'];

																if($clan_e == 1){
																	$clan_ennemi 	= 'rond_b.png';
																	$couleur_clan_e = 'blue';
																	$image_profil 	= "Nord.gif";
																}
																if($clan_e == 2){
																	$clan_ennemi 	= 'rond_r.png';
																	$couleur_clan_e = 'red';
																	$image_profil 	= "Sud.gif";
																}

																// récupération du grade du perso
																$sql_grade = "SELECT perso_as_grade.id_grade, nom_grade FROM perso_as_grade, grades WHERE perso_as_grade.id_grade = grades.id_grade AND id_perso='$id_ennemi'";
																$res_grade = $mysqli->query($sql_grade);
																$t_grade = $res_grade->fetch_assoc();

																$id_grade_ennemi 	= $t_grade["id_grade"];
																$nom_grade_ennemi 	= $t_grade["nom_grade"];

																// cas particuliers grouillot
																if ($id_grade_ennemi == 101) {
																	$id_grade_ennemi = "1.1";
																}
																if ($id_grade_ennemi == 102) {
																	$id_grade_ennemi = "1.2";

																}

																// recuperation de l'id de la compagnie
																$sql_groupe = "SELECT id_compagnie from perso_in_compagnie where id_perso='$id_perso_im' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
																$res_groupe = $mysqli->query($sql_groupe);
																$t_groupe = $res_groupe->fetch_assoc();
																$nb = $res_groupe->num_rows;

																$id_groupe = $nb ? $t_groupe['id_compagnie'] : 0;

																$nom_compagnie = '';

																if($id_groupe){

																	// recuperation des infos sur la compagnie (dont le nom)
																	$sql_groupe2 = "SELECT * FROM compagnies WHERE id_compagnie='$id_groupe'";
																	$res_groupe2 = $mysqli->query($sql_groupe2);
																	$t_groupe2 = $res_groupe2->fetch_assoc();

																	$nom_compagnie 		= addslashes($t_groupe2['nom_compagnie']);
																	$id_compagnie 		= $t_groupe2['id_compagnie'];
																	$image_compagnie	= $t_groupe2['image_compagnie'];

																}

																if(isset($nom_compagnie) && trim($nom_compagnie) != ''){

																	echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																	echo "	<div width=40 height=40 style=\"position: relative;\">";

																	//--- Div matricule perso
																	echo "		<div tabindex='0' data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' style=\"position: absolute;bottom: -2px;text-align: center; width: 100%;font-weight: bold;\" ";
																	// Title popover
echo " title=\"<div style='display: flex; align-items: center; gap: 15px;'>"; // Conteneur Flex

// --- 1. L'AVATAR À GAUCHE ---
if (trim($image_profil) != "") {
    echo "<div style='flex-shrink: 0;'>";
    echo "  <img src='../images/".$image_profil."' width='150' height='150' style='border: 2px solid #b38b6d; border-radius: 5px; object-fit: cover;'>";
    echo "</div>";
}

// --- 2. LES INFOS À DROITE ---
echo "<div style='text-align: left;'>"; // On aligne le texte à gauche à côté de l'image

    // Grade et Nom
    echo "<div>";
    echo "  <img alt='".$nom_grade_ennemi."' title='".$nom_grade_ennemi."' src='../images/grades/" . $id_grade_ennemi . ".gif' width='20' height='20'>";
    echo "  <a href='evenement.php?infoid=".$id_ennemi."' target='_blank' style='font-weight:bold;'> ".$nom_ennemi." [".$id_ennemi."]</a>";
    echo "</div>";

    // Compagnie (Drapeau)
    echo "<div style='margin-top: 5px;'>";
    echo "  <a href='compagnie.php?id_compagnie=".$id_compagnie."&voir_compagnie=ok' target='_blank'>";
    if (trim($image_compagnie) != "" && $image_compagnie != "0") {
        echo "<img src='".$image_compagnie."' width='40' height='40' style='vertical-align: middle;'> ";
    }
    echo stripslashes($nom_compagnie)."</a>";
    echo "</div>";

echo "</div>"; // Fin du bloc de droite
echo "</div>"; // Fin du conteneur Flex
																	if ($nom_terrain == "Pont") {

																		$sql_p = "SELECT id_instanceBat FROM instance_batiment WHERE x_instance='$x' AND y_instance='$y'";
																		$res_p = $mysqli->query($sql_p);
																		$t_p = $res_p->fetch_assoc();

																		$idIBat = $t_p['id_instanceBat'];

																		echo "<div><a href='evenement.php?infoid=".$idIBat."'><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." [".$idIBat."]</a></div>";
																	}
																	else {
																		echo "<div><img src='../fond_carte/".$fond_im."' width='40' height='40'> ".$nom_terrain."</div>";
																	}
																	echo "<div><u>Message du jour</u> :<br />".$message_e."</div>\" ";
																	// data content popover
																	echo "			data-bs-content=\"<div><a href='nouveau_message.php?pseudo=".$nom_ennemi."' target='_blank'>Envoyer un message</a></div>";

																	afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm);

																	echo "			\" >" . $id_ennemi . "</div>";

																	//--- Image perso
																	echo "		<img tabindex='0' border=0 src=\"../images_perso/$dossier_img_joueur/".$tabCarte["image_carte"]."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																	// Title popover
																	echo "			title=\"<div><img src='../images/".$image_profil."' width='40' height='40'><img alt='".$nom_grade_ennemi."' title='".$nom_grade_ennemi."' src='../images/grades/" . $id_grade_ennemi . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_ennemi."' target='_blank'>".$nom_ennemi." [".$id_ennemi."]</a></div><div><a href='compagnie.php?id_compagnie=".$id_compagnie."&voir_compagnie=ok' target='_blank'>";
																	if (trim($image_compagnie) != "" && $image_compagnie != "0") {
																		echo "<img src='".$image_compagnie."' width='40' height='40'>";
																	}
																	echo " ".stripslashes($nom_compagnie)."</a></div>";
																	if ($nom_terrain == "Pont") {

																		$sql_p = "SELECT id_instanceBat FROM instance_batiment WHERE x_instance='$x' AND y_instance='$y'";
																		$res_p = $mysqli->query($sql_p);
																		$t_p = $res_p->fetch_assoc();

																		$idIBat = $t_p['id_instanceBat'];

																		echo "<div><a href='evenement.php?infoid=".$idIBat."'><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." [".$idIBat."]</a></div>";
																	}
																	else {
																		echo "<div><img src='../fond_carte/".$fond_im."' width='40' height='40'> ".$nom_terrain."</div>";
																	}
																	echo "<div><u>Message du jour</u> :<br />".$message_e."</div>\" ";
																	// Data content popover
																	echo "			data-bs-content=\"<div><a href='nouveau_message.php?pseudo=".$nom_ennemi."' target='_blank'>Envoyer un message</a></div>";

																	afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm);

																	echo "			\" />";
																	echo "	</div>";
																	echo "</td>";
																}
																else {
																	echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";

																	//--- Div matricule perso
																	echo "	<div width=40 height=40 style=\"position: relative;\">";
																	echo "		<div tabindex='0' data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' style=\"position: absolute;bottom: -2px;text-align: center; width: 100%;font-weight: bold;\" ";
																	// Title Popover
																	echo "			title=\"<div><img src='../images/".$image_profil."' width='40' height='40'><img alt='".$nom_grade_ennemi."' title='".$nom_grade_ennemi."' src='../images/grades/" . $id_grade_ennemi . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_ennemi."' target='_blank'>".$nom_ennemi." [".$id_ennemi."]</a></div>";
																	echo "<div><img src='../fond_carte/".$fond_im."' width='40' height='40'> ".$nom_terrain."</div>";
																	echo "<div><u>Message du jour</u> :<br />".$message_e."</div>\" ";

																	echo "			data-bs-content=\"<div><a href='nouveau_message.php?pseudo=".$nom_ennemi."' target='_blank'>Envoyer un message</a></div>";

																	afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm);

																	echo "			\" ";
																	echo "		>";
																	// Affichage pastille de marquage
																	$marquages = marquage_joueur($mysqli, $id_ennemi);

																	if($marquages){
																		foreach($marquages as $marquage){
																			affichage_pastille_marquage($marquage['pastille']);
																			echo '<br>';
																		}
																	}
																	echo  $id_ennemi . "</div>";

																	//--- Image perso
																	echo "		<img tabindex='0' border=0 src=\"../images_perso/$dossier_img_joueur/".$tabCarte["image_carte"]."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																	// Title popover
																	echo "			title=\"<div><img src='../images/".$image_profil."' width='40' height='40'><img alt='".$nom_grade_ennemi."' title='".$nom_grade_ennemi."' src='../images/grades/" . $id_grade_ennemi . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_ennemi."' target='_blank'>".$nom_ennemi." [".$id_ennemi."]</a></div><div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain."</div>";
																	echo "<div><u>Message du jour</u> :<br />".$message_e."</div>\" ";
																	echo "			data-bs-content=\"<div><a href='nouveau_message.php?pseudo=".$nom_ennemi."' target='_blank'>Envoyer un message</a></div>";

																	afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm);

																	echo "			\" />";
																	echo "	</div>";

																	echo "</td>";
																}
															}
														}
													}
												}
												else {

													//------------------------------------------------------------
													//  traitement Batiment qui occupe pas une case comme le pont
													if($tabCarte['idPerso_carte'] >= 50000 && $tabCarte['idPerso_carte'] < 200000){

														$idI_bat = $tabCarte['idPerso_carte'];

														// recuperation du type de bat et du camp
														$sql_im = "SELECT instance_batiment.id_batiment, camp_instance, nom_instance, nom_batiment
																	FROM instance_batiment, batiment
																	WHERE instance_batiment.id_batiment = batiment.id_batiment
																	AND id_instanceBat='$idI_bat'";
														$res_im = $mysqli->query($sql_im);
														$t_im = $res_im->fetch_assoc();

														$type_bat 	= $t_im["id_batiment"];
														$camp_bat 	= $t_im["camp_instance"];
														$nom_i_bat	= $t_im["nom_instance"];
														$nom_bat	= $t_im["nom_batiment"];

														$fond_carte = $tabCarte["fond_carte"];

														$cout_pm = cout_pm($fond_carte, $type_perso);

														afficher_popover_pont($x, $x_perso, $y, $y_perso, $fond_carte, $idI_bat, $nom_bat, $cout_pm, $type_perso);
													}
													else {

														$fond_im 			= $tabCarte["fond_carte"];

														$nom_terrain 		= get_nom_terrain($fond_im);
														$cout_pm_terrain 	= cout_pm($fond_im, $type_perso);

														// verification s'il y a un objet sur cette case
														$sql_o = "SELECT type_objet, id_objet FROM objet_in_carte WHERE x_carte='$x' AND y_carte='$y' ORDER BY id_objet DESC";
														$res_o = $mysqli->query($sql_o);
														$nb_o = $res_o->num_rows;

														if($nb_o){
															$t = $res_o->fetch_assoc();
															$type_objet = $t['type_objet'];
															$objet = $t['id_objet'];

															if($type_objet == 2 && $objet == '8'){
																$image_objet = 'etendard_nord.png';
															} else if($type_objet == 2 && $objet == '9'){
																$image_objet = 'etendard_sud.png';
															} else {
																$image_objet = 'o1.gif';
															}
														} else {
															$image_objet = '';
														}

														$sql_case = "SELECT valid_case FROM joueur WHERE id_joueur='$id_joueur_perso'";
														$res_case = $mysqli->query($sql_case);
														$t = $res_case->fetch_assoc();
														$valid_case = $t['valid_case'];

														if (in_bat($mysqli, $id_perso)) {

															$taille_case = ceil($taille_bat_perso / 2);

															afficher_popover_in_bat($x, $x_perso, $y, $y_perso, $taille_case, $fond_im, $nb_o, $nom_terrain, $id_bat_perso, $image_objet);
														}
														else {

															if($y > $y_perso+1 || $y < $y_perso-1 || $x > $x_perso+1 || $x < $x_perso-1) {
																if($nb_o){
																	echo "<td width=40 height=40 background=\"../fond_carte/".$tabCarte["fond_carte"]."\">";
																	echo "	<img border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='tooltip' data-placement='top' title='objets à ramasser'/>";
																	echo "</td>";
																}
																else {
																	echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></td>";
																}
															}
															else {
																if($y == $y_perso+1 && $x == $x_perso+1){
																	if($nb_o){
																		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-bs-content=\"<div><a href='index.php?mouv=3'>Se déplacer</a></div><div><a href='index.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
																		echo "</td>";
																	}
																	else {
																		echo "<td width=40 height=40>";
																		if ($valid_case || is_case_rail($fond_im)) {
																			echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																			echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
																			echo "			data-bs-content=\"<div><a href='index.php?mouv=3'>Se déplacer</a></div>\" >";
																		}
																		else {
																			echo "	<a href=\"index.php?mouv=3\">";
																			echo "		<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40>";
																			echo "	</a>";
																		}
																		echo "</td>";
																	}
																}
																if($y == $y_perso-1 && $x == $x_perso+1){
																	if($nb_o){
																		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-bs-content=\"<div><a href='index.php?mouv=8'>Se déplacer</a></div><div><a href='index.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
																		echo "</td>";
																	}
																	else {
																		echo "<td width=40 height=40>";
																		if ($valid_case || is_case_rail($fond_im)) {
																			echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																			echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
																			echo "			data-bs-content=\"<div><a href='index.php?mouv=8'>Se déplacer</a></div>\" >";
																		}
																		else {
																			echo "	<a href=\"index.php?mouv=8\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
																		}
																		echo "</td>";
																	}
																}
																if($y == $y_perso && $x == $x_perso+1){
																	if($nb_o){
																		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-bs-content=\"<div><a href='index.php?mouv=5'>Se déplacer</a></div><div><a href='index.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
																		echo "</td>";
																	}
																	else {
																		echo "<td width=40 height=40>";
																		if ($valid_case || is_case_rail($fond_im)) {
																			echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																			echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
																			echo "			data-bs-content=\"<div><a href='index.php?mouv=5'>Se déplacer</a></div>\" >";
																		}
																		else {
																			echo "<a href=\"index.php?mouv=5\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
																		}
																		echo "</td>";
																	}
																}
																if($y == $y_perso && $x == $x_perso-1) {
																	if($nb_o){
																		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-bs-content=\"<div><a href='index.php?mouv=4'>Se déplacer</a></div><div><a href='index.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
																		echo "</td>";
																	}
																	else {
																		echo "<td width=40 height=40>";
																		if ($valid_case || is_case_rail($fond_im)) {
																			echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																			echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
																			echo "			data-bs-content=\"<div><a href='index.php?mouv=4'>Se déplacer</a></div>\" >";
																		}
																		else {
																			echo "<a href=\"index.php?mouv=4\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
																		}
																		echo "</td>";
																	}
																}
																if($y == $y_perso+1 && $x == $x_perso-1) {
																	if($nb_o){
																		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-bs-content=\"<div><a href='index.php?mouv=1'>Se déplacer</a></div><div><a href='index.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
																		echo "</td>";
																	}
																	else {
																		echo "<td width=40 height=40>";
																		if ($valid_case || is_case_rail($fond_im)) {
																			echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																			echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
																			echo "			data-bs-content=\"<div><a href='index.php?mouv=1'>Se déplacer</a></div>\" >";
																		}
																		else {
																			echo "<a href=\"index.php?mouv=1\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
																		}
																		echo "</td>";
																	}
																}
																if($y == $y_perso-1 && $x == $x_perso-1) {
																	if($nb_o){
																		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-bs-content=\"<div><a href='index.php?mouv=6'>Se déplacer</a></div><div><a href='index.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
																		echo "</td>";
																	}
																	else {
																		echo "<td width=40 height=40>";
																		if ($valid_case || is_case_rail($fond_im)) {
																			echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																			echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
																			echo "			data-bs-content=\"<div><a href='index.php?mouv=6'>Se déplacer</a></div>\" >";
																		}
																		else {
																			echo "<a href=\"index.php?mouv=6\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
																		}
																		echo "</td>";
																	}
																}
																if($y == $y_perso+1 && $x == $x_perso) {
																	if($nb_o){
																		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-bs-content=\"<div><a href='index.php?mouv=2'>Se déplacer</a></div><div><a href='index.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
																		echo "</td>";
																	}
																	else {
																		echo "<td width=40 height=40>";
																		if ($valid_case || is_case_rail($fond_im)) {
																			echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																			echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
																			echo "			data-bs-content=\"<div><a href='index.php?mouv=2'>Se déplacer</a></div>\" >";
																		}
																		else {
																			echo "<a href=\"index.php?mouv=2\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
																		}
																		echo "</td>";
																	}
																}
																if($y == $y_perso-1 && $x == $x_perso) {
																	if($nb_o){
																		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
																		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-bs-content=\"<div><a href='index.php?mouv=7'>Se déplacer</a></div><div><a href='index.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
																		echo "</td>";
																	}
																	else {
																		echo "<td width=40 height=40>";
																		if ($valid_case || is_case_rail($fond_im)) {
																			echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-bs-toggle='popover' data-bs-trigger='focus' data-bs-html='true' data-bs-placement='bottom' ";
																			echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
																			echo "			data-bs-content=\"<div><a href='index.php?mouv=7'>Se déplacer</a></div>\" >";
																		}
																		else {
																			echo "<a href=\"index.php?mouv=7\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
																		}
																		echo "</td>";
																	}
																}
															}
														}
													}
												}
											}
											$tabCarte = $res->fetch_assoc();
										}
										else //les coordonnées sont hors limites
											echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
									}
									echo "</tr>";
								} ?>
							</table>
						</div>
						<div class='col-12 col-lg-3 d-none d-md-block bg-main p-4'>
							<div class='container-fluid'>
								<h3 class='fs-5'>Actions</h3>
								<form method='post' action='action.php' class='text-center row'>
									<select name='liste_action' class="form-select col">
										<option value="invalide" selected>-- -- - Choisir une action - -- --</option>
										<?php // Action d'entrainement
										if($pa_perso >= 10){ ?>
										<option value="65">Entrainement (10 PA)</option>
										<?php } ?>
										<?php // Action Déposer Objet
										if($pa_perso >= 1){ ?>
											<option value="110">Deposer objet (1 PA)</option>
											<option value="139">Donner objet (1 PA)</option>
										<?php } ?>
										<?php 
										// Actions selon le type d'unité

										// Cavalerie et cavalerie lourde
										if (verif_charge_pm($type_perso, $pm_perso) && !in_train($mysqli, $id_perso) && !in_bat($mysqli, $id_perso)) {
											// Charge = 999
											echo '<option value="999">Charger (tous les PA)</option>';
										}

										$sql = "SELECT action.id_action, nom_action, coutPa_action, reflexive_action
												FROM perso_as_competence, competence_as_action, action
												WHERE id_perso='$id_perso'
												AND perso_as_competence.id_competence=competence_as_action.id_competence
												AND competence_as_action.id_action=action.id_action
												AND passif_action = '0'
												ORDER BY nom_action";
										$res = $mysqli->query($sql);

										while ($t_ac = $res->fetch_assoc()) {

											$id_ac 		= $t_ac["id_action"];
											$cout_PA 	= $t_ac["coutPa_action"];
											$nom_ac 	= $t_ac["nom_action"];
											$ref_ac		= $t_ac["reflexive_action"];

											if ($cout_PA == -1){
												$cout_PA = $paMax_perso;
											}

											if (!in_train($mysqli, $id_perso) && !in_bat($mysqli, $id_perso)) {
												if ($cout_PA <= $pa_perso){
													if ($id_ac == 1 && $pm_perso >= $pmMax_perso) {
														echo "<option value=\"$id_ac\">".$nom_ac." (Tous les PA/PM)</option>";;
													}
													else if ($id_ac == 147) {
														echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "PA à 8PA)</option>";;
													}
													else {
														echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "PA)</option>";;
													}
												}
											}
											else {
												if ($ref_ac) {
													if ($cout_PA <= $pa_perso){
														if ($id_ac == 1 && $pm_perso >= $pmMax_perso) {
															echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "pa)</option>";;
														}
														else if ($id_ac != 1) {
															echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "pa)</option>";;
														}
													}
												}
											}
										}
										?>
										<option value="invalide">-- -- -- -- -- -- -- -- -- -- --</option>
									</select>
									<input class='ms-2 col-2' type='submit' name='action' value='ok' />
								</form>
								<?php if($mess_bat){ ?>
								<div class='row mt-4'>
									<div class='col'><?=$mess_bat?></div>
								</div>
								<?php } ?>
								<div class='row'>
									<?php if (is_objet_a_terre($mysqli, $x_perso, $y_perso)) { ?>
									<p class='text-center'>
										<a href="index.php?ramasser=ok">~~ Ramasser les objets à terre (1 PA) ~~</a><br>
										<a href="index.php?ramasser=voir&x=<?=$x_perso?>&y=<?=$y_perso?>">~~ Voir la liste des objets à terre ~~</a>
									</p>
									<?php } ?>			
									<?php 
									// recuperation des données de la carte
									$sql = "SELECT fond_carte FROM $carte
											WHERE x_carte = $x_perso
											AND y_carte = $y_perso";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();

									$fond_carte_perso = $tab['fond_carte'];

									afficher_liens_rail_genie($genie_compagnie_perso, $fond_carte_perso);
									?>
									<hr class="">
									<p>
										<a class='fw-semibold' href="nouveau_message.php?visu=ok&camp=<?=$clan_perso?>"><img class='size-11' src='../public/img/icons/plume-full.png' data-bs-toggle='tooltip' data-bs-placement='top' title='Envoyer un message aux persos de son camp dans sa visu' border=0 /> Envoyer un MP à sa visu</a><br>
									</p>
									<p>
										<a class='fw-semibold' href="nouveau_message.php?visu=ok"><img class='size-11' src='../public/img/icons/megaphone.png' data-bs-toggle='tooltip' data-bs-placement='top' title='Envoyer un message à tous les persos dans sa visu' border=0 width='100' height='80' /> Crier très fort</a>
									</p>
									<hr class="">
								</div>
								<?php 
								if($afficher_rosace) : 
								if (in_train($mysqli, $id_perso)) {
									$id_train = in_train($mysqli, $id_perso);
								}
								if(in_bat($mysqli, $id_perso)){
									$directionUpLeft = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=1";
									$directionUpCenter = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=2";
									$directionUpRight = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=3";
									$directionCenterLeft = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=4";
									$directionCenterRight = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=5";
									$directionDownLeft = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=6";
									$directionDownCenter = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=7";
									$directionDownRight = "?bat=".$id_bat."&bat2=".$bat."&out=ok&direction=8";
									$text_move = "Sortir";
								}else if (isset($id_train) && $id_train > 0) {
									$directionUpLeft = "?train=".$id_train."&out=ok&direction=1";
									$directionUpCenter = "?train=".$id_train."&out=ok&direction=2";
									$directionUpRight = "?train=".$id_train."&out=ok&direction=3";
									$directionCenterLeft = "?train=".$id_train."&out=ok&direction=4";
									$directionCenterRight = "?train=".$id_train."&out=ok&direction=5";
									$directionDownLeft = "?train=".$id_train."&out=ok&direction=6";
									$directionDownCenter = "?train=".$id_train."&out=ok&direction=7";
									$directionDownRight = "?train=".$id_train."&out=ok&direction=8";
									$text_move = "Sauter";
								}else {
									$directionUpLeft = "?mouv=1";
									$directionUpCenter = "?mouv=2";
									$directionUpRight = "?mouv=3";
									$directionCenterLeft = "?mouv=4";
									$directionCenterRight = "?mouv=5";
									$directionDownLeft = "?mouv=6";
									$directionDownCenter = "?mouv=7";
									$directionDownRight = "?mouv=8";
									$text_move = "Se déplacer";
								}
								?>
								<div class="moving-arrows text-center">
									<div>
										<a href="<?= $directionUpLeft ?>"><img src="../fond_carte/fleche1.png" alt='flèche haut gauche'></a>
									</div>
									<div>
										<a href="<?= $directionUpCenter?>"><img src="../fond_carte/fleche2.png" alt='flèche haut centre'></a>
									</div>
									<div>
										<a href="<?= $directionUpRight?>"><img src="../fond_carte/fleche3.png" alt='flèche haut droit'></a>
									</div>
									<div>
										<a href="<?= $directionCenterLeft?>"><img src="../fond_carte/fleche4.png"></a>
									</div>
									<div class='g-col-4 m-auto'>
										<h3 class='fs-5 d-inline'><?= $text_move?></h3>
									</div>
									<div>
										<a href="<?= $directionCenterRight?>"><img src="../fond_carte/fleche5.png"></a>
									</div>
									<div>
										<a href="<?= $directionDownLeft ?>"><img src="../fond_carte/fleche6.png"></a>
									</div>
									<div>
										<a href="<?= $directionDownCenter ?>"><img src="../fond_carte/fleche7.png"></a>
									</div>
									<div>
										<a href="<?= $directionDownRight ?>"><img src="../fond_carte/fleche8.png"></a>
									</div>
								</div>
								<?php endif; ?>
							</div>
						</div>
				</div>
			</div>
		</main>
		<footer class='container-fluid fixed-bottom p-0 d-none'>
			<div class='row justify-content-center bg-main bg-main-var p-3 text-center'>
				<div class='col-3 col-md-1 fw-semibold cat-title py-2'>
					<a href="#" class='py-3 text-light'>
						<img class="img-fluid size-8 me-2" src="../public/img/icons/battle.png" alt="attaque">
						Combat
					</a>
				</div>
				<div class='col-3 col-md-1 fw-semibold cat-title mx-4 py-2'>
					<a href="#" class='py-3 text-light'>
						<img class="img-fluid size-8 me-2" src="../public/img/icons/move.png" alt="déplacement">
						Se déplacer
					</a>
				</div>
				<div class='col-3 col-md-1 fw-semibold cat-title py-2'>
					<a href="#" class='text-light'>
						<img class="img-fluid size-8 me-2" src="../public/img/icons/flash.png" alt="actions">
						Actions
					</a>
				</div>
			</div>
		</footer>
	<?php
            }
    	}
    }
	else {
		header("Location:../index.php");
	}
	?>
	<script>
/**
 * GESTION DYNAMIQUE DE LA MÉTÉO ET DU CYCLE JOUR/NUIT
 */
function rafraichirTerrain() {
    console.log("Rafraîchissement des effets climatiques...");

    Promise.all([
        fetch('get_cycle_etat.php').then(res => res.json()),
        fetch('meteo.php').then(res => res.json())
    ])
    .then(([dataCycle, dataMeteo]) => {
        // --- 1. CYCLE & DATE ---
        const spanDate = document.getElementById('date-historique');
        if (spanDate && dataCycle.date_jeu) spanDate.innerText = dataCycle.date_jeu;

        const texteCycle = document.getElementById('cycle-texte');
        const estLaNuit = dataCycle.is_night === true || dataCycle.is_night === "true" || dataCycle.is_night === 1;

        if (texteCycle) {
            texteCycle.innerText = (estLaNuit ? "Nuit" : "Jour");
            texteCycle.style.color = (estLaNuit ? "#1e90ff" : "#ff8c00");
        }

        // --- 2. FILTRE NUIT SUR LES TUILES & ICÔNE ---
        const iconeCycle = document.getElementById('cycle-icone');
        if (iconeCycle) {
            iconeCycle.src = estLaNuit ? "../fond_carte/nuit.gif" : "../fond_carte/jour.gif";
        }

        document.querySelectorAll('img[src*="fond_carte/"]').forEach(img => {
            if (img.id === 'cycle-icone') return;
            let src = img.src;
            if (estLaNuit) {
                if (!src.includes('_nuit.gif')) img.src = src.replace('.gif', '_nuit.gif');
            } else {
                if (src.includes('_nuit.gif')) img.src = src.replace('_nuit.gif', '.gif');
            }
        });

        // --- 3. OVERLAY MÉTÉO ---
        const overlay = document.getElementById('weather-overlay');
        const tableCarte = document.getElementById('ma-carte-rpg');

        if (overlay && tableCarte) {
            // 1. Reset visuel complet
            overlay.style.display = 'none';
            overlay.style.background = 'none';
            overlay.style.webkitMaskImage = 'none';
            overlay.style.maskImage = 'none';
            overlay.style.opacity = "1";
            overlay.style.filter = "none";
            overlay.classList.remove('effet-orage-actif');

            // 2. RECALCUL DE LA POSITION (résolution/zoom)
            overlay.style.position = 'absolute';
            overlay.style.top = tableCarte.offsetTop + "px";
            overlay.style.left = tableCarte.offsetLeft + "px";
            overlay.style.width = tableCarte.offsetWidth + "px";
            overlay.style.height = tableCarte.offsetHeight + "px";

            // 3. Calcul des centres relatif à la table
            const largeurTable = tableCarte.offsetWidth;
            const hauteurTable = tableCarte.offsetHeight;

            const centerX = ((largeurTable - 40) / 2) + 40;
            const centerY = ((hauteurTable - 40) / 2) + 40;

            if (dataMeteo.effet && dataMeteo.effet !== "soleil") {
                const effet = dataMeteo.effet.toLowerCase().trim();
                overlay.style.display = 'block';

                const diffX = (dataMeteo.x_center - dataMeteo.joueur_x) * 40;
                const diffY = (dataMeteo.joueur_y - dataMeteo.y_center) * 40;

                const posX = centerX + diffX;
                const posY = centerY + diffY;
                const rayonPx = dataMeteo.rayon * 40;

                // --- LOGIQUE DES EFFETS ---
                if (effet === "brouillard") {
                    overlay.style.background = `radial-gradient(
                        circle at ${posX}px ${posY}px,
                        rgba(200, 200, 200, 0.9) 0,
                        rgba(200, 200, 200, 0.9) ${rayonPx}px,
                        rgba(200, 200, 200, 0.4) ${rayonPx + 40}px
                    )`;
                    const masque = `radial-gradient(circle at ${centerX}px ${centerY}px, transparent 0, transparent 80px, black 160px)`;
                    overlay.style.webkitMaskImage = masque;
                    overlay.style.maskImage = masque;
                    overlay.style.zIndex = "100";

                } else if (effet === "pluie" || effet === "orage") {
                    overlay.style.background = "url('../fond_carte/rain.gif') repeat";
                    overlay.style.opacity = "0.5";
                    const masqueLocal = `radial-gradient(circle at ${posX}px ${posY}px, black 0, black ${rayonPx}px, transparent ${rayonPx + 5}px)`;
                    overlay.style.webkitMaskImage = masqueLocal;
                    overlay.style.maskImage = masqueLocal;
                    if (effet === "orage") overlay.classList.add('effet-orage-actif');

                } else if (effet === "tempete") {
                    overlay.style.background = "rgba(194, 178, 128, 0.4)";
                    overlay.style.filter = "sepia(0.5) saturate(2) contrast(0.8)";
                    const masqueSable = `radial-gradient(circle at ${posX}px ${posY}px, black 0, black ${rayonPx}px, transparent ${rayonPx + 20}px)`;
                    overlay.style.webkitMaskImage = masqueSable;
                    overlay.style.maskImage = masqueSable;
                }
            }
        }
    })
    .catch(err => console.error("Erreur rafraîchissement terrain:", err));
}

// --- AJUSTEMENT INSTANTANÉ AU REDIMENSIONNEMENT ---
window.addEventListener('resize', () => {
    const overlay = document.getElementById('weather-overlay');
    const tableCarte = document.getElementById('ma-carte-rpg');
    if (overlay && tableCarte) {
        overlay.style.top = tableCarte.offsetTop + "px";
        overlay.style.left = tableCarte.offsetLeft + "px";
        overlay.style.width = tableCarte.offsetWidth + "px";
        overlay.style.height = tableCarte.offsetHeight + "px";
    }
});

document.addEventListener("DOMContentLoaded", () => {
    rafraichirTerrain();
    setInterval(rafraichirTerrain, 60000);
});
</script>
	</body>
</html>
<?php


}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session

	header("Location:../index.php");
}
?>