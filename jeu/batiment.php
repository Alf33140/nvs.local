<?php
@session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_action.php");
require_once("../mvc/model/Weapon.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){

	if(isset($_SESSION["id_perso"])){

		$id_perso = $_SESSION['id_perso'];
		$date = time();

		$sql = "SELECT pv_perso, pa_perso, type_perso, or_perso, UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele, clan FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql );
		$tpv = $res->fetch_assoc();

		$testpv 	= $tpv['pv_perso'];
		$type_perso	= $tpv['type_perso'];
		$or 		= $tpv["or_perso"];
		$dla 		= $tpv["DLA"];
		$est_gele 	= $tpv["est_gele"];
		$camp		= $tpv['clan'];
		$pa_perso	= $tpv['pa_perso'];

		$config = '1';

		// Récupération du role du joueur
		$sql = "SELECT * FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$j = $res->fetch_assoc();

		$id_joueur = $j["idJoueur_perso"];

		$sql = "SELECT * FROM joueur WHERE id_joueur='$id_joueur'";
		$res = $mysqli->query($sql );
		$j2 = $res->fetch_assoc();

		$isAdmin = $j2['admin_perso'];
		$isAnim = $j2['animateur'];

		// verification si le perso est encore en vie
		if ($testpv <= 0) {
			// le perso est mort
			header("Location:../tour.php");
		}
		else {

			// le perso est vivant
			if(isset($_GET['bat'])){

				// Recuperation de l'id du batiment
				$id_i_bat = $_GET['bat'];

				// On verifie que c'est bien une valeur numerique
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_i_bat");

				if($verif){

					//verification que le perso est bien dans le batiment
					if (in_instance_bat($mysqli, $id_perso, $id_i_bat)){

						// recupération du type de batiment
						$sql = "SELECT id_batiment, camp_instance, pv_instance, pvMax_instance, nom_instance, x_instance, y_instance
								FROM instance_batiment
								WHERE id_instanceBat='$id_i_bat'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();

						$id_bat 	= $t["id_batiment"];
						$camp_bat 	= $t["camp_instance"];
						$pv_bat 	= $t["pv_instance"];
						$pvMax_bat 	= $t["pvMax_instance"];
						$nom_i_bat 	= $t["nom_instance"];
						$x_i_bat	= $t["x_instance"];
						$y_i_bat	= $t["y_instance"];

						$pourcentage_rabais = 0;

						// rabais marchandage pourcentage
						//$nb_points_marchandage = est_marchand($mysqli, $id_perso);

						//if($nb_points_marchandage){
						  //	if($nb_points_marchandage == 1){
							//	$pourcentage_rabais = 2;
							//}
						//	if($nb_points_marchandage == 2){
						  //		$pourcentage_rabais = 4;
						//	}
						  //	if($nb_points_marchandage == 3){
							//	$pourcentage_rabais = 5;
						//}
					//}

						//recup des infos du batiment
						$sql_i = "SELECT nom_batiment, description FROM batiment WHERE id_batiment='$id_bat'";
						$res_i = $mysqli->query($sql_i);
						$t_i = $res_i->fetch_assoc();

						$nom_bat = $t_i["nom_batiment"];
						$description_bat = $t_i["description"];

						if($camp_bat == '1'){
							$camp_bat2 = 'bleu';
						}
						if($camp_bat == '2'){
							$camp_bat2 = 'rouge';
						}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Nord VS Sud</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=0.3, shrink-to-fit=no">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <style>
            .alert-img { max-width: 300px; border-radius: 8px; margin-bottom: 15px; border: 2px solid rgba(255,255,255,0.5); }
            .alert { margin: 20px auto; max-width: 80%; }
        </style>
    </head>

    <body>
        <div class="container mt-3">
    <!-- BLOC SUCCÈS -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
            <div class="text-center">

                <img src="../public/img/items/valider.png" alt="Succès" style="max-width:200px;" class="mb-2">
                <h4>Récolte réussie !</h4>
                <p>Vos hommes ramènent <b><?php echo intval($_GET['success']); ?></b> caisses au camp.</p>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- BLOC ERREUR -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow" role="alert">
            <div class="text-center">
                <img src="../public/img/items/erreur.png" alt="Erreur" style="max-width:200px;" class="mb-2">
                <h4>Convoi bloqué !</h4>
                <p>
                    <?php
                        if($_GET['error'] == 'pa') echo "Pas assez de Points d'Action.";
                        else if($_GET['error'] == 'stock') echo "Stock épuisé.";
                        else echo "Une erreur est survenue.";
                    ?>
                </p>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
</div>

            <?php if (isset($nom_bat)): ?>
                <h2><?php echo $nom_bat." ".$nom_i_bat; ?></h2>
            <?php endif; ?>

            <div class="btn-group mb-3">
                <input type="button" class="btn btn-secondary" value="Fermer cette fenêtre" onclick="window.close()">
                <input type="button" class="btn btn-primary" onclick="window.open('evenement.php?infoid=<?php echo $id_i_bat; ?>');" value="Voir les évènements" />
            </div>

<?php
						if ($type_perso != 6) {

							// ==========================================================
                            // 1. GESTION DU SURPLUS (ACHAT / VENTE)
                            // ==========================================================

                            // --- ACHAT D'OBJET AU SURPLUS ---
                            if(isset($_POST['achat_objet'])){
                                $id_objet_a = $_POST['achat_objet'];

                                // On récupère les infos de l'objet
                                $sql = "SELECT nom_objet, cout_objet, poids_objet FROM objet WHERE id_objet='$id_objet_a'";
                                $res = $mysqli->query($sql);
                                $t_o = $res->fetch_assoc();

                                $nom_o = $t_o['nom_objet'];
                                $cout_o = $t_o['cout_objet'];
                                $poids_o = $t_o['poids_objet'];

                                // Calcul du rabais (marchandage)
                                // On suppose que $recup_m contient les infos du perso (compétence marchandage)
                                if(isset($recup_m['marchandage_perso'])){
                                    $cout_o = $cout_o - ($cout_o * $recup_m['marchandage_perso'] / 100);
                                }

                                if($or_perso >= $cout_o){
                                    // Ajout à l'inventaire
                                    $sql = "INSERT INTO perso_as_objet (id_perso, id_objet) VALUES ('$id_perso','$id_objet_a')";
                                    $mysqli->query($sql);

                                    // Débit de l'or et MAJ du poids
                                    $sql = "UPDATE perso SET or_perso = or_perso - $cout_o, charge_perso = charge_perso + $poids_o WHERE id_perso='$id_perso'";
                                    $mysqli->query($sql);

                                    echo "<center><font color='blue'>Vous avez acheté 1 $nom_o pour $cout_o or.</font></center>";
                                } else {
                                    echo "<center><font color='red'>Vous n'avez pas assez d'or !</font></center>";
                                }
                            }

                           // --- VENTE D'OBJET AU SURPLUS (LOGIQUE MÉTIER) ---
                            if (isset($_GET['sell_objet_instance'])) {
                                $id_p_obj = (int)$_GET['sell_objet_instance'];

                                // 1. On vérifie que le perso possède bien CETTE instance d'objet et qu'il n'est pas équipé
                                // On fait une jointure pour récupérer les infos de l'objet (nom, prix, poids) en une seule fois
                                $sql = "SELECT pao.id_p_obj, o.nom_objet, o.coutOr_objet, o.poids_objet
                                        FROM perso_as_objet pao
                                        JOIN objet o ON pao.id_objet = o.id_objet
                                        WHERE pao.id_p_obj = '$id_p_obj'
                                        AND pao.id_perso = '$id_perso'
                                        AND pao.equip_objet = '0'
                                        LIMIT 1";

                                $res = $mysqli->query($sql);

                                if ($res && $res->num_rows > 0) {
                                    $t_o = $res->fetch_assoc();

                                    // 2. Calcul du prix de revente et récupération du poids
                                    $prix_vente = floor($t_o['coutOr_objet'] / 2);
                                    $poids_o = $t_o['poids_objet'];
                                    $nom_o = $t_o['nom_objet'];

                                    // 3. Suppression de l'instance précise dans l'inventaire
                                    $mysqli->query("DELETE FROM perso_as_objet WHERE id_p_obj = '$id_p_obj' AND id_perso = '$id_perso'");

                                    // 4. Crédit de l'or et mise à jour de la charge du personnage
                                    $mysqli->query("UPDATE perso SET
                                                    or_perso = or_perso + $prix_vente,
                                                    charge_perso = charge_perso - $poids_o
                                                    WHERE id_perso = '$id_perso'");

                                    // 5. Redirection avec un message de succès pour éviter le "F5" (renvoi de formulaire)
                                    header("Location: batiment.php?bat=$id_i_bat&view_sac=1&msg_vente=".urlencode("Vous avez vendu $nom_o pour $prix_vente or."));
                                    exit;
                                }
                            }

                            // ==========================================================
                            // 2. GESTION DES STOCKS ENTREPOT (OR, BOIS, FER)
                            // ==========================================================

                            // --- DEPOSER DES RESSOURCES (IDs 11, 12, 13) ---
                            if(isset($_POST['depot_ressources'])){
                                $id_objet = $_POST["hid_depot_ressources"];
                                $nb_objet = $_POST["select_ressources"];

                                if($id_bat == '6'){
                                    $nb_res = preg_match("#^[0-9]*[0-9]$#", $nb_objet);

                                    $sql_c = "SELECT count(id_objet) as nb_obj_perso FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet'";
                                    $res_c = $mysqli->query($sql_c);
                                    $t_c = $res_c->fetch_assoc();

                                    if($nb_res && $t_c['nb_obj_perso'] >= $nb_objet && $nb_objet > 0){
                                        // Supprimer de l'inventaire
                                        $mysqli->query("DELETE FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet' LIMIT $nb_objet");

                                        // Calcul poids
                                        $res_p = $mysqli->query("SELECT nom_objet, poids_objet FROM objet WHERE id_objet='$id_objet'");
                                        $t_p = $res_p->fetch_assoc();
                                        $poids_total = $t_p['poids_objet'] * $nb_objet;

                                        // MAJ Perso
                                        $mysqli->query("UPDATE perso SET charge_perso = charge_perso - $poids_total WHERE id_perso='$id_perso'");

                                        // MAJ Stock Instance
                                        $colonne = "";
                                        if($id_objet == 11) $colonne = "stock_or";
                                        if($id_objet == 12) $colonne = "stock_bois";
                                        if($id_objet == 13) $colonne = "stock_fer";

                                        if($colonne != ""){
                                            $mysqli->query("UPDATE instance_batiment SET $colonne = $colonne + $nb_objet WHERE id_instance_bat = '$id_i_bat'");
                                            echo "<center><font color='blue'>Dépôt de $nb_objet ".$t_p['nom_objet']."(s) effectué.</font></center>";
                                        }
                                    }
                                }
                            }

                            // --- RECUPERER DES RESSOURCES (IDs 11, 12, 13) ---
                            if(isset($_POST['recup_ressources'])){
                                $id_objet = $_POST["hid_recup_ressources"];
                                $nb_objet = $_POST["select_recup_ressources"];

                                if($id_bat == '6'){
                                    $nb_res = preg_match("#^[0-9]*[0-9]$#", $nb_objet);
                                    $colonne = "";
                                    if($id_objet == 11) $colonne = "stock_or";
                                    if($id_objet == 12) $colonne = "stock_bois";
                                    if($id_objet == 13) $colonne = "stock_fer";

                                    if($colonne != "" && $nb_res && $nb_objet > 0){
                                        $res_s = $mysqli->query("SELECT $colonne FROM instance_batiment WHERE id_instance_bat = '$id_i_bat'");
                                        $t_s = $res_s->fetch_assoc();

                                        if($t_s[$colonne] >= $nb_objet){
                                            // MAJ Stock Instance
                                            $mysqli->query("UPDATE instance_batiment SET $colonne = $colonne - $nb_objet WHERE id_instance_bat = '$id_i_bat'");

                                            // Ajouter à l'inventaire
                                            for($i=0; $i<$nb_objet; $i++){
                                                $mysqli->query("INSERT INTO perso_as_objet (id_perso, id_objet) VALUES ('$id_perso','$id_objet')");
                                            }

                                            // MAJ Poids
                                            $res_o = $mysqli->query("SELECT nom_objet, poids_objet FROM objet WHERE id_objet='$id_objet'");
                                            $t_o = $res_o->fetch_assoc();
                                            $poids_t = $t_o['poids_objet'] * $nb_objet;
                                            $mysqli->query("UPDATE perso SET charge_perso = charge_perso + $poids_t WHERE id_perso='$id_perso'");

                                            echo "<center><font color='blue'>Vous avez récupéré $nb_objet ".$t_o['nom_objet']."(s).</font></center>";
                                        }
                                    }
                                }
                            }

							// traitement des formulaires prèsent uniquement en gare
							if($id_bat == '11'){

								// Achat d'un ticket de train
								if (isset($_POST["acheter_ticket"]) && isset($_POST["ticket_hidden"]) && trim($_POST["ticket_hidden"]) != "") {

									$tab_ticket_dest 	= explode(',', $_POST["ticket_hidden"]);
									$nb_ticket			= count($tab_ticket_dest);

									if ($nb_ticket > 1) {

										$thune_necessaire = 3 * $nb_ticket;

										if ($or >= $thune_necessaire) {

											for ($i = 0; $i < $nb_ticket; $i++) {

												$ticket_dest = $tab_ticket_dest[$i];

												$sql_dest = "SELECT nom_instance FROM instance_batiment WHERE id_instanceBat='$ticket_dest'";
												$res_dest = $mysqli->query($sql_dest);
												$t_dest = $res_dest->fetch_assoc();

												$nom_destination = "Gare " . $t_dest['nom_instance'] . "[" . $ticket_dest . "]";

												// MAJ thune perso
												$sql = "UPDATE perso SET or_perso=or_perso-3 WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												// Ajout de l'objet ticket de train dans l'inventaire du perso
												$sql = "INSERT INTO perso_as_objet (id_perso, id_objet, capacite_objet) VALUES ('$id_perso', '1', '$ticket_dest')";
												$mysqli->query($sql);

												// Maj thune pour affichage
												$or = $or - 3;

												echo "<center><font color='blue'>Vous avez acheté un ticket de train en destination de $nom_destination</font></center>";
											}
										}
										else {
											echo "<center><font color='red'>Vous n'avez pas suffisamment de thunes pour vous acheter tous les tickets de train</font></center>";
										}
									}
									else {

										$ticket_dest = $tab_ticket_dest[0];

										$sql_dest = "SELECT nom_instance FROM instance_batiment WHERE id_instanceBat='$ticket_dest'";
										$res_dest = $mysqli->query($sql_dest);
										$t_dest = $res_dest->fetch_assoc();

										$nom_destination = "Gare " . $t_dest['nom_instance'] . "[" . $ticket_dest . "]";

										// On vérifie que le perso possède bien 3 thunes
										if ($or >= 3) {

											// On vérifie si le perso n'a pas déjà un ticket pour la même destination
											$sql = "SELECT count(*) as nb_ticket FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='1' AND capacite_objet='$ticket_dest'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();

											$possede_deja_ticket = $t['nb_ticket'];

											if ($possede_deja_ticket == 0) {

												// MAJ thune perso
												$sql = "UPDATE perso SET or_perso=or_perso-3 WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												// Ajout de l'objet ticket de train dans l'inventaire du perso
												$sql = "INSERT INTO perso_as_objet (id_perso, id_objet, capacite_objet) VALUES ('$id_perso', '1', '$ticket_dest')";
												$mysqli->query($sql);

												// Maj thune pour affichage
												$or = $or - 3;

												echo "<center><font color='blue'>Vous avez acheté un ticket de train en destination de $nom_destination</font></center>";
											}
											else {
												echo "<center><font color='red'>Vous possédez déjà un ticket de train pour cette destination</font></center>";
											}
										}
										else {
											echo "<center><font color='red'>Vous n'avez pas suffisamment de thunes pour vous acheter un ticket de train</font></center>";
										}
									}
								}
							}

							echo "<br /><div align=\"center\">Vous possédez <b>$or</b> thune(s)</div><br />";

							/////////////////
							// On veut vendre
							// Possible seulement dans : fort, fortins, hopitaux,
							if($id_bat == '7' || $id_bat == '8' || $id_bat == '9'){

								if(isset($_GET['vente']) && $_GET['vente'] == 'ok'){

									echo "<center><a class='btn btn-primary' href=\"batiment.php?bat=$id_i_bat\">Fermer la partie sur la vente de vos biens</a></center><br />";

									
									if($id_bat == '7'){
										// On ne peut vendre que des objets de type soins dans un hopital
										// Récupération des objets de soin (S ou SP ou SSP) que posséde le perso
										$sql_ressources = "SELECT DISTINCT objet.id_objet FROM perso_as_objet, objet WHERE id_perso='$id_perso'
												AND perso_as_objet.id_objet=objet.id_objet
												AND (type_objet='S' OR type_objet='SSP' OR type_objet='SP')";
									}
									if($id_bat == '8' || $id_bat == '9'){
										// On ne peut vendre que des objets de type autre que ressources et soins dans un fort / fortin
										// Récupération des objets non S et non M que posséde le perso
										$sql_ressources = "SELECT DISTINCT objet.id_objet FROM perso_as_objet, objet WHERE id_perso='$id_perso'
												AND perso_as_objet.id_objet=objet.id_objet
												AND type_objet!='M' AND type_objet!='MSP'
												AND type_objet!='S' AND type_objet!='SP' AND type_objet!='SSP'
												AND objet.id_objet != '1'
												AND perso_as_objet.equip_objet = '0'
                        AND objet.echangeable = 1";
									}

									$res_resources = $mysqli->query($sql_ressources);

									echo "<table border=1 align=center width='70%'>";
									echo "<tr><th colspan='5'>Vos ressources</th></tr>";
									echo "<tr><th>Objet</th><th>Poids</th><th>Quantité possédée</th><th>Prix de vente (unité)</th><th>Vente</th></tr>";

									while($t = $res_resources->fetch_assoc()){

										echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

										$id_objet = $t['id_objet'];

										// recupération du nombre d'objets de ce type que posséde le perso
										$sql_nb = "SELECT COUNT(id_objet) as nb_obj FROM perso_as_objet WHERE id_objet='$id_objet' AND id_perso='$id_perso'";
										$res_nb = $mysqli->query($sql_nb);
										$t_nb = $res_nb->fetch_assoc();

										$nb_obj = $t_nb['nb_obj'];

										// Récupération des informations sur l'objet
										$sql_o = "SELECT nom_objet, description_objet, poids_objet, coutOr_objet, image_objet FROM objet WHERE id_objet='$id_objet'";
										$res_o = $mysqli->query($sql_o);
										$t_o = $res_o->fetch_assoc();

										$nom_objet = $t_o["nom_objet"];
										$description_objet = $t_o["description_objet"];
										$poids_objet = $t_o["poids_objet"];
										$coutOr_objet = $t_o["coutOr_objet"];
										$image_objet = $t_o["image_objet"];

										// Calcul du prix de vente
										$prix_vente_max = round ($coutOr_objet / 2);

										echo "<tr><td align='center'><img src='../public/img/items/".$image_objet."' /><br /><b>$nom_objet</b></td><td align='center'>$poids_objet</td><td align='center'>$nb_obj</td><td align='center'>$prix_vente_max</td>";
										echo "<td align=\"center\"><input type='submit' name='vente_objet' value='Vendre' />";
										echo "<input type='hidden' name='hid_vente_objet' value='".$id_objet."' />";
										echo "</td></tr>";

										echo "</form>";
									}
									echo "</table><br />";

									// Armes
									// Récupération des armes que posséde le perso (non porté)
									$sql_armes = "SELECT id_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND est_portee='0'";
									$res_armes = $mysqli->query($sql_armes);

									echo "<table border=1 align=center width='70%'>";
									echo "<tr><th colspan='5'>Vos armes</th></tr>";
									echo "<tr><th>Armes</th><th>Poids</th><th>Prix de vente</th><th>Vente</th></tr>";

									while($t = $res_armes->fetch_assoc()){

										echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

										$id_arme = $t['id_arme'];

										// Récupération des informations sur l'arme
										$sql_a = "SELECT nom_arme, description_arme, poids_arme, coutOr_arme, image_arme FROM arme WHERE id_arme='$id_arme'";
										$res_a = $mysqli->query($sql_a);
										$t_a = $res_a->fetch_assoc();

										$nom_arme = $t_a["nom_arme"];
										$description_arme = $t_a["description_arme"];
										$poids_arme = $t_a["poids_arme"];
										$coutOr_arme = $t_a["coutOr_arme"];
										$image_arme = $t_a["image_arme"];

										// Calcul du prix de vente
										$prix_vente_final = ceil($coutOr_arme / 2);

										echo "<tr><td align='center'><img src='../images/armes/".$image_arme."' /><br /><b>$nom_arme</b></td><td align='center'>$poids_arme</td><td align='center'>$prix_vente_final</td>";
										echo "<td align=\"center\"><input type='submit' name='vente_arme' value='Vendre' />";
										echo "<input type='hidden' name='hid_vente_arme' value='".$id_arme."' />";
										echo "</td></tr>";

										echo "</form>";
									}
									echo "</table><br />";
								}
								else {
									// Vos armes / Ressources à vendre
									echo "<center><a class='btn btn-primary' href=\"batiment.php?bat=$id_i_bat&vente=ok\">Vendre vos biens</a></center>";
								}
							}

                        //////////////////////////////////////////////////////////////////////
                        // --- GESTION DES RESSOURCES (OR, BOIS, FER) ---
                        //////////////////////////////////////////////////////////////////////
                        if ($id_bat == '15' || $id_bat == '16' || $id_bat == '17') {

                            // 1. Récupération des données
                            if (!isset($t_bat) || !isset($t_bat['stock_actuel'])) {
                                $sql_s = "SELECT stock_actuel, dernier_update_stock FROM instance_batiment WHERE id_instanceBat = '$id_i_bat'";
                                $res_s = $mysqli->query($sql_s);
                                $t_s = $res_s->fetch_assoc();
                            } else {
                                $t_s = $t_bat;
                            }

                            // 2. Calcul du stock virtuel basé sur le temps écoulé
                            $date_last_update = strtotime($t_s['dernier_update_stock']);
                            $sec_ecoulees = time() - $date_last_update;

                            $gain = floor($sec_ecoulees / 86400);
                            $stock_virtuel = min(50, $t_s['stock_actuel'] + $gain);
                            $pourcentage = ($stock_virtuel / 50) * 100;

                            // 3. Configuration visuelle ET IDs des objets adaptés
                            // /!\ Vérifie les IDs (10, 11, 12) par rapport à ta table 'objet'
                            if ($id_bat == '15') {
                                $nom_bat_display = "Mine d'Or"; $couleur = "warning"; $type = "or"; $cout_pa = 4; $img = "or.png";
                                $bg_image = "mineOr.png";
                                $id_objet_ressource = 11; // ID de la "Pépite d'or"
                            } elseif ($id_bat == '16') {
                                $nom_bat_display = "Scierie"; $couleur = "success"; $type = "bois"; $cout_pa = 3; $img = "bois.png";
                                $bg_image = "scierie.png";
                                $id_objet_ressource = 12; // ID du "Billot de bois"
                            } else {
                                $nom_bat_display = "Mine de Fer"; $couleur = "secondary"; $type = "fer"; $cout_pa = 4; $img = "fer.png";
                                $bg_image = "mineFer.png";
                                $id_objet_ressource = 13; // ID du "Minerai de fer"
                            }

                            // --- Style CSS ---
                            echo "
                            <style>
                                body {
                                    background: url('../public/img/backgrounds/$bg_image') no-repeat center center fixed;
                                    background-size: cover;
                                    color: white;
                                    min-height: 100vh;
                                }
                                body::before {
                                    content: '';
                                    position: fixed;
                                    top: 0; left: 0; width: 100%; height: 100%;
                                    background: rgba(0, 0, 0, 0.6);
                                    z-index: -1;
                                }
                                .card {
                                    background-color: rgba(33, 37, 41, 0.85) !important;
                                    backdrop-filter: blur(5px);
                                }
                                h1 { text-shadow: 2px 2px 4px #000; }
                            </style>
                            ";

                            // 4. Affichage de l'interface
                            echo "<div class='container text-center' style='margin-top:20px;'>";
                            echo "  <h1><img src='../public/img/items/$img' width='40'> $nom_bat_display</h1>";
                            echo "  <button class='btn btn-outline-light mb-3' onclick='window.close()'>Fermer la fenêtre</button>";

                            echo "  <div class='card text-white border-$couleur mb-3 shadow'>";
                            echo "      <div class='card-header font-weight-bold'>État de la production</div>";
                            echo "      <div class='card-body'>";

                            echo "          <h5>Stock disponible : $stock_virtuel / 50</h5>";
                            echo "          <div class='progress' style='height: 25px; background-color: #444; margin-bottom:20px;'>";
                            echo "              <div class='progress-bar progress-bar-striped progress-bar-animated bg-$couleur'
                                                     role='progressbar' style='width: $pourcentage%;'></div>";
                            echo "          </div>";

                            if ($stock_virtuel > 0) {
                                // FORMULAIRE DE RÉCOLTE
                                echo "      <form method='POST' action='recolte_ressource.php'>";
                                echo "          <div class='form-row justify-content-center align-items-center'>";
                                echo "              <div class='col-auto'><label>Quantité : </label></div>";
                                echo "              <div class='col-auto'>";
                                // Utilisation d'un champ nombre plus moderne que le select
                                echo "                  <input type='number' name='quantite_recolte' class='form-control mb-2'
                                                         value='1' min='1' max='$stock_virtuel' style='width:80px;'>";
                                echo "              </div>";
                                echo "              <div class='col-auto'>";
                                echo "                  <input type='hidden' name='id_i_bat' value='$id_i_bat'>";
                                echo "                  <input type='hidden' name='id_objet' value='$id_objet_ressource'>"; // ID de l'objet transmis !
                                echo "    <input type='hidden' name='type_recolte' value='$type'>";

                                $dis = (isset($pa_perso) && $pa_perso < $cout_pa) ? "disabled" : "";
                                echo "                  <button type='submit' class='btn btn-$couleur mb-2' $dis>Récolter ($cout_pa PA)</button>";
                                echo "              </div>";
                                echo "          </div>";
                                echo "      </form>";
                            } else {
                                echo "      <div class='alert alert-light text-dark font-weight-bold'>Le stock est vide. Production en cours...</div>";
                            }

                            echo "      </div>";
                            echo "      <div class='card-footer text-muted small bg-dark'>Coût : $cout_pa PA | Production : 1 unité / jour</div>";
                            echo "  </div>";
                            echo "</div>";
                        }
//////////////////////////////////////////////////////////////////////
// entrepot
//////////////////////////////////////////////////////////////////////

if($id_bat == '6') {
                $id_i_bat = (int)$_GET['bat'];

                // --- 1. TRAITEMENT VIDE-SACOCHE ---
                if (isset($_POST['btn_vide_sac']) && isset($_POST['hid_vente_rapide'])) {
                        $id_obj_v = (int)$_POST['hid_vente_rapide'];
                        $verif = $mysqli->query("SELECT id_objet FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_obj_v' LIMIT 1");

                        if ($verif->num_rows > 0) {
                                $res_p = $mysqli->query("SELECT coutOr_objet FROM objet WHERE id_objet = '$id_obj_v'");
                                $t_p = $res_p->fetch_assoc();
                                $gain_v = round($t_p['coutOr_objet'] / 2);

                                $mysqli->query("DELETE FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_obj_v' LIMIT 1");
                                $mysqli->query("UPDATE perso SET or_perso = or_perso + $gain_v WHERE id_perso = '$id_perso'");

                                echo '<script>window.location.replace("batiment.php?bat='.$id_i_bat.'&vente_rapide=success");</script>';
                                exit;
                        }
                }

                // 2. INITIALISATION / RÉCUPÉRATION DES STOCKS
                $res_check = $mysqli->query("SELECT * FROM ressources_entrepot WHERE id_instance_bat = '$id_i_bat'");
                if ($res_check->num_rows == 0) {
                                $mysqli->query("INSERT INTO ressources_entrepot (id_instance_bat) VALUES ('$id_i_bat')");
                                $res_check = $mysqli->query("SELECT * FROM ressources_entrepot WHERE id_instance_bat = '$id_i_bat'");
                }
                $stocks = $res_check->fetch_assoc();

                // 3. LOGIQUE D'ACHAT SURPLUS
                if (isset($_GET['buy_objet'])) {
                                $id_obj_achat = (int)$_GET['buy_objet'];
                                $res_obj = $mysqli->query("SELECT * FROM objet WHERE id_objet = '$id_obj_achat'");
                                $obj_info = $res_obj->fetch_assoc();

                                if ($obj_info) {
                                                $prix = (int)$obj_info['coutOr_objet'];
                                                $res_p = $mysqli->query("SELECT or_perso FROM perso WHERE id_perso = '$id_perso'");
                                                $p_data = $res_p->fetch_assoc();

                                                if ($p_data['or_perso'] >= $prix && $prix > 0) {
                                                                $mysqli->query("UPDATE perso SET or_perso = or_perso - $prix WHERE id_perso = '$id_perso'");
                                                                $mysqli->query("INSERT INTO perso_as_objet (id_perso, id_objet) VALUES ('$id_perso', '$id_obj_achat')");
                                                                header("Location: batiment.php?bat=$id_i_bat&buy_success=1");
                                                                exit;
                                                } else {
                                                                header("Location: batiment.php?bat=$id_i_bat&error=no_money");
                                                                exit;
                                                }
                                }
                }

                // 4. LOGIQUE DE VENTE RESSOURCES AU CLAN
                if (isset($_POST['submit_depot'])) {
                                $d_or   = max(0, (int)$_POST['depot_or']);
                                $d_bois = max(0, (int)$_POST['depot_bois']);
                                $d_fer  = max(0, (int)$_POST['depot_fer']);
                                $total  = $d_or + $d_bois + $d_fer;

                                if ($total > 0) {
                                                $gain = $total * 10;
                                                if($d_or > 0)   $mysqli->query("DELETE FROM perso_as_objet WHERE id_perso = '$id_perso' AND id_objet = 11 LIMIT $d_or");
                                                if($d_bois > 0) $mysqli->query("DELETE FROM perso_as_objet WHERE id_perso = '$id_perso' AND id_objet = 12 LIMIT $d_bois");
                                                if($d_fer > 0)  $mysqli->query("DELETE FROM perso_as_objet WHERE id_perso = '$id_perso' AND id_objet = 13 LIMIT $d_fer");

                                                $mysqli->query("UPDATE ressources_entrepot SET stock_or = stock_or + $d_or, stock_bois = stock_bois + $d_bois, stock_fer = stock_fer + $d_fer WHERE id_instance_bat = '$id_i_bat'");
                                                $mysqli->query("UPDATE perso SET or_perso = or_perso + $gain WHERE id_perso = '$id_perso'");
                                                header("Location: batiment.php?bat=$id_i_bat&gain=$gain");
                                                exit;
                                }
                }

                // 5. RÉCUPÉRATION SAC
                $res_sac = $mysqli->query("SELECT
                                SUM(CASE WHEN id_objet = 11 THEN 1 ELSE 0 END) as nb_or,
                                SUM(CASE WHEN id_objet = 12 THEN 1 ELSE 0 END) as nb_bois,
                                SUM(CASE WHEN id_objet = 13 THEN 1 ELSE 0 END) as nb_fer
                                FROM perso_as_objet WHERE id_perso = '$id_perso'");
                $s = $res_sac->fetch_assoc();

                echo '<style>
                                body { background-image: url("/public/img/backgrounds/entrepot.png"); background-size: cover; background-attachment: fixed; color: beige; }
                                /* Masquer le bouton de vente générique uniquement pour ce bâtiment */
                                .btn-vendre-biens { display: none !important; }

                                .eb-layout { background: rgba(17, 17, 17, 0.9); padding: 20px; border-radius: 12px; border: 1px solid #444; color: white; backdrop-filter: blur(4px); }
                                .eb-card { background: #222; border: 1px solid #333; border-radius: 10px; overflow: hidden; height: 100%; display: flex; flex-direction: column; }
                                .eb-img-container { width: 100%; height: 200px; background: #111; display: flex; align-items: center; justify-content: center; }
                                .eb-img { max-width: 100%; max-height: 100%; object-fit: contain; }
                                .item-img-box { width: 45px; height: 45px; margin-right: 15px; border: 1px solid #555; background: #000; padding: 2px; border-radius: 4px; display: flex; align-items: center; justify-content: center; }
                                .item-img { max-width: 100%; max-height: 100%; }
                                .desc-objet { font-style: italic; color: #999; font-size: 0.8em; margin-top: 2px; text-align: left; }
                                .res-count { font-weight: bold; font-size: 1.1em; color: #ffc107; }
                                .table-v { color: #eee; font-size: 0.85rem; }
                                .table-v td { vertical-align: middle; border-color: #444; }
                </style>';

                echo '<div class="eb-layout">
                                <div class="row">';

                // --- CARTE 1 : LE SURPLUS (md-6) ---
                echo '<div class="col-md-6 mb-4">
                                <div class="eb-card shadow">
                                        <div class="eb-img-container"><img src="/public/img/backgrounds/surplus.png" class="eb-img"></div>
                                        <div class="eb-content p-3">
                                                <h4 class="text-info text-center mb-3 small font-weight-bold">Armurerie du Surplus</h4>';
                                                $res_types = $mysqli->query("SELECT DISTINCT type_objet FROM objet WHERE type_objet NOT IN ('ressource', 'T') AND coutOr_objet > 0 ORDER BY type_objet ASC");
                                                echo '<div class="accordion" id="accordionShop">';
                                                $idx = 0;
                                                while($t = $res_types->fetch_assoc()) {
                                                        $idx++; $collId = "coll".$idx; $typeRaw = $t['type_objet'];
                                                        switch($typeRaw) {
                                                                case 'E': $label = "EQUIPEMENTS"; $color = "badge-primary"; break;
                                                                case 'N': $label = "CONSOMMABLES"; $color = "badge-warning"; break;
                                                                case 'S': $label = "SOINS"; $color = "badge-success"; break;
                                                                default:  $label = $typeRaw; $color = "badge-info"; break;
                                                        }
                                                        echo '<div class="card bg-dark border-secondary mb-2">
                                                                        <div class="card-header p-1">
                                                                                <button class="btn btn-link btn-block text-left text-decoration-none" type="button" data-toggle="collapse" data-target="#'.$collId.'">
                                                                                        <span class="badge '.$color.' mr-2">'.$typeRaw.'</span>
                                                                                        <span class="text-white small font-weight-bold">'.$label.' :</span>
                                                                                </button>
                                                                        </div>
                                                                        <div id="'.$collId.'" class="collapse '.($idx == 1 ? 'show' : '').'" data-parent="#accordionShop">
                                                                                <div class="card-body p-2 bg-black">';
                                                                                        $res_items = $mysqli->query("SELECT * FROM objet WHERE type_objet = '$typeRaw' AND coutOr_objet > 0");
                                                                                        while($item = $res_items->fetch_assoc()) {
                                                                                                $path_img = "/public/img/items/" . $item['image_objet'];
                                                                                                echo '<div class="d-flex align-items-center p-2 mb-1 border border-secondary" style="background: #1a1a1a;">
                                                                                                                <div class="item-img-box"><img src="'.$path_img.'" class="item-img"></div>
                                                                                                                <div style="flex:1;">
                                                                                                                        <div class="small font-weight-bold text-white">'.$item['nom_objet'].'</div>
                                                                                                                        <div class="text-warning small">'.$item['coutOr_objet'].' or</div>
                                                                                                                </div>
                                                                                                                <a href="batiment.php?bat='.$id_i_bat.'&buy_objet='.$item['id_objet'].'" class="btn btn-sm btn-success">Acheter</a>
                                                                                                            </div>';
                                                                                        }
                                                                echo '  </div>
                                                                        </div>
                                                                    </div>';
                                                }
                echo '      </div>
                                        </div>
                                </div>
                            </div>';

							
                // --- CARTE 2 : RÉSERVE DU CLAN (md-6) ---
                echo '<div class="col-md-6 mb-4">
                                <div class="eb-card shadow">
                                        <div class="eb-img-container"><img src="/public/img/backgrounds/bureauEntrepot.png" class="eb-img"></div>
                                        <div class="eb-content p-3">
                                                <h4 class="text-success text-center mb-3 small font-weight-bold">Réserves du Corps</h4>
                                                <div class="row text-center border-bottom border-secondary pb-3 mb-3">
                                                        <div class="col-4"><img src="/public/img/items/or.png" width="20"><br><span class="res-count">'.$stocks['stock_or'].'</span></div>
                                                        <div class="col-4"><img src="/public/img/items/bois.png" width="20"><br><span class="res-count">'.$stocks['stock_bois'].'</span></div>
                                                        <div class="col-4"><img src="/public/img/items/fer.png" width="20"><br><span class="res-count">'.$stocks['stock_fer'].'</span></div>
                                                </div>';
                                                if(isset($_GET['depot'])) {
                                                        echo '<form method="post" action="batiment.php?bat='.$id_i_bat.'">
                                                                        <div class="small">Or (Sac: '.$s['nb_or'].')</div>
                                                                        <input type="number" name="depot_or" class="form-control form-control-sm bg-dark text-white mb-2" value="0" min="0" max="'.$s['nb_or'].'">
                                                                        <div class="small">Bois (Sac: '.$s['nb_bois'].')</div>
                                                                        <input type="number" name="depot_bois" class="form-control form-control-sm bg-dark text-white mb-2" value="0" min="0" max="'.$s['nb_bois'].'">
                                                                        <div class="small">Fer (Sac: '.$s['nb_fer'].')</div>
                                                                        <input type="number" name="depot_fer" class="form-control form-control-sm bg-dark text-white mb-3" value="0" min="0" max="'.$s['nb_fer'].'">
                                                                        <button type="submit" name="submit_depot" class="btn btn-success btn-sm w-100 font-weight-bold">VENDRE AU CLAN</button>
                                                                        <a href="batiment.php?bat='.$id_i_bat.'" class="d-block text-center mt-2 small text-muted">Annuler</a>
                                                                    </form>';
                                                } else {
                                                        echo '<a href="batiment.php?bat='.$id_i_bat.'&depot=1" class="btn btn-primary btn-sm w-100 py-2">VENDRE MES RESSOURCES</a>';
                                                }
                echo '      </div>
                                </div>
                            </div>';

                // --- CARTE 3 : VIDE-SACOCHE (col-12) ---
                echo '<div class="col-12 mb-4">
                                <div class="eb-card shadow">
                                        <div class="p-3 bg-danger text-white d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0 small font-weight-bold">VIDE-SACOCHE (Vente directe au marchand)</h5>
                                                <span class="badge badge-light text-danger small">Rachat : 50%</span>
                                        </div>
                                        <div class="p-2 bg-dark">
                                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                        <table class="table table-sm table-v mb-0">
                                                                <thead>
                                                                        <tr class="text-muted small">
                                                                                <th>Objet</th>
                                                                                <th>Quantité</th>
                                                                                <th>Prix Unitaire</th>
                                                                                <th class="text-right">Action</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>';
                                                                        $sql_v = "SELECT o.id_objet, o.nom_objet, o.coutOr_objet, COUNT(*) as qte
                                                                                            FROM perso_as_objet pao
                                                                                            JOIN objet o ON pao.id_objet = o.id_objet
                                                                                            WHERE pao.id_perso = '$id_perso' AND o.id_objet NOT IN (11, 12, 13)
                                                                                            GROUP BY o.id_objet";
                                                                        $res_v = $mysqli->query($sql_v);
                                                                        if ($res_v && $res_v->num_rows > 0) {
                                                                                while ($v = $res_v->fetch_assoc()) {
                                                                                        $px = round($v['coutOr_objet'] / 2);
                                                                                        echo '<tr>
                                                                                                        <td><img src="/public/img/items/objet'.$v['id_objet'].'.png" width="20" class="mr-2"> '.$v['nom_objet'].'</td>
                                                                                                        <td>x '.$v['qte'].'</td>
                                                                                                        <td class="text-warning">'.$px.' or</td>
                                                                                                        <td class="text-right">
                                                                                                                <form method="POST" class="m-0">
                                                                                                                        <input type="hidden" name="hid_vente_rapide" value="'.$v['id_objet'].'">
                                                                                                                        <button type="submit" name="btn_vide_sac" class="btn btn-outline-danger btn-sm py-0">Vendre 1 u.</button>
                                                                                                                </form>
                                                                                                        </td>
                                                                                                    </tr>';
                                                                                }
                                                                        } else {
                                                                                echo '<tr><td colspan="4" class="text-center text-muted small py-4">Sacoche vide (objets divers)</td></tr>';
                                                                        }
                                echo '          </tbody>
                                                        </table>
                                                </div>
                                        </div>
                                </div>
                            </div>';

                echo '  </div>
                            </div>';
}
							//////////////
							// hopital
							if($id_bat == '7'){

								echo "<center><font color='red'>Chaque achat coûte 2PA au perso (il vous reste ".$pa_perso." PA)</font></center>";

								// Objets de soin
								echo "<table width=100% border=1>";
								echo "<tr><th colspan='6' style='text-align:center'>Objets de soin</th></tr>";
								echo "<tr bgcolor=\"lightgreen\">";
								echo "<th style='text-align:center'>objet</th>";
								echo "<th style='text-align:center'>image</th>";
								echo "<th style='text-align:center'>description</th>";
								echo "<th style='text-align:center'>poids</th>";
								echo "<th style='text-align:center'>quantité</th>";
								echo "<th style='text-align:center'>coût à l'unité</th>";
								echo "<th style='text-align:center'>achat</th>";
								echo "</tr>";

								// achat potions en tout genre + alcool ^^
								// Objets de type S = Soin; SP et SSP = Soin Spécial
								$sql = "SELECT * FROM objet WHERE type_objet = 'S' OR type_objet = 'SP' OR type_objet = 'SSP' or id_objet='4' ORDER BY coutOr_objet";
								$res = $mysqli->query($sql);
								$nb = $res->num_rows;

								if($nb){
									while ($t = $res->fetch_assoc()) {

										echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

										$id_o 			= $t["id_objet"];
										$nom_o 			= $t["nom_objet"];
										$image_o 		= $t["image_objet"];
										$description_o 	= $t["description_objet"];
										$poid_o 		= $t["poids_objet"];
										$cout_o 		= $t["coutOr_objet"];

										// Calcul du rabais
										$rabais = floor(($cout_o * $pourcentage_rabais)/100);

										echo "<tr>";
										echo "	<td><center>$nom_o</center></td>";
										echo "	<td align='center'><img src=\"../public/img/items/$image_o\" width=\"40\" height=\"40\"></td>";
										echo "	<td><center>$description_o</center></td>";
										echo "	<td><center>$poid_o</center></td>";
										echo "	<td align='center'>";
										echo "		<select name='quantite_objet'>";
										echo "			<option value='1'>1</option>";
										echo "			<option value='2'>2</option>";
										echo "			<option value='3'>3</option>";
										echo "			<option value='4'>4</option>";
										echo "			<option value='5'>5</option>";
										echo "			<option value='6'>6</option>";
										echo "		</select>";
										echo "	</td>";
										echo "	<td>";
										echo "		<center>".$cout_o;
										if($rabais) {
											$new_cout_o = $cout_o - $rabais;
											echo "<font color='blue'> ($new_cout_o)</font>";
										}
										echo "		</center>";
										echo "	</td>";
										echo "	<td align=\"center\"><input type='submit' class='btn btn-primary' name='achat_objet' value='Acheter' ";
										if ($pa_perso < 2) {
											echo "disabled";
										}
										echo " />";
										echo "<input type='hidden' name='hid_achat_objet' value=".$id_o." />";
										echo "	</td>";
										echo "</tr>";

										echo "</form>";
									}
								}
							}

							/////////////////////
							// forts et fortins
							if($id_bat == '8' || $id_bat == '9') {

								// Armes, Armures et Objets
								echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
								echo "Choix :";
								echo "<select name=\"choix\" onchange=\"this.form.submit()\">";
								echo "<OPTION value=objets";
								if (isset($_POST["choix"])){
									if($_POST["choix"] == "objets"){
										echo " selected ";
									}
								}
								echo">objets</option>";
								echo "<OPTION value=armes";
								if (isset($_POST["choix"])){
									if($_POST["choix"] == "armes"){
										echo " selected ";
									}
								}
								echo ">armes</option>";
								echo "</select>";
								echo "<input type=\"submit\" name=\"ch\" value=\"ok\">";
								echo "</form>";

								if (isset($_POST["choix"])){
									$choix = $_POST["choix"];
								} else {
									$choix = "objets";
								}

								echo "<center><font color='red'>Chaque achat coûte 2PA au perso (il vous reste ".$pa_perso." PA)</font></center>";

								// Objets
								if($choix == "objets"){

									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

									echo "<table width=100% border=1>";
									echo "<tr><th colspan=7 style='text-align:center'>Objets</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "	<th style='text-align:center'>Objet</th>";
									echo "	<th style='text-align:center'>Poids</th>";
									echo "	<th style='text-align:center'>Image</th>";
									echo "	<th style='text-align:center'>Description</th>";
									echo "	<th style='text-align:center'>Quantité</th>";
									echo "	<th style='text-align:center'>Coût à l'unité</th>";
									echo "	<th style='text-align:center'>Achat</th>";
									echo "</tr>";

									// possibilité achat objets de base
									// Affichage de l'étendard seulement pour les anims et admins
									if(($isAdmin || $isAnim) && $type_perso == '1'){
										$sql = "SELECT * from objet where (type_objet='N' OR type_objet='E' OR type_objet='RP') AND achetable=1";
										$res = $mysqli->query($sql);
										$nb = $res->num_rows;
									} else {
										$sql = "SELECT * from objet where (type_objet='N' OR type_objet='E') AND achetable=1 AND id_objet!='8' AND id_objet!='9'";
										$res = $mysqli->query($sql);
										$nb = $res->num_rows;
									}

									if($nb){
										while ($t = $res->fetch_assoc()) {

											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

											$id_objet 			= $t["id_objet"];
											$nom_objet 			= $t["nom_objet"];
											$poids_objet 		= $t["poids_objet"];
											$coutOr_objet 		= $t["coutOr_objet"];
											$description_objet 	= $t["description_objet"];

											$image_objet = $t["image_objet"];;

											// rabais
											$rabais = floor(($coutOr_objet * $pourcentage_rabais)/100);

											echo "<tr>";
											echo "	<td align='center'>$nom_objet</td>";
											echo "	<td align='center'>$poids_objet</td>";
											echo "	<td align='center'><img src=\"../public/img/items/$image_objet\" width=\"40\" height=\"40\" ></td>";
											echo "	<td>$description_objet</td>";
											echo "	<td align='center'>";
											echo "		<select name='quantite_objet'>";
											echo "			<option value='1'>1</option>";
											echo "			<option value='2'>2</option>";
											echo "			<option value='3'>3</option>";
											echo "			<option value='4'>4</option>";
											echo "			<option value='5'>5</option>";
											echo "			<option value='6'>6</option>";
											echo "		</select>";
											echo "	</td>";
											echo "	<td align='center'>";
											echo $coutOr_objet;
											if($rabais) {
												$new_coutOr_objet = $coutOr_objet - $rabais;
												echo "<font color='blue'> ($new_coutOr_objet)</font>";
											}
											echo "</td>";
											echo "	<td align='center'><input type='submit' class='btn btn-primary' name='achat_objet' value='Acheter' ";
											if ($pa_perso < 2) {
												echo "disabled";
											}
											echo " />";
											echo "<input type='hidden' name='hid_achat_objet' value=".$id_objet." />";
											echo "	</td>";
											echo "</tr>";

											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='6'><i>Aucun objet disponible pour le moment</i></td></tr>";
									}
								}

								// Armes
								if($choix == "armes") {

									// Armes au CaC
									echo "<table width=100% border=1>";
									echo "<tr><th colspan=10 style='text-align:center'>Armes CàC</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "	<th style='text-align:center'>Arme</th>";
									echo "	<th style='text-align:center'>Image</th>";
									echo "	<th style='text-align:center'>Unité(s)</th>";
									echo "	<th style='text-align:center'>Coût PA</th>";
									echo "	<th style='text-align:center'>Précision</th>";
									echo "	<th style='text-align:center'>Dégats</th>";
									echo "	<th style='text-align:center'>Poids</th>";
									echo "	<th style='text-align:center'>Quantité</th>";
									echo "	<th style='text-align:center'>Coût</th>";
									echo "	<th style='text-align:center'>Achat</th>";
									echo "</tr>";

									// Récupération des données des armes de CàC de niveau égal à 6
									$sql = "SELECT arme.id_arme, nom_arme, coutPa_arme, degatMin_arme, degatMax_arme, valeur_des_arme, precision_arme, poids_arme, coutOr_arme, image_arme
											FROM arme
											WHERE porteeMin_arme = 1
											AND porteeMax_arme = 1
											AND coutOr_arme > 0";
									$res = $mysqli->query($sql);
									$nb = $res->num_rows;

									if($nb){
										while ($t = $res->fetch_assoc()) {

											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

											$id_arme 			= $t["id_arme"];
											$nom_arme 			= $t["nom_arme"];
											$coutPa_arme 		= $t["coutPa_arme"];
											$degatMin_arme 		= $t["degatMin_arme"];
											$degatMax_arme 		= $t["degatMax_arme"];
											$valeur_des_arme 	= $t["valeur_des_arme"];
											$precision_arme		= $t["precision_arme"];
											$coutOr_arme 		= $t["coutOr_arme"];
											$image_arme 		= $t["image_arme"];
											$poids_arme			= $t["poids_arme"];

											// rabais
											$rabais = floor(($coutOr_arme * $pourcentage_rabais)/100);

											if($nom_arme != "poing") {

												echo "<tr>";
												echo "	<td><center>$nom_arme</center></td>";
												echo "	<td align=\"center\"><img src=\"../images/armes/$image_arme\" width=\"40\" height=\"40\"></td>";

												echo "	<td><center>";
												$sql_u = "SELECT nom_unite FROM type_unite, arme_as_type_unite
															WHERE type_unite.id_unite = arme_as_type_unite.id_type_unite
															AND arme_as_type_unite.id_arme = '$id_arme'";
												$res_u = $mysqli->query($sql_u);
												$liste_unite = "";
												while ($t_u = $res_u->fetch_assoc()) {
													$nom_unite = $t_u["nom_unite"];

													if ($liste_unite != "") {
														$liste_unite .= " / ";
													}
													$liste_unite .= $nom_unite;
												}
												echo $liste_unite;
												echo "	</center></td>";

												echo "	<td><center>$coutPa_arme</center></td>";
												echo "	<td><center>".$precision_arme."%</center></td>";
												if($degatMin_arme && $valeur_des_arme){
													echo "	<td><center>" . $degatMin_arme . "D". $valeur_des_arme ."</center></td>";
												}
												else {
													echo "	<td><center> - </center></td>";
												}
												echo "	<td><center>$poids_arme</center></td>";
												echo "	<td align='center'>1</td>";
												echo "	<td>";
												echo "<center>".$coutOr_arme;
												if($rabais) {
													$new_coutOr_arme = $coutOr_arme - $rabais;
													echo "<font color='blue'> ($new_coutOr_arme)</font>";
												}
												echo "</center></td>";

												echo "	<td align=\"center\"><input type='submit' class='btn btn-primary' name='achat_arme' value='Acheter' ";
												if ($pa_perso < 2) {
													echo "disabled";
												}
												echo " />";
												echo "	<input type='hidden' name='hid_achat_arme' value=".$t["id_arme"]." />";
												echo "	</td>";
												echo "</tr>";
											}
											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='7'><i>Aucunes armes au CàC disponibles pour le moment</i></td></tr>";
									}
									echo "</table>";
									echo "<br>";
									echo "<table width=100% border=1>";

									// Armes à Distance
									echo "<tr><th colspan=12 style='text-align:center'>Armes Dist</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "	<th style='text-align:center'>Arme</th>";
									echo "	<th style='text-align:center'>Image</th>";
									echo "	<th style='text-align:center'>Unités</th>";
									echo "	<th style='text-align:center'>Portée</th>";
									echo "	<th style='text-align:center'>Coût PA</th>";
									echo "	<th style='text-align:center'>Précision</th>";
									echo "	<th style='text-align:center'>Dégats</th>";
									echo "	<th style='text-align:center'>Dégats de zone ?</th>";
									echo "	<th style='text-align:center'>Poids</th>";
									echo "	<th style='text-align:center'>Quantité</th>";
									echo "	<th style='text-align:center'>Coût</th>";
									echo "	<th style='text-align:center'>Achat</th>";

									// Récupération des données des armes à distance de qualité égal à 6
									$sql2 = "SELECT id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, degatMin_arme, degatMax_arme, valeur_des_arme, precision_arme, degatZone_arme, poids_arme, coutOr_arme, image_arme
												FROM arme
												WHERE porteeMax_arme > 1
												AND coutOr_arme > 0";
									$res2 = $mysqli->query($sql2);
									$nb2 = $res2->num_rows;

									if($nb2){
										while ($t2 = $res2->fetch_assoc()) {

											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

											$id_arme2 			= $t2["id_arme"];
											$nom_arme2 			= $t2["nom_arme"];
											$porteeMin_arme2 	= $t2["porteeMin_arme"];
											$porteeMax_arme2 	= $t2["porteeMax_arme"];
											$coutPa_arme2 		= $t2["coutPa_arme"];
											$valeur_des_arme2 	= $t2["valeur_des_arme"];
											$precision_arme2 	= $t2["precision_arme"];
											$degatMin_arme2 	= $t2["degatMin_arme"];
											$degatMax_arme2 	= $t2["degatMax_arme"];
											$degatZone_arme2 	= $t2["degatZone_arme"];
											$poids_arme2 		= $t2["poids_arme"];
											$coutOr_arme2 		= $t2["coutOr_arme"];
											$image_arme2 		= $t2["image_arme"];

											// Calcul rabais
											$rabais = floor(($coutOr_arme2 * $pourcentage_rabais)/100);

											echo "<tr>";
											echo "	<td><center>$nom_arme2</center></td>";
											echo "	<td align=\"center\"><img src=\"../images/armes/$image_arme2\" width=\"40\" height=\"40\"></td>";

											echo "	<td><center>";
												$sql_u = "SELECT nom_unite FROM type_unite, arme_as_type_unite
															WHERE type_unite.id_unite = arme_as_type_unite.id_type_unite
															AND arme_as_type_unite.id_arme = '$id_arme2'";
												$res_u = $mysqli->query($sql_u);
												$liste_unite = "";
												while ($t_u = $res_u->fetch_assoc()) {
													$nom_unite = $t_u["nom_unite"];

													if ($liste_unite != "") {
														$liste_unite .= " / ";
													}
													$liste_unite .= $nom_unite;
												}
												echo $liste_unite;
												echo "	</center></td>";

											echo "	<td><center>$porteeMin_arme2 - $porteeMax_arme2</center></td>";
											echo "	<td><center>$coutPa_arme2</center></td>";
											echo "	<td><center>".$precision_arme2."%</center></td>";
											if($degatMin_arme2 && $valeur_des_arme2){
												echo "<td><center>" . $degatMin_arme2 . "D" . $valeur_des_arme2 . "</center></td>";
											}
											else {
												echo "<td><center> - </center></td>";
											}
											echo "<td>";
											if ($degatZone_arme2){
												echo "<center>oui</center></td>";
											}
											else{
												echo "<center>non</center></td>";
											}
											echo "	<td><center>$poids_arme2</center></td>";
											echo "	<td align='center'>1</td>";
											echo "	<td>";
											echo "<center>".$coutOr_arme2;
											if($rabais) {
												$new_coutOr_arme2 = $coutOr_arme2 - $rabais;
												echo "<font color='blue'> ($new_coutOr_arme2)</font>";
											}
											echo "</center></td>";
											echo "<td align=\"center\"><input type='submit' class='btn btn-primary' name='achat_arme' value='Acheter' ";
											if ($pa_perso < 2) {
												echo "disabled";
											}
											echo " />";
											echo "<input type='hidden' name='hid_achat_arme' value=".$t2["id_arme"]." />";
											echo "</td></tr>";

											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='9'><i>Aucunes armes à distance disponibles pour le moment</i></td></tr>";
									}
									echo "</table>";
								}
							}

							//////////////
							// Gare
							if($id_bat == '11') {

								// Plan gare
								if ($camp == 1) {
									$image_plan = "plan_gare_nord.png";
									$image_plan_sans_terrain = "gare_nord.png";
								}

								if ($camp == 2) {
									$image_plan = "plan_gare_sud.png";
									$image_plan_sans_terrain = "gare_sud.png";
								}
								echo "<center>";
								echo "<img src='../images/".$image_plan_sans_terrain."' class=\"img-fluid\" alt='blason gares' width='200' >";

								echo "</center><br />";

								echo "<center>";
								if (isset($_GET['afficher_plan'])) {
									echo "<img src='generer_plan_gare.php' class=\"img-fluid\" alt='plan gares'/><br />";
									echo "<a href='batiment.php?bat=".$id_i_bat."' class='btn btn-info'>Cacher le plan du reseau ferré</a>";
								}
								else {
									echo "<a href='batiment.php?bat=".$id_i_bat."&afficher_plan=ok' class='btn btn-info'>Afficher le plan du reseau ferré</a>";
								}
								echo "</center>";

								echo "<br />";


								echo "<table width='50%' border='1' align='center'>";
								echo "	<tr bgcolor=\"lightgrey\"><th>Destination</th><th>Action</th></tr>";

								// On parcours d'un côté les liaisons via id_gare2
								$liaison_trouve 		= true;
								$base_dest				= $id_i_bat;
								$array_dest				= array($base_dest);
								$array_parcours			= array($base_dest);
								$array_parcours_tmp		= array();
								$array_parcours_value_dest	= array();
								$array_parcours_value_dest_tmp	= array();
								$nb_liaisons			= 1;
								$value_dest 			= "";

								$profondeur = 1;
								$prof_tmp	= 1;

								while (count($array_parcours) > 0) {

									$array_parcours_tmp = array();
									$array_parcours_value_dest_tmp = array();

									$taille_parcours = count($array_parcours);

									for ($i = 0; $i < $taille_parcours; $i++) {

										$base_dest = $array_parcours[$i];

										// Récupération des liaisons depuis cette gare
										$sql = "SELECT * FROM liaisons_gare
												WHERE (id_gare2='$base_dest' AND id_gare1 NOT IN('" . implode( "', '" , $array_dest ) . "'))
													OR (id_gare1='$base_dest' AND id_gare2 NOT IN('" . implode( "', '" , $array_dest ) . "'))";
										$res = $mysqli->query($sql);

										unset($array_parcours[array_search($base_dest, $array_parcours)]);

										while ($t = $res->fetch_assoc()) {

											$id_gare1 = $t['id_gare1'];
											$id_gare2 = $t['id_gare2'];

											if ($id_gare1 == $base_dest) {
												$destination = $id_gare2;
											}
											else {
												$destination = $id_gare1;
											}

											// Récupération infos destination
											$sql_dest = "SELECT nom_instance, camp_instance FROM instance_batiment WHERE id_instanceBat='$destination'";
											$res_dest = $mysqli->query($sql_dest);
											$t_dest = $res_dest->fetch_assoc();

											$camp_dest 	= $t_dest['camp_instance'];
											$nom_dest	= $t_dest['nom_instance'];

											$nom_destination = "Gare " . $nom_dest . "[<a href='evenement.php?infoid=".$destination."' target='_blank'>".$destination."</a>]";

											$cout_thune = $profondeur * 3;

											if ($profondeur == 1) {
												$value_dest = $destination;
											}
											else {
												$value_dest = $array_parcours_value_dest[$i].",".$destination;
											}

											if ($camp_dest == $camp) {

												echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

												// Achat de tickets
												echo "<tr>";
												echo "	<td align='center'>$nom_destination - (tickets : $value_dest)</td>";
												echo "	<td align='center'><input type='hidden' name='ticket_hidden' value='$value_dest'> <input type='submit' class='btn btn-primary' name='acheter_ticket' value='Acheter un ticket (".$cout_thune." thunes)'></td>";
												echo "</tr>";

												echo "</form>";
											}
											else {
												echo "<tr>";
												echo "	<td align='center'>$nom_destination</td>";
												echo "	<td align='center'>Gare aux mains de l'ennemi, impossible d'acheter un ticket</td>";
												echo "</tr>";
											}

											array_push($array_dest, $destination);
											array_push($array_parcours_tmp, $destination);
											array_push($array_parcours_value_dest_tmp, $value_dest);
										}
									}

									$array_parcours = $array_parcours_tmp;
									$array_parcours_value_dest = $array_parcours_value_dest_tmp;

									$profondeur++;
								}

								echo "</table>";

							}
					}
					else {
						echo "<center><font color='red'><b>Vous devez être dans le bâtiment pour accéder à sa page !</b></font></center><br /><br />";
						echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
					}
				}
			}
            else {
				echo "<center><font color='red'><b>Cette page n'existe pas</b></font></center><br /><br />";
				echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
			}
		}
	}
	?>
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

		<script>
		$(function () {
			$('[data-toggle="tooltip"]').tooltip();
			$('[data-toggle="popover"]').popover();
		})
		</script>

	</body>
</html>
<?php
}

else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session

	header("Location:../index2.php");
}
}