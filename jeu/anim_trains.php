<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path)) {
    include ($phpbb_root_path .'config.php');
}

$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"] ?? 0);

if($dispo == '1' || $admin) {
    if (isset($_SESSION["id_perso"])) {
        $id = $_SESSION["id_perso"];

        if (anim_perso($mysqli, $id)) {
            // Récupération du camp
            $sql = "SELECT clan FROM perso WHERE id_perso='$id'";
            $res = $mysqli->query($sql);
            $t = $res->fetch_assoc();
            $camp = $t['clan'];
            $nom_camp = ($camp == '1') ? 'Nord' : (($camp == '2') ? 'Sud' : 'Indien');

            // --- CALCUL DU STOCK GLOBAL ---
            $sql_total_fer = "SELECT SUM(re.stock_fer) as total_fer
                            FROM ressources_entrepot re
                            JOIN instance_batiment ib ON re.id_instance_bat = ib.id_instanceBat
                            WHERE ib.id_batiment = 6 AND ib.camp_instance = '$camp'";
            $res_total = $mysqli->query($sql_total_fer);
            $t_total = $res_total->fetch_assoc();
            $fer_total_dispo = $t_total['total_fer'] ?? 0;

            $mess = "";
            $mess_erreur = "";

            // --- TRAITEMENTS (CRÉATION / DESTRUCTION) ---
            if (isset($_GET['creer_train_liaison']) && isset($_GET['creer_train_pos'])) {
                $id_gares_liaison = $_GET['creer_train_liaison'];
                $pos = $_GET['creer_train_pos'];
                $t_gares = explode(',',$id_gares_liaison);
                $id_gare1_liaison = $t_gares[0];
                $id_gare2_liaison = $t_gares[1];
                $t_pos = explode(',',$pos);
                $x_respawn_train = (int)($t_pos[0] ?? 0);
                $y_respawn_train = (int)($t_pos[1] ?? 0);

                if (preg_match("#^[0-9]*$#", $id_gare1_liaison) && preg_match("#^[0-9]*$#", $id_gare2_liaison)) {
                    $sql_c = "SELECT x_carte FROM carte WHERE (fond_carte LIKE 'rail%') AND x_carte=$x_respawn_train AND y_carte=$y_respawn_train";
                    $res_c = $mysqli->query($sql_c);

                    if (!$res_c->num_rows) {
                        $mess_erreur .= "Il faut ajouter le train sur une case rail (Position $x_respawn_train/$y_respawn_train invalide).";
                    } else if ($fer_total_dispo < 30) {
                        $mess_erreur = "Stock global insuffisant (30 requis).";
                    } else {
                        $mysqli->query("LOCK TABLES instance_batiment WRITE, instance_batiment AS ib WRITE, liaisons_gare WRITE, ressources_entrepot WRITE, ressources_entrepot AS re WRITE, carte WRITE");

                        $sql_e = "SELECT re.id_instance_bat FROM ressources_entrepot re
                                  JOIN instance_batiment ib ON re.id_instance_bat = ib.id_instanceBat
                                  WHERE ib.id_batiment = 6 AND ib.camp_instance = '$camp' AND re.stock_fer >= 30 LIMIT 1";
                        $res_e = $mysqli->query($sql_e);

                        if ($res_e && $res_e->num_rows > 0) {
                            $entrep = $res_e->fetch_assoc();
                            $id_e = $entrep['id_instance_bat'];
                            $mysqli->query("UPDATE ressources_entrepot SET stock_fer = stock_fer - 30 WHERE id_instance_bat = '$id_e'");

                            $sql_ins = "INSERT INTO instance_batiment (id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance)
                                        VALUES ('12', 'Convoi', '2500', '2500', '$x_respawn_train', '$y_respawn_train', '$camp', '50')";
                            $mysqli->query($sql_ins);
                            $id_new_train = $mysqli->insert_id;

                            $mysqli->query("UPDATE liaisons_gare SET id_train='$id_new_train' WHERE (id_gare1='$id_gare1_liaison' AND id_gare2='$id_gare2_liaison') OR (id_gare1='$id_gare2_liaison' AND id_gare2='$id_gare1_liaison')");
                            $img = ($camp == '1') ? 'b12b.gif' : 'b12r.gif';
                            $mysqli->query("UPDATE carte SET idPerso_carte='$id_new_train', occupee_carte='1', image_carte='$img' WHERE x_carte='$x_respawn_train' AND y_carte='$y_respawn_train'");

                            $mess = "Train créé (#$id_new_train). 30 Fer déduits de l'entrepôt #$id_e.";
                        } else {
                            $mess_erreur = "Aucun entrepôt n'a 30 fer individuellement.";
                        }
                        $mysqli->query("UNLOCK TABLES");
                    }
                }
            }

            if (isset($_GET['detruire_obstacle'])) {
                $id_obs = (int)$_GET['detruire_obstacle'];
                $res_obs = $mysqli->query("SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_obs' AND id_batiment=1");
                if ($t_obs = $res_obs->fetch_assoc()) {
                    $mysqli->query("UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='".$t_obs['x_instance']."' AND y_carte='".$t_obs['y_instance']."'");
                    $mysqli->query("DELETE FROM instance_batiment WHERE id_instanceBat='$id_obs'");
                    $mess = "Obstacle détruit.";
                }
            }

            if (isset($_GET['detruire_train'])) {
                $id_t = (int)$_GET['detruire_train'];
                $res_t = $mysqli->query("SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_t' AND id_batiment='12' AND camp_instance='$camp'");
                if ($t_t = $res_t->fetch_assoc()) {
                    $mysqli->query("UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='".$t_t['x_instance']."' AND y_carte='".$t_t['y_instance']."'");
                    $mysqli->query("DELETE FROM instance_batiment WHERE id_instanceBat='$id_t'");
                    $mysqli->query("UPDATE liaisons_gare SET id_train=NULL WHERE id_train='$id_t'");
                    $mess = "Train détruit.";
                }
            }

            if (isset($_GET['select_liaison_gare1'], $_GET['select_liaison_gare2'])) {
                $g1 = (int)$_GET['select_liaison_gare1']; $g2 = (int)$_GET['select_liaison_gare2'];
                if($g1 != $g2) {
                    $mysqli->query("INSERT INTO liaisons_gare (id_gare1, id_gare2, id_train, direction) VALUES ('$g1', '$g2', NULL, '$g2')");
                    $mess = "Liaison créée entre les gares #$g1 et #$g2.";
                }
            }

            if (isset($_GET['supprimer_troncon'])) {
                $ids = explode(',', $_GET['supprimer_troncon']);
                $id_g1 = (int)$ids[0]; $id_g2 = (int)$ids[1];
                $mysqli->query("DELETE FROM liaisons_gare WHERE (id_gare1 = '$id_g1' AND id_gare2 = '$id_g2') OR (id_gare1 = '$id_g2' AND id_gare2 = '$id_g1')");
                header("Location: anim_trains.php"); exit();
            }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Animation - Gestion des Trains</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { background-image: url('../public/img/backgrounds/gestionTrains.png') !important; background-size: cover; background-attachment: fixed; background-color: #1a1a2e; color: #e0e0e0; font-size: 0.9rem; }
        .container-main { padding: 30px; margin-top: 30px; background: rgba(0,0,0,0.85); border-radius: 15px; border: 1px solid #444; }
        @keyframes blinker { 50% { opacity: 0; } }
        .badge-flash { animation: blinker 1.5s linear infinite; }
        @keyframes flash-red { 0%, 50%, 100% { opacity: 1; } 25%, 75% { opacity: 0.4; } }
        .badge-flash-danger { animation: flash-red 1.5s infinite; border: 1px solid #ff0000; }
        .table-xs td, .table-xs th { padding: 0.4rem; vertical-align: middle; }
    </style>
</head>
<body>
<div class="container-main">
    <div class="text-center mb-3"><a class="btn btn-outline-secondary btn-sm" href="animation.php">Retour Animation</a></div>
    <h2 class="text-center text-info">Animation - Logistique Ferroviaire (<?php echo $nom_camp; ?>)</h2>

    <div class="alert alert-dark border-secondary shadow-sm mt-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Stocks de Fer (Entrepôts #6)</h5>
            <span class="badge <?php echo ($fer_total_dispo >= 30) ? 'badge-success' : 'badge-danger badge-flash'; ?> px-3 py-2">
                Total Camp : <?php echo $fer_total_dispo; ?> Fer
            </span>
        </div>
        <div class="row">
            <?php
            $res_ent = $mysqli->query("SELECT ib.id_instanceBat, re.stock_fer FROM ressources_entrepot re JOIN instance_batiment ib ON re.id_instance_bat = ib.id_instanceBat WHERE ib.id_batiment = 6 AND ib.camp_instance = '$camp'");
            while($ent = $res_ent->fetch_assoc()) {
                $warn = ($ent['stock_fer'] < 30);
                ?>
                <div class="col-md-3 mb-2">
                    <div class="p-2 border rounded <?php echo $warn ? "border-warning" : "border-success"; ?>" style="background: rgba(255,255,255,0.05);">
                        <small class="text-muted">#<?php echo $ent['id_instanceBat']; ?></small> :
                        <strong class="<?php echo $warn ? "text-warning" : "text-success"; ?>"><?php echo $ent['stock_fer']; ?> Fer</strong>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php if($mess) echo "<div class='alert alert-success'>$mess</div>"; ?>
    <?php if($mess_erreur) echo "<div class='alert alert-danger'>$mess_erreur</div>"; ?>

    <div class="card bg-dark text-white mt-4 border-secondary">
        <div class="card-header border-secondary bg-secondary text-white"><h5>Liaisons Actives</h5></div>
        <div class="table-responsive">
            <table class="table table-dark table-striped table-hover mb-0 table-xs">
                <thead>
                    <tr class="text-muted small">
                        <th class="text-center">Train</th>
                        <th class="text-center">Gare Départ</th>
                        <th class="text-center">Gare Arrivée</th>
                        <th class="text-center">Position Actuelle</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Gestion</th>
                    </tr>
                </thead>
         <tbody>
    <?php
    $sql = "SELECT DISTINCT lg.id_gare1, lg.id_gare2, lg.id_train, lg.direction FROM liaisons_gare lg JOIN instance_batiment ib ON (lg.id_gare1 = ib.id_instanceBat OR lg.id_gare2 = ib.id_instanceBat) WHERE ib.camp_instance='$camp'";
    $res = $mysqli->query($sql);
    while ($t = $res->fetch_assoc()) {
        $id_gare1 = $t['id_gare1'];
        $id_gare2 = $t['id_gare2'];
        $id_train = $t['id_train'];

        $t_g1 = $mysqli->query("SELECT nom_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_gare1'")->fetch_assoc();
        $t_g2 = $mysqli->query("SELECT nom_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_gare2'")->fetch_assoc();
        $troncon_invalide = (!$t_g1 || !$t_g2);

        echo "<tr><td class='text-center'>";
        if ($id_train) {
            $res_t = $mysqli->query("SELECT pv_instance, pvMax_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_train'");
            $t_t = $res_t->fetch_assoc();
            if ($t_t) {
                $p_train = round(($t_t['pv_instance'] / $t_t['pvMax_instance']) * 100);
                echo "<b>#$id_train</b><br><div class='progress' style='height:4px; width:40px; margin:auto;'><div class='progress-bar bg-info' style='width:$p_train%'></div></div>";
            } else { echo "<span class='text-danger'>Détruit</span>"; }
        } else { echo "<i class='text-muted'>Aucun</i>"; }
        echo "</td>";

        echo "<td class='text-center'>".($t_g1['nom_instance'] ?? '???')." <small>(#$id_gare1)</small></td>";
        echo "<td class='text-center'>".($t_g2['nom_instance'] ?? '???')." <small>(#$id_gare2)</small></td>";

        // --- DEBUT DE LA CORRECTION POUR L'AFFICHAGE DE LA DESTINATION ---
        echo "<td class='text-center small'>";
        if ($id_train && isset($t_t)) {
            // Déterminer l'ID de la gare cible selon la direction
            $id_cible = ($t['direction'] == $id_gare1) ? $id_gare1 : $id_gare2;

            // Récupérer le nom de l'instance correspondante
            $nom_cible = ($t['direction'] == $id_gare1) ? ($t_g1['nom_instance'] ?? "") : ($t_g2['nom_instance'] ?? "");

            // Si le nom est vide ou non défini, on affiche l'ID par défaut
            $dest_display = !empty($nom_cible) ? $nom_cible : "#".$id_cible;

            echo "<b>".$t_t['x_instance']."/".$t_t['y_instance']."</b><br>";
            echo "<small class='text-info'>Vers $dest_display</small>";
        } else {
            echo "-";
        }
        echo "</td>";
 
        echo "<td class='text-center'>";
        if ($id_train && isset($t_t)) {
            $last_ev = $mysqli->query("SELECT phrase_evenement FROM evenement WHERE IDActeur_evenement = '$id_train' ORDER BY date_evenement DESC LIMIT 1")->fetch_assoc();
            if ($last_ev && strpos($last_ev['phrase_evenement'], 'bloqué') !== false) echo '<span class="badge badge-danger badge-flash-danger">🚫 BLOQUÉ</span>';
            else echo '<span class="badge badge-success">🚄 EN ROUTE</span>';
        } elseif ($troncon_invalide) echo '<span class="badge badge-danger">Liaison Rompue</span>';
        else echo '<span class="badge badge-warning">Ligne inactive</span>';
        echo "</td>";

        echo "<td class='text-center'>";
        // Suppression liaison
        echo '<a href="anim_trains.php?supprimer_troncon='.$id_gare1.','.$id_gare2.'" class="btn btn-xs btn-outline-danger p-0 mb-1 btn-block" style="font-size:10px;">Supprimer Liaison</a>';

        if (!$id_train && !$troncon_invalide) {
            // LOGIQUE BOUTON DYNAMIQUE
            if ($fer_total_dispo >= 30) {
                echo '<form action="anim_trains.php" method="get" class="border border-success p-1 rounded">';
                echo '<input type="hidden" name="creer_train_liaison" value="'.$id_gare1.','.$id_gare2.'">';
                echo '<input type="text" name="creer_train_pos" class="form-control form-control-sm mb-1" placeholder="X,Y" value="'.$t_g1['x_instance'].','.$t_g1['y_instance'].'" style="font-size:10px; height:18px;">';
                echo '<button type="submit" class="btn btn-xs btn-success btn-block" style="font-size:10px;">Construire (-30)</button></form>';
            } else {
                echo '<div class="border border-secondary p-1 rounded" style="opacity:0.6;">';
                echo '<input type="text" class="form-control form-control-sm mb-1" value="Manque Fer" disabled style="font-size:10px; height:18px; background:#222; color:#777;">';
                echo '<button class="btn btn-xs btn-secondary btn-block" disabled style="font-size:10px;">Fer insuffisant</button></div>';
            }
        } elseif ($id_train) {
            echo "<a href='anim_trains.php?detruire_train=$id_train' class='btn btn-xs btn-warning btn-block' style='font-size:10px;'>Supprimer Train</a>";
        }
        echo "</td></tr>";
    } ?>
</tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card bg-dark text-white mt-4 border-info">
                <div class="card-header border-info bg-info text-dark font-weight-bold">Suggestions de liaisons (Gares non reliées)</div>
                <div class="card-body p-0">
                    <table class="table table-dark table-sm table-hover mb-0">
                        <tbody>
                        <?php
                        $res_gares = $mysqli->query("SELECT id_instanceBat, nom_instance FROM instance_batiment WHERE id_batiment='11' AND camp_instance='$camp' ORDER BY nom_instance ASC");
                        $mes_gares = []; while($rg = $res_gares->fetch_assoc()) { $mes_gares[] = $rg; }
                        $count_sug = 0;
                        foreach ($mes_gares as $i => $gareA) {
                            foreach ($mes_gares as $j => $gareB) {
                                if ($i >= $j) continue;
                                $idA = $gareA['id_instanceBat']; $idB = $gareB['id_instanceBat'];
                                $check = $mysqli->query("SELECT id_gare1 FROM liaisons_gare WHERE (id_gare1='$idA' AND id_gare2='$idB') OR (id_gare1='$idB' AND id_gare2='$idA')");
                                if ($check->num_rows == 0) {
                                    $count_sug++;
                                    echo "<tr><td class='p-2'>".$gareA['nom_instance']." ↔ ".$gareB['nom_instance']."</td>";
                                    echo "<td class='text-right p-2'><a href='anim_trains.php?select_liaison_gare1=$idA&select_liaison_gare2=$idB' class='btn btn-outline-info btn-xs' style='font-size:10px;'>Créer la liaison</a></td></tr>";
                                }
                            }
                        }
                        if($count_sug == 0) echo "<tr><td class='text-center text-muted p-3'>Toutes les gares sont interconnectées.</td></tr>";
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card bg-dark text-white mt-4 border-danger">
                <div class="card-header border-danger bg-danger text-white">Obstacles sur rails</div>
                <div class="card-body p-0">
                    <table class="table table-dark table-sm mb-0">
                        <?php
                        $sql_obs = "SELECT ib.id_instanceBat, ib.x_instance, ib.y_instance, b.nom_batiment
                                   FROM instance_batiment ib
                                   JOIN batiment b ON ib.id_batiment = b.id_batiment
                                   JOIN carte c ON (ib.x_instance = c.x_carte AND ib.y_instance = c.y_carte)
                                   WHERE ib.id_batiment = 1 AND (c.fond_carte LIKE 'rail%')";
                        $res_obs = $mysqli->query($sql_obs);
                        if ($res_obs->num_rows > 0) {
                            while ($to = $res_obs->fetch_assoc()) {
                                echo "<tr><td class='p-2 small text-warning'>⚠️ ".$to['nom_batiment']." #".$to['id_instanceBat']." (".$to['x_instance']."/".$to['y_instance'].")</td>";
                                echo "<td class='text-right p-2'><a href='anim_trains.php?detruire_obstacle=".$to['id_instanceBat']."' class='btn btn-danger btn-xs' style='font-size:10px;'>Dégager</a></td></tr>";
                            }
                        } else echo "<tr><td class='text-center text-muted p-3'>Voies ferrées dégagées.</td></tr>";
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
        } // Fin anim_perso
    } // Fin isset id_perso
} // Fin admin/dispo
?>