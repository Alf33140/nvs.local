<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once("f_action.php");

$mysqli = db_connexion();

include('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path)) {
    include($phpbb_root_path . 'config.php');
}

$mess_err = "";
$mess = "";

if (isset($_SESSION["id_perso"])) {

    $id_perso = $_SESSION['id_perso'];
    $admin = admin_perso($mysqli, $id_perso);
    $anim = anim_perso($mysqli, $id_perso);

    if ($anim || $admin) {

        if (isset($_POST['select_batiment']) && $_POST['select_batiment'] != ""
            && isset($_POST['coord_x_placement']) && trim($_POST['coord_x_placement']) != ""
            && isset($_POST['coord_y_placement']) && trim($_POST['coord_y_placement']) != "") {

            $id_bat   = $_POST['select_batiment'];
            $x_bat    = (int)$_POST['coord_x_placement'];
            $y_bat    = (int)$_POST['coord_y_placement'];
            $camp_bat = $_POST['select_camp'];
            $verif    = $_POST['select_verifications'];

            // --- INITIALISATION POUR ÉVITER LES WARNINGS (ENTREPOT ID 6) ---
            // On définit les variables attendues par f_action.php pour éviter les erreurs "Undefined variable"
            if ($id_bat == 6) {
                $bois_requis = 0;
                $or_requis   = 0;
                $fer_requis  = 0;
            }
            // ---------------------------------------------------------------

            $couleur_clan_bat = couleur_clan($camp_bat);

            // 1. Récupération des infos du bâtiment type
            $sql = "SELECT nom_batiment, taille_batiment, image_prefix, pvMax_batiment FROM batiment WHERE id_batiment='$id_bat'";
            $res = $mysqli->query($sql);
            $tb = $res->fetch_assoc();

            $nom_bat      = $tb["nom_batiment"];
            $taille_bat   = $tb["taille_batiment"];
            $img_prefix   = $tb["image_prefix"];
            $pvMax_bat    = $tb["pvMax_batiment"];

            // 2. Vérification position carte
            $sql_max = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
            $res_max = $mysqli->query($sql_max);
            $t_max = $res_max->fetch_assoc();

            $rayon = floor($taille_bat / 2);

            if ($x_bat - $rayon < 0 || $x_bat + $rayon > $t_max['x_max'] || $y_bat - $rayon < 0 || $y_bat + $rayon > $t_max['y_max']) {
                $mess_err = "Le bâtiment dépasse les limites de la carte.";
            } else {

                $sql_f = "SELECT fond_carte FROM carte WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
                $res_f = $mysqli->query($sql_f);
                $t_f = $res_f->fetch_assoc();
                $fond_carte = $t_f['fond_carte'];

                $verif_fond_carte = true;

                // Contraintes de terrain
                if ($id_bat == 1) { // Barricade
                    $rails = array('rail.gif', 'rail_1.gif', 'rail_2.gif', 'rail_3.gif', 'rail_4.gif', 'rail_5.gif', 'rail_7.gif', 'railP.gif', '1.gif');
                    if (!in_array($fond_carte, $rails)) $verif_fond_carte = false;
                } else if ($id_bat == 5) { // Pont
                    if ($fond_carte != '8.gif' && $fond_carte != '9.gif') $verif_fond_carte = false;
                } else if ($id_bat == 15 || $id_bat == 17) { // Mines
                    if ($fond_carte != '3.gif') $verif_fond_carte = false;
                } else if ($id_bat == 16) { // Scierie
                    if ($fond_carte != '7.gif') $verif_fond_carte = false;
                } else {
                    if ($fond_carte != '1.gif' && $fond_carte != '1.png') $verif_fond_carte = false;
                }

                if ($verif_fond_carte || $verif == 1) {

                    $aut_gc      = ($verif == 1) ? true : verif_contraintes_construction($mysqli, $id_bat, $camp_bat, $x_bat, $y_bat);
                    $aut_ennemis = ($verif == 1) ? true : verif_contraintes_construction_ennemis($mysqli, $id_bat, $camp_bat, $x_bat, $y_bat);
                    $aut_bats    = ($verif == 1) ? true : verif_contraintes_construction_bat($mysqli, $id_bat, $camp_bat, $x_bat, $y_bat);

                    $aut_taille = true;
                    $sql_z = "SELECT occupee_carte FROM carte WHERE x_carte BETWEEN ".($x_bat - $rayon)." AND ".($x_bat + $rayon)." AND y_carte BETWEEN ".($y_bat - $rayon)." AND ".($y_bat + $rayon);
                    $res_z = $mysqli->query($sql_z);
                    while($tz = $res_z->fetch_assoc()) {
                        if($tz['occupee_carte']) { $aut_taille = false; break; }
                    }

                    if ($aut_gc && $aut_ennemis && $aut_bats && $aut_taille) {

                        // 3. Création de l'instance
                        $sql_ins = "INSERT INTO instance_batiment (niveau_instance, id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, camp_origine_instance, contenance_instance)
                                    VALUES ('1', '$id_bat', '', '$pvMax_bat', '$pvMax_bat', '$x_bat', '$y_bat', '$camp_bat', '$camp_bat', '10')";
                        $mysqli->query($sql_ins);
                        $id_i_bat = $mysqli->insert_id;

                        // Initialisation des ressources de l'entrepôt avec stock_fer
                        $mysqli->query("INSERT INTO ressources_entrepot (id_instance_bat, stock_or, stock_bois, stock_fer) VALUES ('$id_i_bat', 0, 0, 0)");

                        // 4. Mise à jour de la carte (Gestion Spéciale Mines & Scieries)
                        if ($id_bat == 5) {
                            $mysqli->query("UPDATE carte SET occupee_carte='0', idPerso_carte='$id_i_bat', save_info_carte='$id_i_bat' WHERE x_carte='$x_bat' AND y_carte='$y_bat'");
                        } else {
                            $idx_img = 1;
                            $dossier_clan = ($camp_bat == 1) ? "nord" : "sud";

                            for ($y = $y_bat - $rayon; $y <= $y_bat + $rayon; $y++) {
                                for ($x = $x_bat - $rayon; $x <= $x_bat + $rayon; $x++) {

                                    if ($taille_bat > 1) {
                                        $img_nom = $img_prefix . $camp_bat . "_" . $idx_img . ".png";
                                        $chemin_final = "public/img/buildings/" . $dossier_clan . "/" . $img_nom;
                                    } else {
                                        $img_nom = $img_prefix . $camp_bat . ".png";
                                        $chemin_final = "public/img/buildings/" . $dossier_clan . "/" . $img_nom;
                                    }

                                    $mysqli->query("UPDATE carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$chemin_final' WHERE x_carte='$x' AND y_carte='$y'");
                                    $idx_img++;
                                }
                            }
                        }

                        // 5. Logiques Canons / Gares
                        if ($id_bat == '8') {
                            for ($ox = -1; $ox <= 1; $ox += 2) {
                                for ($oy = -1; $oy <= 1; $oy += 2) {
                                    $mysqli->query("INSERT INTO instance_batiment_canon (id_instanceBat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat+$ox, $y_bat+$oy, '$camp_bat')");
                                }
                            }
                        } else if ($id_bat == '9') {
                            $offs = [[-2,2], [-2,0], [-2,-2], [2,2], [2,0], [2,-2]];
                            foreach($offs as $o) {
                                $mysqli->query("INSERT INTO instance_batiment_canon (id_instanceBat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat+$o[0], $y_bat+$o[1], '$camp_bat')");
                            }
                        }

                        $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement) VALUES ($id_i_bat, '$nom_bat', 'a été construit par un animateur', NOW())");
                        $mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'anim_creer_batiment.php', 'creation batiment', 'Batiment $nom_bat [$id_i_bat] en $x_bat/$y_bat')");

                        $mess = "Le bâtiment <b>$nom_bat</b> a été posé avec succès.";
                    } else {
                        $mess_err = "Échec : La zone est déjà occupée ou les contraintes ne sont pas respectées.";
                    }
                } else {
                    $mess_err = "Le terrain ($fond_carte) n'est pas adapté pour ce bâtiment.";
                }
            }
        }
    } else { $mess_err = "Accès non autorisé."; }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Administration - Bâtiments</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h2>Outil de Création de Bâtiments</h2>
            </div>
            <div class="card-body">
                <?php if($mess_err) echo "<div class='alert alert-danger'>$mess_err</div>"; ?>
                <?php if($mess) echo "<div class='alert alert-success'>$mess</div>"; ?>

                <form method='POST' action='anim_creer_batiment.php' class="mt-3">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Type de bâtiment</label>
                            <select name="select_batiment" class="form-control">
                                <?php
                                $sql_b = "SELECT id_batiment, nom_batiment, taille_batiment FROM batiment ORDER BY id_batiment ASC";
                                $res_b = $mysqli->query($sql_b);
                                while($tb = $res_b->fetch_assoc()) {
                                    echo "<option value='".$tb['id_batiment']."'>".$tb['nom_batiment']." (".$tb['taille_batiment']."x".$tb['taille_batiment'].")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Camp</label>
                            <select name="select_camp" class="form-control">
                                <option value='1'>Nord (Bleu)</option>
                                <option value='2'>Sud (Rouge)</option>
                                <option value='3'>Neutre (Vert)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Vérifications</label>
                            <select name="select_verifications" class="form-control">
                                <option value='2'>Appliquer les règles (Distance/Terrain)</option>
                                <option value='1'>Forcer (Ignorer contraintes)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Coordonnée X (Centre)</label>
                            <input type='number' name='coord_x_placement' class="form-control" placeholder='X' required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Coordonnée Y (Centre)</label>
                            <input type='number' name='coord_y_placement' class="form-control" placeholder='Y' required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-block mt-3">Générer le bâtiment sur la carte</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a class='btn btn-outline-secondary' href='admin_batiments.php'>Gérer les instances</a>
                <a class='btn btn-link text-info' href='jouer.php'>Retour au Jeu</a>
            </div>
        </div>
    </div>
</body>
</html>