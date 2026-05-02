<?php
session_start();
require_once("../fonctions.php");
require_once("f_train.php");
require_once("../mvc/model/Vehicle.php");

$mysqli = db_connexion();

// Récupération de la clef secrete
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='clef_secrete'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$clef_secrete = $t['valeur_config'];

if (isset($_GET['clef']) && $_GET['clef'] == $clef_secrete) {

	// Récupération de tous les trains
	$trains = new Vehicle();
	$trains = $trains->where('id_batiment',12)->leftJoin('liaisons_gare','liaisons_gare.id_train','=','instance_batiment.id_instanceBat')->get();
	
	foreach($trains as $train){
		echo '------------<br>';
		$id_instance_train 	= $train->id_instanceBat;
		$nom_train			= $train->nom_instance;
		$pv_train			= $train->pv_instance;
		$pvMax_train		= $train->pvMax_instance;
		$x_train			= $train->x_instance;
		$y_train			= $train->y_instance;
		$camp_train			= $train->camp_instance;
		$contenance_train	= $train->contenance_instance;
		
		if ($camp_train == 1) {
			// Nord
			$image_train 		= "b12b.gif";
			$couleur_camp_train	= "blue";
		}
		else if ($camp_train == 2) {
			// Sud
			$image_train 		= "b12r.gif";
			$couleur_camp_train	= "red";
		}
		else {
			// Ne devrait pas arriver
			$image_train 		= "b12.gif";
			$couleur_camp_train	= "black";
		}	
		
		// récupération de la direction de ce train
		$gare_arrivee = $train->direction;
		
		// Récupération des coordonnées de la direction

		$targetStation = new Vehicle();
		$targetStation = $targetStation->where('id_instanceBat',$train->direction)->get();

		if(!empty($targetStation)){
			$targetStation = $targetStation[0];
			
			$x_gare_arrivee 	= $targetStation->x_instance;
			$y_gare_arrivee 	= $targetStation->y_instance;
			$pv_gare_arrivee	= $targetStation->pv_instance;
			$pvMax_gare_arrivee	= $targetStation->pvMax_instance;
			$camp_gare_arrivee	= $targetStation->camp_instance;
			
			// Calcul pourcentage pv du batiment 
			$pourc_pv_gare_arrivee = ($pv_gare_arrivee / $pvMax_gare_arrivee) * 100;
			
			echo "--- Déplacement du train ". $id_instance_train ." ($x_train / $y_train) vers la gare ". $gare_arrivee ." ($x_gare_arrivee / $y_gare_arrivee) ---<br>";
			
			// 10 PM
			$dep_restant = 10;

			// Une gare n'est active qu'au dessus de 50% de ses PV
			// Le train circule vers la gare que si la gare d'arrivée est du même camp que le train
			while (!est_arrivee($mysqli, $x_train, $y_train, $gare_arrivee)
				&& $dep_restant > 0 
				&& $pourc_pv_gare_arrivee >= 50 
				&& $camp_gare_arrivee == $camp_train) {
				
				$movingTrain = new Vehicle();
				$current_position = $movingTrain->select('id_instanceBat, x_instance, y_instance')->where('id_instanceBat',$id_instance_train)->get();
				$current_position = $current_position[0];
				
				// on initialise avec la position du train pour exclure la case de rail du train de la recherche
				$tab_dep_train = array();
				array_push($tab_dep_train, $current_position->x_instance.';'.$current_position->y_instance);
				
				$lastMove = new Vehicle();
				$lastMove =$lastMove->getLastPathTile($train->id_instanceBat);

				// On exclue la dernière case dep_train avant sa position actuelle
				if ($lastMove) {
					array_push($tab_dep_train, $lastMove['x_last_dep'].';'.$lastMove['y_last_dep']);
				}
					
				// Récupération des rails autour du train
				$sql_r = "SELECT x_carte, y_carte, fond_carte, CONCAT(x_carte, ';', y_carte) as coordonnees FROM carte 
							WHERE (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif')
							AND x_carte >= $x_train-1 AND x_carte <= $x_train+1
							AND y_carte >= $y_train-1 AND y_carte <= $y_train+1
							HAVING  coordonnees NOT IN ( '" . implode( "', '" , $tab_dep_train ) . "' )";
				
				$res_r = $mysqli->query($sql_r);
				$nb_r = $res_r->num_rows;
				
				if ($nb_r) {
					
					// rail trouvé
					$t_r = $res_r->fetch_assoc();
					$x_r = $t_r['x_carte'];
					$y_r = $t_r['y_carte'];
					echo("------ Prochain rail : $x_r / $y_r<br/>" );
					// Y a t-il un obstacle sur les rails ?
					$sql_c = "SELECT occupee_carte, idPerso_carte FROM carte WHERE x_carte='$x_r' AND y_carte='$y_r'";
					$res_c = $mysqli->query($sql_c);
					$t_c = $res_c->fetch_assoc();
					
					$occupee_carte 	= $t_c['occupee_carte'];
					$idPerso_carte	= $t_c['idPerso_carte'];
					
					if ($occupee_carte && $idPerso_carte >= 50000 && $idPerso_carte < 200000) {
						echo '------ le rail est bloqué par la structure '.$idPerso_carte.'<br>';
						// Compteur blocage
						gestion_blocage_train($mysqli, $id_instance_train, $idPerso_carte, $x_r, $y_r);
						
						// On sort de la boucle et on se déplace pas
						break;
					}
					else {
						
						// On supprime le compteur de blocage s'il existe car le train peut se déplacer
						// Cas où la barricade a été détruite par un joueur
						suppression_compteur_blocage($mysqli, $id_instance_train);

						// Modification coordonnées instance train
						$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
						$mysqli->query($sql_t);
						
						// on enregistre la position actuelle du train en tant que dernière position connue
						$newLastMove = new Vehicle();
						if ($lastMove) {
							$newLastMove = $newLastMove->updateLastPathTile($id_instance_train,$current_position->x_instance,$current_position->y_instance);
						}else{
							$newLastMove = $newLastMove->setLastPathTile($id_instance_train,$current_position->x_instance,$current_position->y_instance);
						}
						
						$x_train = $x_r;
						$y_train = $y_r;
						
						if (deplacement_train($mysqli, $id_instance_train, $x_train, $y_train, $image_train, $nom_train, $couleur_camp_train)) {
							$dep_restant--;
						}
						
						echo "------ Le train a bougé en ". $x_r . "/ ". $y_r . ". PM restant(s) ". $dep_restant.'<br>';
					}
				}
				else {
					// pas de rail trouvé => train bloqué
					echo "------ Aucun rail trouvé. Le train est bloqué <br>";
					$sql_e = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
						VALUES ($id_instance_train,'Train','bloqué',NULL,'','en $x_train/$y_train',NOW(),'0')";
					$mysqli->query($sql_e);
					
					break;
				}
			}
			
			if (est_arrivee($mysqli, $x_train, $y_train, $gare_arrivee)) {
				
				echo '--- train arrivé en gare '.$gare_arrivee.' ---<br>';
				
				// evenement arrivée
				$sql_e = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
						VALUES ($id_instance_train,'Train','est entré en Gare',NULL,'','[$gare_arrivee] en $x_train/$y_train',NOW(),'0')";
				$mysqli->query($sql_e);
				
				// On remet les PV au train 
				$sql_pv = "UPDATE instance_batiment SET pv_instance=pvMax_instance WHERE id_instanceBat='$id_instance_train'";
				$mysqli->query($sql_pv);
				
				dechargement_persos_train($mysqli, $id_instance_train, $gare_arrivee, $x_gare_arrivee, $y_gare_arrivee);
				
				// on met à jour le dernier déplacement
				$newLastMove = new Vehicle();
				if ($lastMove) {
					$newLastMove = $newLastMove->updateLastPathTile($id_instance_train,$x_train,$y_train);
				}else{
					$newLastMove = $newLastMove->setLastPathTile($id_instance_train,$x_train,$y_train);
				}
				// $sql_ld = "INSERT INTO train_last_dep (id_train, x_last_dep, y_last_dep) VALUES ('$id_instance_train', '$x_train', '$y_train')";
				// $mysqli->query($sql_ld);
				
				// On change la destination du train
				$sql_dt = "SELECT id_gare1, id_gare2 FROM liaisons_gare WHERE id_train='$id_instance_train'";
				$res_dt = $mysqli->query($sql_dt);
				$t_dt = $res_dt->fetch_assoc();
				
				$id_gare1 = $t_dt['id_gare1'];
				$id_gare2 = $t_dt['id_gare2'];
				
				if ($gare_arrivee == $id_gare1) {
					$nouvelle_direction = $id_gare2;
				} else {
					$nouvelle_direction = $id_gare1;
				}
				
				$sql_lg = "UPDATE liaisons_gare SET direction='$nouvelle_direction' WHERE id_train='$id_instance_train'";
				$mysqli->query($sql_lg);
				
				echo '--- prochaine gare : '.$nouvelle_direction.' ---<br>';
				
				chargement_persos_train($mysqli, $id_instance_train, $x_train, $y_train, $nouvelle_direction, $gare_arrivee, $camp_train);
			}
		}
		else {
			echo " -> Gare cible inexistante ou détruite !<br />";
		}
	}
}
?>
