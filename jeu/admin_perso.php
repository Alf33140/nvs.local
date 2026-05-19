<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once("f_action.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){

	$id_perso = $_SESSION['id_perso'];

	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);

	if($admin){

		$mess_err 	= "";
		$mess 		= "";

		if (isset($_POST['matricule_pendre_hidden'])) {

			$id_perso_pendre = $_POST['matricule_pendre_hidden'];

			$sql = "UPDATE joueur SET pendu=1 WHERE id_joueur=(SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_pendre')";
			$mysqli->query($sql);

			// Récupération de tous les persos
			$sql = "SELECT id_perso, nom_perso, type_perso, clan FROM perso WHERE perso.idJoueur_perso = (SELECT perso.idJoueur_perso FROM perso WHERE id_perso='$id_perso_pendre')";
			$res = $mysqli->query($sql);

			while ($t = $res->fetch_assoc()) {

				$id_perso_a_pendre 		= $t['id_perso'];
				$nom_perso_a_pendre		= $t['nom_perso'];
				$type_perso_a_pendre	= $t['type_perso'];
				$clan	= $t['clan'];

				$couleur_clan_perso = couleur_clan($clan);

				if ($type_perso_a_pendre == 1) {

					$raison_pendaison = "";

					if (isset($_POST['raison_pendaison'])) {
						$raison_pendaison = addslashes($_POST['raison_pendaison']);
					}

					$sql_log = "INSERT INTO log_pendaison (date_pendaison, id_perso, nom_perso, raison_pendaison) VALUES (NOW(), '$id_perso_a_pendre', '$nom_perso_a_pendre', '$raison_pendaison')";
					$mysqli->query($sql_log);
				}

				$sql_c = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso_a_pendre'";
				$res_c = $mysqli->query($sql_c);
				$t_c = $res_c->fetch_assoc();

				$id_compagnie = $t_c['id_compagnie'];

				if ($id_compagnie != null && $id_compagnie > 0) {

					$sql_b = "SELECT SUM(montant) as thune_en_banque FROM histobanque_compagnie
							WHERE id_perso='$id_perso_a_pendre'
							AND id_compagnie='$id_compagnie'";
					$res_b = $mysqli->query($sql_b);
					$tab = $res_b->fetch_assoc();

					$thune_en_banque = $tab["thune_en_banque"];
				}
				else {
					$thune_en_banque = 0;
				}

				$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ('$id_perso_a_pendre','Pendaison','', '$id_perso_a_pendre','<font color=$couleur_clan_perso>$nom_perso_a_pendre</font>', '', NOW(), 1)";
				$mysqli->query($sql);

				$tables_to_clean = ['perso_as_arme','perso_as_armure','perso_as_competence','perso_as_contact','perso_as_dossiers','perso_as_decoration','perso_as_entrainement','perso_as_killpnj','perso_as_objet','perso_as_respawn','perso_bagne','perso_in_batiment','histobanque_compagnie','banque_compagnie'];
				foreach($tables_to_clean as $tbl){
					$mysqli->query("DELETE FROM $tbl WHERE id_perso='$id_perso_a_pendre'");
				}

				if ($thune_en_banque > 0) {
					$sql = "UPDATE banque_as_compagnie SET montant = montant - $thune_en_banque WHERE id_compagnie=(SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso_a_pendre')";
					$mysqli->query($sql);

					$sql_b = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie'";
					$res_b = $mysqli->query($sql_b);
					$t_b = $res_b->fetch_assoc();

					$montant_final_banque = $t_b['montant'];
					$date = time();
					$sql = "INSERT INTO banque_log (date_log, id_compagnie, id_perso, montant_transfert, montant_final) VALUES (FROM_UNIXTIME($date), '$id_compagnie', '$id_perso_a_pendre', '-$thune_en_banque', '$montant_final_banque')";
					$mysqli->query($sql);
				}

				$mysqli->query("DELETE FROM perso_in_compagnie WHERE id_perso='$id_perso_a_pendre'");

				if (in_bat($mysqli, $id_perso_a_pendre)) {
					$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_a_pendre'";
				} else if (in_train($mysqli, $id_perso_a_pendre)) {
					$sql = "DELETE FROM perso_in_train WHERE id_perso='$id_perso_a_pendre'";
				} else {
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte='$id_perso_a_pendre'";
				}
				$mysqli->query($sql);

				$mysqli->query("UPDATE perso SET x_perso='1000', y_perso='1000' WHERE id_perso='$id_perso_a_pendre'");
				$mysqli->query("DELETE FROM perso_in_mission WHERE id_perso='$id_perso_a_pendre'");

				echo "<div class='alert alert-info text-center'>Le perso $nom_perso_a_pendre (matricule $id_perso_a_pendre) a bien été pendu.</div>";
			}
		}

		if(isset($_POST['select_perso']) && $_POST['select_perso'] != '') {
			$id_perso_select = $_POST['select_perso'];
		}
		if (isset($_GET['consulter_mp']))  { $id_perso_select = $_GET['consulter_mp']; }
		if (isset($_GET['modifier_mdp']))  { $id_perso_select = $_GET['modifier_mdp']; }
		if (isset($_GET['verifier_charge'])){ $id_perso_select = $_GET['verifier_charge']; }
		if (isset($_GET['voir_respawn']))   { $id_perso_select = $_GET['voir_respawn']; }

		if (isset($_GET['voir_inventaire'])) {

			$id_perso_select = $_GET['voir_inventaire'];

			if (isset($_GET['id_obj'])) {

				$id_o = $_GET['id_obj'];
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_o");

				if($verif && $id_o > 0) {
					if (isset($_GET['desequip'])) {
						$sql = "UPDATE perso_as_objet SET equip_objet=0 WHERE id_perso='$id_perso_select' AND id_objet='$id_o'";
						$mysqli->query($sql);
						$mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement inventaire déséquiper', 'perso $id_perso_select objet $id_o')");
					}
					elseif (isset($_GET['equip'])) {
						$sql = "UPDATE perso_as_objet SET equip_objet=1 WHERE id_perso='$id_perso_select' AND id_objet='$id_o'";
						$mysqli->query($sql);
						$mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement inventaire équiper', 'perso $id_perso_select objet $id_o')");
					}
					elseif (isset($_GET['deposer'])) {
						action_deposerObjet($mysqli, $id_perso_select, 2, $id_o, 1);
						$mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement inventaire déposer', 'perso $id_perso_select objet $id_o')");
					}
					elseif (isset($_GET['use'])) {
						if($id_o != 1){
							$sql = "SELECT * FROM objet WHERE id_objet='$id_o'";
							$res = $mysqli->query($sql);
							$bonus_o = $res->fetch_assoc();

							$nom_ob 		= $bonus_o["nom_objet"];
							$bonusPerception= $bonus_o["bonusPerception_objet"];
							$bonusRecup 	= $bonus_o["bonusRecup_objet"];
							$bonusPv 		= $bonus_o["bonusPv_objet"];
							$bonusPm 		= $bonus_o["bonusPm_objet"];
							$bonusPa		= $bonus_o["bonusPA_objet"];
							$coutPa 		= $bonus_o["coutPa_objet"];
							$poids 			= $bonus_o["poids_objet"];
							$type_o 		= $bonus_o["type_objet"];

							if ($type_o == 'N') {
								$mysqli->query("DELETE FROM perso_as_objet WHERE id_perso='$id_perso_select' AND id_objet='$id_o' LIMIT 1");
								$sql = "SELECT pv_perso, pvMax_perso, recup_perso, bonusRecup_perso FROM perso WHERE id_perso='$id_perso_select'";
								$res = $mysqli->query($sql);
								$t_p = $res->fetch_assoc();

								if($bonusRecup) {
									$mysqli->query("UPDATE perso SET bonusRecup_perso=bonusRecup_perso+$bonusRecup WHERE id_perso='$id_perso_select'");
									$mess .= "Utilisé ".$nom_ob." sur le perso<br>";
								}
								if ($bonusPerception < 0) {
									$mysqli->query("UPDATE perso SET bourre_perso=bourre_perso+1 WHERE id_perso='$id_perso_select'");
									$mess .= "Perception ".$bonusPerception;
								}
								$mysqli->query("UPDATE perso SET pa_perso = pa_perso - 1, charge_perso=charge_perso-$poids WHERE id_perso='$id_perso_select'");
							}
						}
					}
				}
			}
			elseif (isset($_GET['dest'])) {
				$dest_ticket_to_delete = $_GET['dest'];
				if (isset($_GET['delete'])) {
					$verif = preg_match("#^[0-9]*[0-9]$#i","$dest_ticket_to_delete");
					if ($verif) {
						$mysqli->query("DELETE FROM perso_as_objet WHERE id_objet='1' AND id_perso='$id_perso_select' AND capacite_objet='$dest_ticket_to_delete' LIMIT 1");
						$mess .= "Le ticket à destination de ".$dest_ticket_to_delete." a bien été supprimé";
					} else {
						$mess_err .= "Données envoyées incorrectes...";
					}
				}
			}
		}

		if (isset($_POST['id_perso_select']) && $_POST['id_perso_select'] != '') {

			$id_perso_select = $_POST['id_perso_select'];

			$fields = [
				'xp_perso'   => ['col'=>'xp_perso',     'label'=>'XP',     'log'=>'Changement XP'],
				'pi_perso'   => ['col'=>'pi_perso',     'label'=>'PI',     'log'=>'Changement XPI'],
				'pc_perso'   => ['col'=>'pc_perso',     'label'=>'PC',     'log'=>'Changement PC'],
				'or_perso'   => ['col'=>'or_perso',     'label'=>'THUNE',  'log'=>'Changement OR'],
				'pv_perso'   => ['col'=>'pv_perso',     'label'=>'PV',     'log'=>'Changement PV'],
				'pm_perso'   => ['col'=>'pm_perso',     'label'=>'PM',     'log'=>'Changement PM'],
				'pa_perso'   => ['col'=>'pa_perso',     'label'=>'PA',     'log'=>'Changement PA'],
			];

			foreach ($fields as $post_key => $info) {
				if (isset($_POST[$post_key]) && trim($_POST[$post_key]) != '') {
					$new_val = $_POST[$post_key];
					$col = $info['col'];
					$res = $mysqli->query("SELECT $col FROM perso WHERE id_perso='$id_perso_select'");
					$t = $res->fetch_assoc();
					$old_val = $t[$col];
					$mysqli->query("UPDATE perso SET $col=$new_val WHERE id_perso='$id_perso_select'");
					$mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', '{$info['log']}', 'perso $id_perso_select $old_val vers $new_val')");
					$mess = "MAJ {$info['label']} perso matricule ".$id_perso_select." vers ".$new_val;
				}
			}

			if (isset($_POST['image_perso']) && trim($_POST['image_perso']) != '') {
				$new_image_perso = $_POST['image_perso'];
				$mysqli->query("UPDATE perso SET image_perso='$new_image_perso' WHERE id_perso='$id_perso_select'");
				$mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement IMAGE', 'perso $id_perso_select vers $new_image_perso')");
				$mess = "MAJ IMAGE perso matricule ".$id_perso_select." vers ".$new_image_perso;
			}

			if (isset($_POST['ch_perso']) && trim($_POST['ch_perso']) != '') {
				$new_ch_perso = $_POST['ch_perso'];
				$mysqli->query("UPDATE perso SET charge_perso=$new_ch_perso WHERE id_perso='$id_perso_select'");
				$mess = "MAJ Charge perso matricule ".$id_perso_select." vers ".$new_ch_perso;
			}

			if (isset($_POST['mdp_perso']) && trim($_POST['mdp_perso']) != "") {
				$new_password = $_POST['mdp_perso'];
				$new_password_md5 = MD5($new_password);
				$mysqli->query("UPDATE joueur SET mdp_joueur='$new_password_md5' WHERE id_joueur=(SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_select')");
				$mess = "Changement du mot de passe du perso matricule ".$id_perso_select." vers ".$new_password;
			}
		}

?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Nord VS Sud – Admin Perso</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
		<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
		<style>
			body { background: #f8f9fa; }
			.section-card { margin-bottom: 1.5rem; }
			.table th { background: #343a40; color: #fff; }
			.badge-localisation { font-size: 1rem; padding: .5em .8em; }
			.inventory-section { border: 1px solid #dee2e6; border-radius: .5rem; padding: 1rem; margin-bottom: 1rem; background:#fff; }
			.inventory-section h6 { font-weight: 700; border-bottom: 2px solid #dee2e6; padding-bottom: .4rem; margin-bottom: .8rem; }
		</style>
	</head>
	<body>
		<div class="container-fluid py-3">

			<!-- En-tête -->
			<div class="row mb-2">
				<div class="col-12 text-center">
					<h2 class="font-weight-bold">Administration des Persos</h2>
					<a class="btn btn-primary btn-sm" href="admin_nvs.php"><i class="fa fa-arrow-left"></i> Retour administration</a>
					<a class="btn btn-secondary btn-sm ml-1" href="jouer.php"><i class="fa fa-gamepad"></i> Retour au jeu</a>
				</div>
			</div>

			<!-- Messages -->
			<?php if($mess_err): ?>
				<div class="alert alert-danger text-center"><?= $mess_err ?></div>
			<?php endif; ?>
			<?php if($mess): ?>
				<div class="alert alert-success text-center"><?= $mess ?></div>
			<?php endif; ?>

			<!-- ===== SÉLECTEUR PERSO ===== -->
			<div class="card section-card shadow-sm">
				<div class="card-header bg-dark text-white"><i class="fa fa-user"></i> Choisir un personnage</div>
				<div class="card-body">
					<form method="POST" action="admin_perso.php" class="form-inline">
						<select name="select_perso" class="form-control mr-2" onchange="this.form.submit()" style="max-width:400px;">
							<option value="">— Sélectionner un perso —</option>
							<?php
							$sql = "SELECT id_perso, nom_perso, x_perso, y_perso FROM perso ORDER BY id_perso ASC";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								$sel = (isset($id_perso_select) && $id_perso_select == $t['id_perso']) ? ' selected' : '';
								echo "<option value='{$t['id_perso']}'{$sel}>[{$t['id_perso']}] {$t['nom_perso']} ({$t['x_perso']}/{$t['y_perso']})</option>";
							}
							?>
						</select>
						<button type="submit" class="btn btn-primary">Choisir</button>
					</form>
				</div>
			</div>

			<?php
			if (isset($id_perso_select) && $id_perso_select != 0) {

				// --- Données perso ---
				$sql = "SELECT email_joueur FROM joueur, perso WHERE id_joueur = idJoueur_perso AND id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t_j = $res->fetch_assoc();
				$email_joueur = $t_j['email_joueur'];

				$sql = "SELECT * FROM perso WHERE id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();

				$nom_perso		= $t['nom_perso'];
				$xp_perso		= $t['xp_perso'];
				$pc_perso		= $t['pc_perso'];
				$pi_perso		= $t['pi_perso'];
				$pv_perso		= $t['pv_perso'];
				$pm_perso		= $t['pm_perso'];
				$pa_perso		= $t['pa_perso'];
				$or_perso		= $t['or_perso'];
				$ch_perso		= $t['charge_perso'];
				$type_p			= $t['type_perso'];
				$test_b			= $t['bourre_perso'];
				$camp_perso		= $t['clan'];
				$bat_perso		= $t['bataillon'];
				$image_perso	= $t['image_perso'];
				$x_perso		= $t['x_perso'];
				$y_perso		= $t['y_perso'];
				$pvMax_perso	= $t['pvMax_perso'];
				$pmMax_perso	= $t['pmMax_perso'] ?? $pm_perso;
				$paMax_perso	= $t['paMax_perso'] ?? $pa_perso;
				$niveau_perso	= $t['niveau_perso'] ?? '';
				$recup_perso	= $t['recup_perso'] ?? '';
				$force_perso	= $t['force_perso'] ?? '';
				$perception_perso = $t['perception_perso'] ?? '';

				$camp_labels = [1=>'Nord',2=>'Sud',3=>'Indiens'];
				$nom_camp_perso = $camp_labels[$camp_perso] ?? 'Outlaw';
				$im_type_perso = get_image_type_perso($type_p, $camp_perso);

				// ================================================================
				// TABLEAU 1 – CARACTÉRISTIQUES DU PERSO
				// ================================================================
				?>
				<div class="card section-card shadow-sm">
					<div class="card-header bg-dark text-white">
						<i class="fa fa-id-card"></i> Tableau 1 – Caractéristiques du personnage
						<span class="float-right text-warning"><?= htmlspecialchars($nom_perso) ?> [#<?= $id_perso_select ?>]</span>
					</div>
					<div class="card-body p-2">

						<div class="row mb-2 align-items-center">
							<div class="col-auto text-center">
								<img src="../images_perso/<?= $im_type_perso ?>" class="img-thumbnail" style="max-height:90px;" alt="<?= htmlspecialchars($nom_perso) ?>">
								<br><small class="text-muted"><?= htmlspecialchars($nom_camp_perso) ?></small>
							</div>
							<div class="col">
								<small><b>Email joueur :</b> <?= htmlspecialchars($email_joueur) ?></small><br>
								<small><b>Bataillon :</b> <?= htmlspecialchars($bat_perso) ?></small>
								<?php if($test_b >= 1): ?>
									<span class="badge badge-warning ml-2"><i class="fa fa-beer"></i> Bourré (<?= $test_b ?>)</span>
								<?php endif; ?>
							</div>
						</div>

						<div class="table-responsive">
							<table class="table table-bordered table-sm table-hover align-middle mb-0">
								<thead class="thead-dark">
									<tr>
										<th class="text-center">Caractéristique</th>
										<th class="text-center">Valeur actuelle</th>
										<th class="text-center">Modifier</th>
									</tr>
								</thead>
								<tbody>

									<?php
									// Champs modifiables
									$carac_fields = [
										['label'=>'XP',         'post'=>'xp_perso',    'val'=>$xp_perso,    'icon'=>'fa-star',        'color'=>'warning'],
										['label'=>'PI',         'post'=>'pi_perso',    'val'=>$pi_perso,    'icon'=>'fa-graduation-cap','color'=>'info'],
										['label'=>'PC',         'post'=>'pc_perso',    'val'=>$pc_perso,    'icon'=>'fa-certificate',  'color'=>'info'],
										['label'=>'Thune (or)', 'post'=>'or_perso',    'val'=>$or_perso,    'icon'=>'fa-money',        'color'=>'success'],
										['label'=>'PV',         'post'=>'pv_perso',    'val'=>$pv_perso,    'icon'=>'fa-heart',        'color'=>'danger'],
										['label'=>'PM',         'post'=>'pm_perso',    'val'=>$pm_perso,    'icon'=>'fa-bolt',         'color'=>'primary'],
										['label'=>'PA',         'post'=>'pa_perso',    'val'=>$pa_perso,    'icon'=>'fa-clock-o',      'color'=>'secondary'],
										['label'=>'Charge',     'post'=>'ch_perso',    'val'=>$ch_perso,    'icon'=>'fa-suitcase',     'color'=>'dark'],
										['label'=>'Image',      'post'=>'image_perso', 'val'=>$image_perso, 'icon'=>'fa-picture-o',    'color'=>'secondary'],
									];
									foreach($carac_fields as $cf):
									?>
									<tr>
										<td class="text-center font-weight-bold">
											<i class="fa <?= $cf['icon'] ?> text-<?= $cf['color'] ?>"></i>
											<?= $cf['label'] ?>
										</td>
										<td class="text-center">
											<span class="badge badge-<?= $cf['color'] ?> badge-pill px-3 py-2"><?= htmlspecialchars($cf['val']) ?></span>
										</td>
										<td class="text-center">
											<form method="POST" action="admin_perso.php" class="form-inline justify-content-center">
												<input type="hidden" name="id_perso_select" value="<?= $id_perso_select ?>">
												<input type="text" name="<?= $cf['post'] ?>" value="<?= htmlspecialchars($cf['val']) ?>" class="form-control form-control-sm mr-1" style="width:110px;">
												<button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa fa-save"></i> Modifier</button>
											</form>
										</td>
									</tr>
									<?php endforeach; ?>

									<!-- Position  -->
									<tr>
										<td class="text-center font-weight-bold"><i class="fa fa-map-marker text-danger"></i> Position</td>
										<td class="text-center"><span class="badge badge-secondary badge-pill px-3 py-2"><?= $x_perso ?> / <?= $y_perso ?></span></td>
										<td class="text-center text-muted"><small>Non modifiable ici</small></td>
									</tr>

								</tbody>
							</table>
						</div>

						<!-- Boutons d'actions -->
						<div class="mt-3 d-flex flex-wrap gap-2">
							<?php
							$btn_mp  = isset($_GET['consulter_mp'])  ? 'btn-secondary' : 'btn-primary';
							$btn_chg = isset($_GET['verifier_charge'])? 'btn-secondary' : 'btn-primary';
							$btn_rsp = isset($_GET['voir_respawn'])  ? 'btn-secondary' : 'btn-primary';
							$btn_inv = isset($_GET['voir_inventaire'])? 'btn-secondary' : 'btn-primary';
							$btn_mdp = isset($_GET['modifier_mdp'])  ? 'btn-secondary' : 'btn-danger';
							?>
							<a href="admin_perso.php?consulter_mp=<?= $id_perso_select ?>" class="btn btn-sm <?= $btn_mp ?>"><i class="fa fa-envelope"></i> Consulter les MP</a>
							<a href="admin_perso.php?verifier_charge=<?= $id_perso_select ?>" class="btn btn-sm <?= $btn_chg ?>"><i class="fa fa-balance-scale"></i> Vérifier la charge</a>
							<a href="admin_perso.php?voir_respawn=<?= $id_perso_select ?>" class="btn btn-sm <?= $btn_rsp ?>"><i class="fa fa-map"></i> Voir respawns</a>
							<a href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>" class="btn btn-sm <?= $btn_inv ?>"><i class="fa fa-briefcase"></i> Voir inventaire</a>
							<a href="admin_perso.php?modifier_mdp=<?= $id_perso_select ?>" class="btn btn-sm <?= $btn_mdp ?>"><i class="fa fa-key"></i> Modifier MDP</a>
							<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalPendre<?= $id_perso_select ?>">
								<i class="fa fa-legal"></i> Pendre
							</button>
						</div>

						<!-- Modifier MDP -->
						<?php if (isset($_GET['modifier_mdp'])): ?>
						<form method="POST" action="admin_perso.php" class="form-inline mt-3">
							<input type="hidden" name="id_perso_select" value="<?= $id_perso_select ?>">
							<label class="mr-2"><b>Nouveau MDP :</b></label>
							<input type="text" name="mdp_perso" class="form-control form-control-sm mr-2">
							<button type="submit" class="btn btn-sm btn-primary">Modifier</button>
						</form>
						<?php endif; ?>

						<!-- Vérifier charge -->
						<?php if (isset($_GET['verifier_charge'])): ?>
						<div class="alert alert-light mt-3 border">
							<?php
							$res_po = $mysqli->query("SELECT SUM(poids_objet) as sp FROM perso_as_objet, objet WHERE perso_as_objet.id_objet=objet.id_objet AND id_perso='$id_perso_select'");
							$poids_objets = $res_po->fetch_assoc()['sp'] ?? 0;
							$res_pa = $mysqli->query("SELECT SUM(poids_arme) as sp FROM perso_as_arme, arme WHERE perso_as_arme.id_arme=arme.id_arme AND id_perso='$id_perso_select' AND est_portee='0'");
							$poids_armes = $res_pa->fetch_assoc()['sp'] ?? 0;
							?>
							<b><i class="fa fa-balance-scale"></i> Charge détaillée :</b><br>
							Poids objets dans le sac : <b><?= $poids_objets ?></b><br>
							Poids armes dans le sac : <b><?= $poids_armes ?></b><br>
							Total : <b><?= $poids_objets + $poids_armes ?></b> / <?= $ch_perso ?>
						</div>
						<?php endif; ?>

						<!-- Voir respawns -->
						<?php if (isset($_GET['voir_respawn'])): ?>
						<div class="mt-3">
							<h6><i class="fa fa-map-o"></i> Points de respawn</h6>
							<table class="table table-sm table-bordered table-striped">
								<thead class="thead-dark"><tr><th>Bâtiment</th><th>État (PV)</th><th>Position</th></tr></thead>
								<tbody>
								<?php
								$res_r = $mysqli->query("SELECT * FROM perso_as_respawn WHERE id_perso='$id_perso_select' ORDER BY id_bat ASC");
								while ($t_r = $res_r->fetch_assoc()){
									$id_i = $t_r['id_instance_bat'];
									$sql_b = "SELECT nom_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance FROM instance_batiment, batiment WHERE instance_batiment.id_batiment=batiment.id_batiment AND id_instanceBat='$id_i'";
									$t_b = $mysqli->query($sql_b)->fetch_assoc();
									echo "<tr><td>{$t_b['nom_batiment']} {$t_b['nom_instance']} [#$id_i]</td><td>{$t_b['pv_instance']}/{$t_b['pvMax_instance']}</td><td>{$t_b['x_instance']}/{$t_b['y_instance']}</td></tr>";
								}
								?>
								</tbody>
							</table>
						</div>
						<?php endif; ?>

					</div>
				</div>

				<!-- Modal pendre -->
				<form method="post" action="admin_perso.php">
					<div class="modal fade" id="modalPendre<?= $id_perso_select ?>" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">Pendre <?= htmlspecialchars($nom_perso) ?> [#<?= $id_perso_select ?>] ?</h5>
									<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
								</div>
								<div class="modal-body">
									<p>Êtes-vous sûr de vouloir pendre ce personnage ?</p>
									<input type="text" name="raison_pendaison" class="form-control" placeholder="Raison de la pendaison...">
									<input type="hidden" name="matricule_pendre_hidden" value="<?= $id_perso_select ?>">
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
									<button type="button" onclick="this.form.submit()" class="btn btn-danger"><i class="fa fa-legal"></i> Confirmer</button>
								</div>
							</div>
						</div>
					</div>
				</form>

				<?php
				// ================================================================
				// TABLEAU 2 – LOCALISATION (bâtiment / train / dehors)
				// ================================================================
				?>
				<div class="card section-card shadow-sm">
					<div class="card-header bg-dark text-white">
						<i class="fa fa-map-marker"></i> Tableau 2 – Localisation du personnage
					</div>
					<div class="card-body text-center py-4">
						<?php
						// ---- Récupération localisation du perso ----
						// Un perso peut être dans perso_in_batiment avec :
						//   - id_batiment != 12  => bâtiment classique
						//   - id_batiment  = 12  => c'est un train, nom_instance = nom du train
						// Ou dans perso_in_train (id_train = id_instanceBat du train)
						// Ou dans la pampa (ni train ni batiment)

						$sql_bat = "SELECT pib.id_instanceBat,
										ib.nom_instance, ib.id_batiment,
										ib.pv_instance, ib.pvMax_instance,
										ib.x_instance, ib.y_instance,
										ib.niveau_instance, ib.contenance_instance,
										b.nom_batiment
									FROM perso_in_batiment pib
									JOIN instance_batiment ib ON pib.id_instanceBat = ib.id_instanceBat
									JOIN batiment b ON ib.id_batiment = b.id_batiment
									WHERE pib.id_perso = '$id_perso_select'";
						$res_bat = $mysqli->query($sql_bat);
						$in_bat_row = ($res_bat && $res_bat->num_rows > 0) ? $res_bat->fetch_assoc() : null;

						// Vérifie aussi perso_in_train (cas où le perso est noté dans les deux)
						$res_pit = $mysqli->query("SELECT id_train FROM perso_in_train WHERE id_perso='$id_perso_select'");
						$in_train_row = ($res_pit && $res_pit->num_rows > 0) ? $res_pit->fetch_assoc() : null;

						// id_batiment = 12 => instance = un train, nom_instance = nom du train
						$bat_is_train = $in_bat_row && (int)$in_bat_row['id_batiment'] === 12;

						if ($bat_is_train || $in_train_row) {
							// === DANS UN TRAIN ===
							// Si via perso_in_batiment (id_batiment=12) : nom_instance = nom du train, id_instanceBat = id du train
							// Si via perso_in_train uniquement : orecuperation du nom via instance_batiment
							$id_train_val  = $bat_is_train ? $in_bat_row['id_instanceBat'] : $in_train_row['id_train'];
							$nom_train_val = '';
							if ($bat_is_train && $in_bat_row['nom_instance']) {
								$nom_train_val = $in_bat_row['nom_instance'];
							} else {
								// Fallback : chercher dans instance_batiment
								$r_t = $mysqli->query("SELECT nom_instance FROM instance_batiment WHERE id_instanceBat='$id_train_val' AND id_batiment=12");
								if ($r_t && $r_t->num_rows > 0) $nom_train_val = $r_t->fetch_assoc()['nom_instance'];
							}
							?>
							<div class="d-flex flex-wrap justify-content-center align-items-center" style="gap:.5rem;">
								<span class="badge badge-primary badge-localisation">
									<i class="fa fa-train"></i> Perso dans un train
								</span>
								<span class="badge badge-light border badge-localisation">
									Train #<?= $id_train_val ?>
									<?= $nom_train_val ? ' – <strong>'.htmlspecialchars($nom_train_val).'</strong>' : '' ?>
								</span>
								<?php if ($bat_is_train): ?>
								<span class="badge badge-info badge-localisation">
									<i class="fa fa-heartbeat"></i> PV : <?= $in_bat_row['pv_instance'] ?>/<?= $in_bat_row['pvMax_instance'] ?>
								</span>
								<span class="badge badge-secondary badge-localisation">
									<i class="fa fa-map-pin"></i> <?= $in_bat_row['x_instance'] ?> / <?= $in_bat_row['y_instance'] ?>
								</span>
								<?php endif; ?>
							</div>
							<?php

						} elseif ($in_bat_row) {
							// === DANS UN BÂTIMENT  ===
							?>
							<div class="d-flex flex-wrap justify-content-center align-items-center" style="gap:.5rem;">
								<span class="badge badge-success badge-localisation">
									<i class="fa fa-home"></i> Dans un bâtiment
								</span>
								<span class="badge badge-light border badge-localisation">
									#<?= $in_bat_row['id_instanceBat'] ?>
									– <strong><?= htmlspecialchars($in_bat_row['nom_batiment']) ?></strong>
									<?= $in_bat_row['nom_instance'] ? ' – '.htmlspecialchars($in_bat_row['nom_instance']) : '' ?>
								</span>
								<span class="badge badge-info badge-localisation">
									<i class="fa fa-heartbeat"></i> PV : <?= $in_bat_row['pv_instance'] ?>/<?= $in_bat_row['pvMax_instance'] ?>
								</span>
								<?php if ((int)$in_bat_row['niveau_instance'] > 0): ?>
								<span class="badge badge-warning badge-localisation">
									<i class="fa fa-level-up"></i> Niv. <?= $in_bat_row['niveau_instance'] ?>
								</span>
								<?php endif; ?>
								<span class="badge badge-secondary badge-localisation">
									<i class="fa fa-map-pin"></i> <?= $in_bat_row['x_instance'] ?> / <?= $in_bat_row['y_instance'] ?>
								</span>
							</div>
							<?php

						} else {
							// === SUR LE TERRAIN ===
							?>
							<div class="d-flex flex-wrap justify-content-center align-items-center" style="gap:.5rem;">
								<span class="badge badge-danger badge-localisation">
									<i class="fa fa-sun-o"></i> Perso dans la Pampa
								</span>
								<span class="badge badge-secondary badge-localisation">
									<i class="fa fa-map-pin"></i> Position : <?= $x_perso ?> / <?= $y_perso ?>
								</span>
							</div>
							<?php
						}
						?>
					</div>
				</div>

				<?php
				// ================================================================
				// TABLEAU 3 – COMPAGNIE
				// ================================================================
				$sql_comp = "SELECT pic.id_compagnie, c.nom_compagnie, c.couleur_compagnie, c.image_compagnie,
								c.resume_compagnie,
								p.nom_poste, p.role_level, p.description AS description_poste,
								pic.attenteValidation_compagnie, pic.poste_compagnie
							 FROM perso_in_compagnie pic
							 JOIN compagnies c ON pic.id_compagnie = c.id_compagnie
							 LEFT JOIN poste p ON pic.poste_compagnie = p.id_poste
							 WHERE pic.id_perso = '$id_perso_select'";
				$res_comp = $mysqli->query($sql_comp);
				$comp_row = $res_comp ? $res_comp->fetch_assoc() : null;
				?>
				<div class="card section-card shadow-sm">
					<div class="card-header bg-dark text-white">
						<i class="fa fa-users"></i> Tableau 3 – Compagnie
					</div>
					<div class="card-body p-2">
						<?php if ($comp_row): ?>
						<div class="table-responsive">
							<table class="table table-bordered table-sm table-hover mb-0">
								<thead class="thead-dark">
									<tr>
										<th class="text-center">Compagnie</th>
										<th class="text-center">Poste / Rang</th>
										<th class="text-center">Niveau (role_level)</th>
										<th class="text-center">Statut d'incorporation</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<!-- Compagnie -->
										<td class="text-center">
											<?php if($comp_row['image_compagnie']): ?>
												<img src="../images/compagnies/<?= htmlspecialchars($comp_row['image_compagnie']) ?>" class="img-thumbnail mb-1" style="max-height:45px;" alt="">
												<br>
											<?php endif; ?>
											<span style="color:<?= htmlspecialchars($comp_row['couleur_compagnie']) ?>; font-weight:700; font-size:1.05em;">
												<?= htmlspecialchars($comp_row['nom_compagnie']) ?>
											</span>
											<small class="text-muted d-block">[#<?= $comp_row['id_compagnie'] ?>]</small>
											<?php if($comp_row['resume_compagnie']): ?>
												<small class="text-muted font-italic"><?= htmlspecialchars($comp_row['resume_compagnie']) ?></small>
											<?php endif; ?>
										</td>
										<!-- Poste -->
										<td class="text-center">
											<span class="font-weight-bold"><?= htmlspecialchars($comp_row['nom_poste'] ?? '—') ?></span>
											<?php if($comp_row['description_poste']): ?>
												<br><small class="text-muted"><?= htmlspecialchars($comp_row['description_poste']) ?></small>
											<?php endif; ?>
										</td>
										<!-- Niveau -->
										<td class="text-center">
											<?php
											$rl = (int)$comp_row['role_level'];
											$badge_rl = 'secondary';
											if ($rl <= 2)      $badge_rl = 'danger';
											elseif ($rl <= 4)  $badge_rl = 'warning';
											elseif ($rl <= 6)  $badge_rl = 'info';
											else               $badge_rl = 'secondary';
											?>
											<span class="badge badge-<?= $badge_rl ?> badge-pill" style="font-size:.95em; padding:.4em .8em;">
												<?= $rl ?>
											</span>
										</td>
										<!-- Statut -->
										<td class="text-center">
											<?php if ($comp_row['attenteValidation_compagnie'] == 1): ?>
												<span class="badge badge-warning px-3 py-2" style="font-size:.9em;">
													<i class="fa fa-clock-o"></i> En attente de validation
												</span>
											<?php else: ?>
												<span class="badge badge-success px-3 py-2" style="font-size:.9em;">
													<i class="fa fa-check-circle"></i> Membre validé
												</span>
											<?php endif; ?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<?php else: ?>
							<div class="text-center py-3">
								<span class="badge badge-secondary badge-localisation">
									<i class="fa fa-user-times"></i> Sans compagnie
								</span>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php
				// ================================================================
				// TABLEAU 4 – INVENTAIRE (sac & équipé)
				// ================================================================
				?>
				<div class="card section-card shadow-sm">
					<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
						<span><i class="fa fa-briefcase"></i> Tableau 4 – Inventaire</span>
						<?php if (isset($_GET['voir_inventaire'])): ?>
							<a href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>&ajout_objet=ok" class="btn btn-warning btn-sm">
								<i class="fa fa-plus"></i> Ajouter un objet
							</a>
						<?php else: ?>
							<a href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>" class="btn btn-primary btn-sm">
								<i class="fa fa-eye"></i> Voir l'inventaire
							</a>
						<?php endif; ?>
					</div>
					<div class="card-body">
						<?php if (isset($_GET['voir_inventaire'])): ?>

						<div class="row">
							<!-- === SECTION 1 : SAC (non équipé) === -->
							<div class="col-md-6">
								<div class="inventory-section">
									<h6><i class="fa fa-shopping-bag text-secondary"></i> Dans le sac (non équipé)</h6>

									<!-- Objets non équipés -->
									<?php
									$sql_obj_sac = "SELECT DISTINCT id_objet FROM perso_as_objet WHERE id_perso='$id_perso_select' AND equip_objet='0' ORDER BY id_objet";
									$res_obj_sac = $mysqli->query($sql_obj_sac);
									$nb_obj_sac = $res_obj_sac->num_rows;

									// Armes non portées
									$sql_arme_sac = "SELECT DISTINCT id_arme FROM perso_as_arme WHERE id_perso='$id_perso_select' AND est_portee='0' ORDER BY id_arme";
									$res_arme_sac = $mysqli->query($sql_arme_sac);
									$nb_arme_sac = $res_arme_sac->num_rows;
									?>

									<?php if ($nb_obj_sac == 0 && $nb_arme_sac == 0): ?>
										<p class="text-muted text-center"><em>Sac vide</em></p>
									<?php else: ?>
									<div class="table-responsive">
										<table class="table table-sm table-bordered table-hover">
											<thead class="thead-dark">
												<tr><th>Objet / Arme</th><th class="text-center">Qté</th><th class="text-center">Poids</th><th class="text-center">Actions</th></tr>
											</thead>
											<tbody>
											<?php
											// Objets
											while ($t_obj = $res_obj_sac->fetch_assoc()) {
												$id_obj = $t_obj['id_objet'];
												$t_o = $mysqli->query("SELECT nom_objet, poids_objet, type_objet FROM objet WHERE id_objet='$id_obj'")->fetch_assoc();
												$res2 = $mysqli->query("SELECT id_objet, capacite_objet FROM perso_as_objet WHERE id_perso='$id_perso_select' AND id_objet='$id_obj' AND equip_objet='0'");
												$nb_o = $res2->num_rows;
												$poids_total_o = $t_o['poids_objet'] * $nb_o;
												$type_o = $t_o['type_objet'];
												?>
												<tr>
													<td>
														<img class="img-fluid mr-1" src="../images/objets/objet<?= $id_obj ?>.png" style="max-height:30px;" alt="">
														<span class="text-success font-weight-bold"><?= htmlspecialchars($t_o['nom_objet']) ?></span>
														<span class="badge badge-secondary ml-1"><?= $type_o ?></span>
													</td>
													<td class="text-center"><b><?= $nb_o ?></b></td>
													<td class="text-center"><?= $poids_total_o ?></td>
													<td class="text-center">
														<?php if($type_o=='N'): ?>
															<?php if($test_b >= 2 && $id_obj == 3): ?>
																<span class="text-danger small">Max whisky</span>
															<?php else: ?>
																<a class="btn btn-xs btn-outline-success btn-sm" href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>&id_obj=<?= $id_obj ?>&use=ok" title="Utiliser"><i class="fa fa-play"></i></a>
															<?php endif; ?>
														<?php endif; ?>
														<?php if($type_o=='E' && $type_p != 6): ?>
															<a class="btn btn-sm btn-outline-primary" href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>&id_obj=<?= $id_obj ?>&equip=ok" title="Équiper"><i class="fa fa-arrow-up"></i></a>
														<?php endif; ?>
														<?php if($type_o=='T'): ?>
															<br><b>Destinations :</b><br>
															<?php while($t_ticket = $res2->fetch_assoc()): $dest = $t_ticket['capacite_objet']; ?>
																<?php if(trim($dest)==""): ?>
																	<span class="text-muted small">Ticket invalide</span>
																<?php else: ?>
																	<a class="btn btn-primary btn-sm" href="evenement.php?infoid=<?= $dest ?>"><?= htmlspecialchars($dest) ?></a>
																	<a class="btn btn-danger btn-sm" href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>&dest=<?= $dest ?>&delete=ok"><i class="fa fa-trash"></i></a>
																<?php endif; ?>
															<?php endwhile; ?>
														<?php else: ?>
															<a class="btn btn-sm btn-outline-secondary" href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>&id_obj=<?= $id_obj ?>&deposer=ok" title="Déposer"><i class="fa fa-sign-out"></i></a>
															<a class="btn btn-sm btn-outline-danger" href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>&id_obj=<?= $id_obj ?>&delete=ok" title="Supprimer"><i class="fa fa-trash"></i></a>
														<?php endif; ?>
													</td>
												</tr>
												<?php
											}
											// Armes non portées
											while ($t_arme = $res_arme_sac->fetch_assoc()) {
												$id_arme = $t_arme['id_arme'];
												$t_a = $mysqli->query("SELECT nom_arme, poids_arme, image_arme FROM arme WHERE id_arme='$id_arme'")->fetch_assoc();
												$nb_a = $mysqli->query("SELECT id_arme FROM perso_as_arme WHERE id_perso='$id_perso_select' AND id_arme='$id_arme' AND est_portee='0'")->num_rows;
												$poids_total_a = $t_a['poids_arme'] * $nb_a;
												$res_u = $mysqli->query("SELECT nom_unite FROM type_unite, arme_as_type_unite WHERE type_unite.id_unite=arme_as_type_unite.id_type_unite AND arme_as_type_unite.id_arme='$id_arme'");
												$liste_unite = [];
												while($t_u=$res_u->fetch_assoc()) $liste_unite[]=$t_u['nom_unite'];
												?>
												<tr>
													<td>
														<img class="img-fluid mr-1" src="../images/armes/<?= htmlspecialchars($t_a['image_arme']) ?>" style="max-height:30px;" alt="">
														<span class="text-danger font-weight-bold"><?= htmlspecialchars($t_a['nom_arme']) ?></span>
														<span class="badge badge-danger ml-1">ARME</span>
													</td>
													<td class="text-center"><b><?= $nb_a ?></b></td>
													<td class="text-center"><?= $poids_total_a ?></td>
													<td class="text-center">
														<a class="btn btn-sm btn-outline-danger" href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>&id_arme=<?= $id_arme ?>&delete=ok" title="Supprimer"><i class="fa fa-trash"></i></a>
													</td>
												</tr>
												<?php
											}
											?>
											</tbody>
										</table>
									</div>
									<?php endif; ?>
								</div>
							</div>

							<!-- === SECTION 2 : ÉQUIPÉ === -->
							<div class="col-md-6">
								<div class="inventory-section">
									<h6><i class="fa fa-shield text-success"></i> Équipement en cours</h6>

									<?php
									// Objets équipés
									$sql_obj_equip = "SELECT DISTINCT id_objet FROM perso_as_objet WHERE id_perso='$id_perso_select' AND equip_objet='1' ORDER BY id_objet";
									$res_obj_equip = $mysqli->query($sql_obj_equip);
									$nb_obj_equip = $res_obj_equip->num_rows;

									// Armes portées
									$sql_arme_equip = "SELECT DISTINCT id_arme FROM perso_as_arme WHERE id_perso='$id_perso_select' AND est_portee='1' ORDER BY id_arme";
									$res_arme_equip = $mysqli->query($sql_arme_equip);
									$nb_arme_equip = $res_arme_equip->num_rows;
									?>

									<?php if ($nb_obj_equip == 0 && $nb_arme_equip == 0): ?>
										<p class="text-muted text-center"><em>Rien d'équipé</em></p>
									<?php else: ?>
									<div class="table-responsive">
										<table class="table table-sm table-bordered table-hover">
											<thead class="thead-dark">
												<tr><th>Objet / Arme</th><th class="text-center">Qté</th><th class="text-center">Poids</th><th class="text-center">Actions</th></tr>
											</thead>
											<tbody>
											<?php
											// Objets équipés
											while ($t_obj = $res_obj_equip->fetch_assoc()) {
												$id_obj = $t_obj['id_objet'];
												$t_o = $mysqli->query("SELECT nom_objet, poids_objet, type_objet FROM objet WHERE id_objet='$id_obj'")->fetch_assoc();
												$nb_o = $mysqli->query("SELECT id_objet FROM perso_as_objet WHERE id_perso='$id_perso_select' AND id_objet='$id_obj' AND equip_objet='1'")->num_rows;
												$poids_total_o = $t_o['poids_objet'] * $nb_o;
												?>
												<tr>
													<td>
														<img class="img-fluid mr-1" src="../images/objets/objet<?= $id_obj ?>.png" style="max-height:30px;" alt="">
														<span class="text-success font-weight-bold"><?= htmlspecialchars($t_o['nom_objet']) ?></span>
														<span class="badge badge-success ml-1">ÉQUIPÉ</span>
													</td>
													<td class="text-center"><b><?= $nb_o ?></b></td>
													<td class="text-center"><?= $poids_total_o ?></td>
													<td class="text-center">
														<a class="btn btn-sm btn-outline-warning" href="admin_perso.php?voir_inventaire=<?= $id_perso_select ?>&id_obj=<?= $id_obj ?>&desequip=ok" title="Déséquiper"><i class="fa fa-arrow-down"></i> Déséquiper</a>
													</td>
												</tr>
												<?php
											}
											// Armes portées
											while ($t_arme = $res_arme_equip->fetch_assoc()) {
												$id_arme = $t_arme['id_arme'];
												$t_a = $mysqli->query("SELECT nom_arme, poids_arme, image_arme FROM arme WHERE id_arme='$id_arme'")->fetch_assoc();
												$nb_a = $mysqli->query("SELECT id_arme FROM perso_as_arme WHERE id_perso='$id_perso_select' AND id_arme='$id_arme' AND est_portee='1'")->num_rows;
												$poids_total_a = $t_a['poids_arme'] * $nb_a;
												?>
												<tr>
													<td>
														<img class="img-fluid mr-1" src="../images/armes/<?= htmlspecialchars($t_a['image_arme']) ?>" style="max-height:30px;" alt="">
														<span class="text-danger font-weight-bold"><?= htmlspecialchars($t_a['nom_arme']) ?></span>
														<span class="badge badge-danger ml-1">PORTÉE</span>
													</td>
													<td class="text-center"><b><?= $nb_a ?></b></td>
													<td class="text-center"><?= $poids_total_a ?></td>
													<td class="text-center">
														<span class="text-muted small"><i class="fa fa-lock"></i> En combat</span>
													</td>
												</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
									<?php endif; ?>
								</div>
							</div>
						</div><!-- /.row inventaire -->

						<?php else: ?>
							<p class="text-muted text-center mb-0">
								<i class="fa fa-info-circle"></i> Cliquez sur "Voir l'inventaire" pour afficher le contenu du sac.
							</p>
						<?php endif; ?>

					</div>
				</div>

				<!-- ================================================================
				     SECTION : Consulter les MP
				     ================================================================ -->
				<?php if (isset($_GET['consulter_mp'])): ?>
				<div class="card section-card shadow-sm">
					<div class="card-header bg-dark text-white"><i class="fa fa-envelope"></i> Messages privés</div>
					<div class="card-body p-2">
						<?php
						$res_mp_e = $mysqli->query("SELECT * FROM message WHERE id_expediteur='$id_perso_select' ORDER BY id_message DESC");
						$nb_mp_e = $res_mp_e->num_rows;
						echo "<h6>MP envoyés ($nb_mp_e)</h6>";
						echo "<div class='table-responsive'><table class='table table-sm table-bordered table-hover'><thead class='thead-dark'><tr><th>Date</th><th>Objet</th><th>Contenu</th></tr></thead><tbody>";
						while($t_mp=$res_mp_e->fetch_assoc()){
							echo "<tr><td>{$t_mp['date_message']}</td><td>{$t_mp['objet_message']}</td><td>{$t_mp['contenu_message']}</td></tr>";
						}
						echo "</tbody></table></div>";

						$res_mp_r = $mysqli->query("SELECT date_message, objet_message, contenu_message FROM message, message_perso WHERE message.id_message=message_perso.id_message AND message_perso.id_perso='$id_perso_select' ORDER BY message.id_message DESC");
						$nb_mp_r = $res_mp_r->num_rows;
						echo "<h6 class='mt-3'>MP reçus ($nb_mp_r)</h6>";
						echo "<div class='table-responsive'><table class='table table-sm table-bordered table-hover'><thead class='thead-dark'><tr><th>Date</th><th>Objet</th><th>Contenu</th></tr></thead><tbody>";
						while($t_mp=$res_mp_r->fetch_assoc()){
							echo "<tr><td>{$t_mp['date_message']}</td><td>{$t_mp['objet_message']}</td><td>{$t_mp['contenu_message']}</td></tr>";
						}
						echo "</tbody></table></div>";
						?>
					</div>
				</div>
				<?php endif; ?>

			<?php } // fin if isset($id_perso_select) ?>

		</div><!-- /.container-fluid -->

		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
	</body>
</html>

<?php
	}
	else {
		$_SESSION = array();
		session_destroy();
		header("Location:../index.php");
	}
}
else{
	echo "<div class='alert alert-danger m-3'>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</div>";
}
