<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
    include ($phpbb_root_path .'config.php');
}

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){

    if (isset($_SESSION["id_perso"])) {

        $id = $_SESSION["id_perso"];

        if (anim_perso($mysqli, $id)) {

            // Récupération du camp de l'animateur
            $sql = "SELECT clan FROM perso WHERE id_perso='$id'";
            $res = $mysqli->query($sql);
            $t = $res->fetch_assoc();

            $camp = $t['clan'];

            if ($camp == '1') {
                $nom_camp = 'Nord';
                $b_camp = 'b';
                $couleur_camp = 'blue';
            }
            else if ($camp == '2') {
                $nom_camp = 'Sud';
                $b_camp = 'r';
                $couleur_camp = 'red';
            }
            else if ($camp == '3') {
                $nom_camp = 'Indien';
                $b_camp = 'g';
                $couleur_camp = 'green';
            }

            $mess = "";
            $mess_erreur = "";

            // 1. Création pénitencier
            if (isset($_POST['coord_x_penitencier']) && $_POST['coord_x_penitencier'] != ''
                    && isset($_POST['coord_y_penitencier']) && $_POST['coord_y_penitencier'] != '') {

                $x_penitencier = $_POST['coord_x_penitencier'];
                $y_penitencier = $_POST['coord_y_penitencier'];

                $verif_x = preg_match("#^[0-9]*[0-9]$#i","$x_penitencier");
                $verif_y = preg_match("#^[0-9]*[0-9]$#i","$y_penitencier");

                $sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
                $res = $mysqli->query($sql);
                $t = $res->fetch_assoc();

                $X_MAX = $t['x_max'];
                $Y_MAX = $t['y_max'];

                if ($verif_x && $verif_y && in_map($x_penitencier, $y_penitencier, $X_MAX, $Y_MAX)) {
                    $autorisation_construction_taille = true;
                    $taille_bat = 3;
                    $taille_search = floor($taille_bat / 2);

                    $sql = "SELECT occupee_carte FROM carte
                            WHERE x_carte <= $x_penitencier + $taille_search AND x_carte >= $x_penitencier - $taille_search
                            AND y_carte <= $y_penitencier + $taille_search AND y_carte >= $y_penitencier - $taille_search";
                    $res = $mysqli->query($sql);

                    while ($t = $res->fetch_assoc()) {
                        if ($t["occupee_carte"]) {
                            $autorisation_construction_taille = false;
                        }
                    }

                    if ($autorisation_construction_taille) {
                        $sql = "INSERT INTO instance_batiment (niveau_instance, id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance)
                                VALUES ('1', '10', '', '15000', '15000', '$x_penitencier', '$y_penitencier', '$camp', '50')";
                        $mysqli->query($sql);
                        $id_i_bat = $mysqli->insert_id;

                        $i = 1;
                        for ($x = $x_penitencier - $taille_search; $x <= $x_penitencier + $taille_search; $x++) {
                            for ($y = $y_penitencier - $taille_search; $y <= $y_penitencier + $taille_search; $y++) {
                                $img = 'jail_'.$camp.'_'.$i.'.png';
                                $sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img' WHERE x_carte='$x' AND y_carte='$y'";
                                $mysqli->query($sql);
                                $i++;
                            }
                        }
                        $mess = "Pénitencier créé avec succès.";
                    } else {
                        $mess_erreur = "Zone occupée.";
                    }
                }
            }

            // Récupération infos pénitencier existant
            $sql_peni = "SELECT id_instanceBat, x_instance, y_instance FROM instance_batiment WHERE id_batiment=10 AND camp_instance='$camp'";
            $res_peni = $mysqli->query($sql_peni);
            $verif_penitencier = $res_peni->num_rows;
            $t_peni = $res_peni->fetch_assoc();

            $id_penitencier = $t_peni['id_instanceBat'] ?? null;
            $x_penitencier  = $t_peni['x_instance'] ?? null;
            $y_penitencier  = $t_peni['y_instance'] ?? null;

            // 1. Action : Envoyer au bagne
            if (isset($_POST['liste_perso_contact_penitencier'])) {
                $id_perso_envoi = $_POST['liste_perso_contact_penitencier'];

                if (preg_match("#^[0-9]+$#", $id_perso_envoi) && $id_penitencier) {
                    $sql = "SELECT nom_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$id_perso_envoi'";
                    $res = $mysqli->query($sql);
                    $t = $res->fetch_assoc();

                    if ($t) {
                        $nom_p = $t['nom_perso'];
                        $x_old = $t['x_perso'];
                        $y_old = $t['y_perso'];
                        $cp    = $t['clan'];
                        $color = ($cp==1)?'blue':(($cp==2)?'red':'green');

                        // Déplacements et base de données
                        $mysqli->query("DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_envoi'");
                        $mysqli->query("UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_old' AND y_carte='$y_old'");
                        $mysqli->query("UPDATE perso SET x_perso='$x_penitencier', y_perso='$y_penitencier' WHERE id_perso='$id_perso_envoi'");
                        $mysqli->query("INSERT INTO perso_in_batiment (id_perso, id_instanceBat) VALUES ('$id_perso_envoi', '$id_penitencier')");
                        $mysqli->query("DELETE FROM perso_bagne WHERE id_perso='$id_perso_envoi'");
                        $mysqli->query("INSERT INTO perso_bagne (id_perso, date_debut, duree) VALUES ('$id_perso_envoi', NOW(), '3')");

                        // Log Évènement
                        $nom_act = addslashes("<font color=$color><b>$nom_p</b></font>");
                        $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement)
                                        VALUES ('$id_perso_envoi', '$nom_act', 'a été envoyé au Pénitencier', NOW())");

                        $mess = "Le perso $nom_p a été envoyé au Pénitencier.";
                    }
                }
            }

            // Gestion des punitions via Bureau du Marshall
            $id_perso_puni = $_POST['liste_perso_punition'] ?? $_GET['id_perso'] ?? null;

            if ($id_perso_puni && preg_match("#^[0-9]+$#", $id_perso_puni)) {
                $sql = "SELECT nom_perso, clan, or_perso, xp_perso, pc_perso, chef, x_perso, y_perso FROM perso WHERE id_perso='$id_perso_puni'";
                $res = $mysqli->query($sql);
                $t_puni = $res->fetch_assoc();

                if ($t_puni) {
                    $nom_p_puni = $t_puni['nom_perso'];
                    $color_puni = ($t_puni['clan'] == 1) ? 'blue' : (($t_puni['clan'] == 2) ? 'red' : 'green');
                    $nom_act = addslashes("<font color=$color_puni><b>$nom_p_puni</b></font>");

                    // ACTION : ENVOI AU BAGNE
                    if (isset($_GET['bagne']) && $_GET['bagne'] == 'ok' && isset($_GET['duree'])) {
                        $duree = (int)$_GET['duree'];
                        if ($id_penitencier) {
                            $x_old = $t_puni['x_perso']; $y_old = $t_puni['y_perso'];
                            $mysqli->query("DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_puni'");
                            $mysqli->query("UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_old' AND y_carte='$y_old'");
                            $mysqli->query("UPDATE perso SET x_perso='$x_penitencier', y_perso='$y_penitencier' WHERE id_perso='$id_perso_puni'");
                            $mysqli->query("INSERT INTO perso_in_batiment (id_perso, id_instanceBat) VALUES ('$id_perso_puni', '$id_penitencier')");
                            $mysqli->query("DELETE FROM perso_bagne WHERE id_perso='$id_perso_puni'");
                            $mysqli->query("INSERT INTO perso_bagne (id_perso, date_debut, duree) VALUES ('$id_perso_puni', NOW(), '$duree')");
                            $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement) VALUES ('$id_perso_puni', '$nom_act', 'a été envoyé au Pénitencier pour $duree jours', NOW())");
                            $mess = "$nom_p_puni envoyé au bagne ($duree j).";
                        }
                    }

                    // ACTION : AMENDE
                    if (isset($_GET['amende'])) {
                        $montant = ($_GET['amende'] == 'all') ? $t_puni['or_perso'] : (int)$_GET['amende'];
                        $mysqli->query("UPDATE perso SET or_perso = or_perso - $montant WHERE id_perso='$id_perso_puni'");
                        $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement) VALUES ('$id_perso_puni', '$nom_act', 'a payé une amende de $montant thunes', NOW())");
                        $mess = "Amende de $montant thunes infligée.";
                    }

                    // ACTION : XP
                    if (isset($_GET['xp'])) {
                        $montant = ($_GET['xp'] == 'all') ? $t_puni['xp_perso'] : (int)$_GET['xp'];
                        $mysqli->query("UPDATE perso SET xp_perso = GREATEST(0, xp_perso - $montant) WHERE id_perso='$id_perso_puni'");
                        $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement) VALUES ('$id_perso_puni', '$nom_act', 'a perdu $montant points d\'XP', NOW())");
                        $mess = "Retrait de XP effectué.";
                    }
                }
            }

            // 2. Action : Relâcher CORRIGÉE
            if (isset($_GET['relacher'])) {
                $id_rel = $_GET['relacher'];

                $sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_rel'";
                $res_p = $mysqli->query($sql_p);
                $t_p = $res_p->fetch_assoc();
                $nom_p_rel = $t_p['nom_perso'] ?? 'Inconnu';
                $clan_rel = $t_p['clan'];
                $color_rel = ($clan_rel==1)?'blue':(($clan_rel==2)?'red':'green');

                // On cherche un Fort (id_batiment 9) pour le camp
                $sql_fort = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_batiment=9 AND camp_instance='$camp' LIMIT 1";
                $res_fort = $mysqli->query($sql_fort);

                if ($res_fort->num_rows > 0) {
                    $t_fort = $res_fort->fetch_assoc();
                    $x_f = $t_fort['x_instance'];
                    $y_f = $t_fort['y_instance'];

                    // 1. On le retire des instances (Bâtiment et Bagne)
                    $mysqli->query("DELETE FROM perso_in_batiment WHERE id_perso='$id_rel'");
                    $mysqli->query("DELETE FROM perso_bagne WHERE id_perso='$id_rel'");

                    // 2. On met à jour sa position et ses PV
                    $mysqli->query("UPDATE perso SET x_perso='$x_f', y_perso='$y_f', pv_perso=pvMax_perso WHERE id_perso='$id_rel'");

                    // 3. CORRECTION : On le réinscrit physiquement sur la CARTE (pour qu'il soit visible et puisse bouger)
                    $mysqli->query("UPDATE carte SET occupee_carte='1', idPerso_carte='$id_rel', image_carte='v$clan_rel.pnj' WHERE x_carte='$x_f' AND y_carte='$y_f'");

                    // Log Évènement
                    $nom_act = addslashes("<font color=$color_rel><b>$nom_p_rel</b></font>");
                    $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement)
                                    VALUES ('$id_rel', '$nom_act', 'a purgé sa peine et a été transféré au Fort', NOW())");

                    $mess = "Le perso $nom_p_rel a été libéré et transféré au Fort ($x_f/$y_f).";
                } else {
                    $mess_erreur = "Aucun Fort trouvé pour accueillir le prisonnier libéré.";
                }
            }

            // 3. Action : Modifier Peine
            if (isset($_GET['ajouter'])) {
                $id_add = $_GET['ajouter'];
                $sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_add'";
                $res_p = $mysqli->query($sql_p);
                $t_p = $res_p->fetch_assoc();
                $nom_p_add = $t_p['nom_perso'];
                $color_add = ($t_p['clan']==1)?'blue':(($t_p['clan']==2)?'red':'green');

                $mysqli->query("UPDATE perso_bagne SET duree = duree + 1 WHERE id_perso='$id_add'");
                $nom_act = addslashes("<font color=$color_add><b>$nom_p_add</b></font>");
                $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement)
                                VALUES ('$id_add', '$nom_act', 'a vu sa peine allongée de 1 jour pour problème de discipline', NOW())");
                $mess = "La peine a été allongée pour $nom_p_add.";
            }

            if (isset($_GET['retirer'])) {
                $id_rem = $_GET['retirer'];
                $sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_rem'";
                $res_p = $mysqli->query($sql_p);
                $t_p = $res_p->fetch_assoc();
                $nom_p_rem = $t_p['nom_perso'];
                $color_rem = ($t_p['clan']==1)?'blue':(($t_p['clan']==2)?'red':'green');

                $mysqli->query("UPDATE perso_bagne SET duree = GREATEST(1, duree - 1) WHERE id_perso='$id_rem'");
                $nom_act = addslashes("<font color=$color_rem><b>$nom_p_rem</b></font>");
                $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement)
                                VALUES ('$id_rem', '$nom_act', 'a bénéficié d\'une réduction de peine de 1 jour pour bonne conduite', NOW())");
                $mess = "La peine a été réduite pour $nom_p_rem.";
            }
        ?>

<!DOCTYPE HTML>
<html>
    <head>
        <title>Nord VS Sud - Bureau du Marshall</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <style>
            body {
                background-image: url('/public/img/backgrounds/penitencier.jpg') !important;
                background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;
            }
            h1,h2, .display-4 { color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); }
            .card { background-color: rgba(255, 255, 255, 0.9); }
            .container-main { background-color: rgba(0, 0, 0, 0.5); padding: 20px; border-radius: 10px; color: white; }
        </style>
    </head>
    <body class="bg-light">
        <div class="container mt-4">
            <div class="text-center">
                <h2>Animation - Gestion du pénitencier (<?php echo $nom_camp; ?>)</h2>
                <a class="btn btn-primary my-3" href="animation.php">Retour page principale</a>
            </div>

            <?php if($mess) echo "<div class='alert alert-success'>$mess</div>"; ?>
            <?php if($mess_erreur) echo "<div class='alert alert-danger'>$mess_erreur</div>"; ?>

            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php if (!$verif_penitencier): ?>
                        <form method='POST' class="form-inline justify-content-center">
                            <input type='text' class="form-control mr-2" placeholder='X' name='coord_x_penitencier' required>
                            <input type='text' class="form-control mr-2" placeholder='Y' name='coord_y_penitencier' required>
                            <button type='submit' class='btn btn-danger'>Construire le Pénitencier</button>
                        </form>
                    <?php else: ?>
                        <h5 class="card-title">Localisation : <strong><?php echo "$x_penitencier / $y_penitencier"; ?></strong></h5>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($verif_penitencier): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">Transférer une unité au bagne</div>
                <div class="card-body">
                    <form method='POST'>
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-9">
                                <label>Choisir un personnage du camp :</label>
                               <select class="form-control" name='liste_perso_contact_penitencier'>
                                    <?php
                                    $sql_list = "SELECT id_perso, nom_perso, x_perso, y_perso FROM perso WHERE clan = '$camp' ORDER BY id_perso ASC";
                                    $res_list = $mysqli->query($sql_list);
                                    while ($tp = $res_list->fetch_assoc()) {
                                        echo "<option value='".$tp['id_perso']."'>[".$tp['id_perso']."] ".$tp['nom_perso']." - Pos: ".$tp['x_perso']."/".$tp['y_perso']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <button type="submit" class="btn btn-block btn-warning">Envoyer au Pénitencier</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">Prisonniers actuels</div>
                <div class="table-responsive">
                    <table class='table table-striped mb-0'>
                        <thead class="thead-light">
                            <tr>
                                <th>Perso</th>
                                <th>Entrée</th>
                                <th>Peine</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($id_penitencier) {
                                $sql_jail = "SELECT p.id_perso, p.nom_perso, pb.date_debut, pb.duree
                                             FROM perso p, perso_in_batiment pib, perso_bagne pb
                                             WHERE p.id_perso = pib.id_perso AND p.id_perso = pb.id_perso
                                             AND pib.id_instanceBat = $id_penitencier";
                                $res_jail = $mysqli->query($sql_jail);
                                if($res_jail->num_rows > 0) {
                                    while ($tj = $res_jail->fetch_assoc()) {
                                        echo "<tr>
                                            <td><b>".$tj['nom_perso']."</b> [".$tj['id_perso']."]</td>
                                            <td>".$tj['date_debut']."</td>
                                            <td><span class='badge badge-info'>".$tj['duree']." jours</span></td>
                                            <td class='text-center'>
                                                <a class='btn btn-sm btn-outline-secondary' href='anim_penitencier.php?id_perso=".$tj['id_perso']."&ajouter=".$tj['id_perso']."'>+1j</a>
                                                <a class='btn btn-sm btn-outline-secondary' href='anim_penitencier.php?id_perso=".$tj['id_perso']."&retirer=".$tj['id_perso']."'>-1j</a>
                                                <a class='btn btn-sm btn-success' href='anim_penitencier.php?relacher=".$tj['id_perso']."'>Relâcher au Fort</a>
                                            </td>
                                        </tr>";
                                    }
                                } else { echo "<tr><td colspan='4' class='text-center'><i>Le pénitencier est vide</i></td></tr>"; }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center mb-5 mt-5">
                <hr style="border-top: 3px double #8c8b8b;">
                <h1 class="display-4 font-weight-bold">🏛️ BUREAU DU MARSHALL</h1>
            </div>

            <div class="row mb-4">
                <div class="col-md-4 offset-md-4">
                    <form method='POST' action='anim_penitencier.php'>
                        <label class="text-white">Punir le perso : </label>
                        <select class="form-control form-control-lg border-primary" name='liste_perso_punition' onchange="this.form.submit()">
                            <option value="">-- Choisir un perso --</option>
                            <?php
                            $sql_l = "SELECT id_perso, nom_perso FROM perso WHERE clan='$camp' ORDER BY nom_perso ASC";
                            $res_l = $mysqli->query($sql_l);
                            while ($tl = $res_l->fetch_assoc()) {
                                $s = ($id_perso_puni == $tl['id_perso']) ? "selected" : "";
                                echo "<option value='".$tl['id_perso']."' $s>".$tl['nom_perso']." [".$tl['id_perso']."]</option>";
                            }
                            ?>
                        </select>
                    </form>
                </div>
            </div>

            <?php if ($id_perso_puni && isset($t_puni)): ?>
            <div class="row">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-info h-100 shadow">
                        <div class="card-header bg-info text-white text-center"><h5>💰 Peines Mineures</h5></div>
                        <div class="card-body text-center">
                            <p class="lead">Fortune : <strong><?php echo $t_puni['or_perso']; ?></strong> thunes</p>
                            <div class="list-group">
                                <a href="anim_penitencier.php?id_perso=<?php echo $id_perso_puni; ?>&amende=5" class="list-group-item list-group-item-action">Amende 5 thunes</a>
                                <a href="anim_penitencier.php?id_perso=<?php echo $id_perso_puni; ?>&amende=10" class="list-group-item list-group-item-action">Amende 10 thunes</a>
                                <a href="anim_penitencier.php?id_perso=<?php echo $id_perso_puni; ?>&amende=all" class="list-group-item list-group-item-action list-group-item-danger font-weight-bold">Confisquer tout l'or</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-warning h-100 shadow">
                        <div class="card-header bg-warning text-dark text-center"><h5>📉 Peines Intermédiaires</h5></div>
                        <div class="card-body text-center">
                            <p>XP : <strong><?php echo $t_puni['xp_perso']; ?></strong></p>
                            <div class="btn-group-vertical w-100">
                                <a href="anim_penitencier.php?id_perso=<?php echo $id_perso_puni; ?>&xp=20" class="btn btn-outline-warning mb-2 text-dark">Retirer 20 XP</a>
                                <a href="anim_penitencier.php?id_perso=<?php echo $id_perso_puni; ?>&xp=all" class="btn btn-warning font-weight-bold mb-3 text-dark">Reset XP à 0</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-12 mb-4">
                    <div class="card border-danger h-100 shadow">
                        <div class="card-header bg-danger text-white text-center"><h5>⛓️ Peines Majeures</h5></div>
                        <div class="card-body text-center">
                            <?php if(!isset($_GET['bagne'])): ?>
                                <a href="anim_penitencier.php?id_perso=<?php echo $id_perso_puni; ?>&bagne=ok" class="btn btn-danger btn-lg btn-block py-3">PRÉPARER LE TRANSFERT</a>
                            <?php else: ?>
                                <h6 class="text-danger mb-3 font-weight-bold">DURÉE</h6>
                                <div class="row no-gutters">
                                    <?php for($d=1; $d<=8; $d++): ?>
                                        <div class="col-3 p-1">
                                            <a href="anim_penitencier.php?id_perso=<?php echo $id_perso_puni; ?>&bagne=ok&duree=<?php echo $d; ?>" class="btn btn-sm btn-outline-danger btn-block font-weight-bold"><?php echo $d; ?>j</a>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <a href="anim_penitencier.php?id_perso=<?php echo $id_perso_puni; ?>" class="btn btn-link btn-sm mt-3 text-muted">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </body>
</html>
<?php
        }
        else {
            header("Location:jouer.php");
        }
    }
    else{
        echo "<center><font color='red'>Veuillez vous loguer.</font></center>";
    }
}
else {
    $_SESSION = array();
    session_destroy();
    header("Location:../index2.php");
}
?>