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

function utf8_clean_string($text) {
    $text = preg_replace('#(?:[\x00-\x1F\x7F]+|(?:\xC2[\x80-\x9F])+)#', '', $text);
    $text = preg_replace('# {2,}#', ' ', $text);
    $text = strtolower($text);
    return trim($text);
}

$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

$message_alerte = "";

if($dispo == '1' || $admin){
    if (isset($_SESSION["id_perso"])) {
        $id = $_SESSION["id_perso"];

        if (anim_perso($mysqli, $id)) {

            function mail_changement_nom($nouveau_nom, $email_joueur){
                $headers ='From: Nord vs Sud<no-reply@nord-vs-sud.fr>'."\n";
                $headers .='Reply-To: no-reply@nord-vs-sud.fr'."\n";
                $headers .='Content-Type: text/plain; charset="utf-8"'."\n";
                $headers .='Content-Transfer-Encoding: 8bit';
                $titre = 'Changement du nom de votre personnage principal';
                $message = "Votre nouveau nom est ".$nouveau_nom.". Veuillez utiliser votre nouveau nom pour vous connecter.";
                mail($email_joueur, $titre, $message, $headers);
            }

            $sql = "SELECT clan FROM perso WHERE id_perso='$id'";
            $res = $mysqli->query($sql);
            $t = $res->fetch_assoc();
            $camp = $t['clan'];

            $nom_camp = ($camp == '1') ? 'Nord' : (($camp == '2') ? 'Sud' : 'Indien');

            if (isset($_GET['id_perso']) && isset($_GET['type']) && isset($_GET['valid'])) {
                $id_perso_maj = $_GET['id_perso'];
                $type_demande_maj = $_GET['type'];

                if (preg_match("#^[0-9]+$#", $id_perso_maj) && preg_match("#^[0-9]+$#", $type_demande_maj)) {
                    if ($_GET['valid'] == 'ok') {
                        if ($type_demande_maj == 1) {
                            $sql = "SELECT info_demande FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='1'";
                            $res = $mysqli->query($sql);
                            $t = $res->fetch_assoc();
                            $nouveau_nom_perso = addslashes($t['info_demande']);

                            $sql = "SELECT id_perso FROM perso WHERE nom_perso='$nouveau_nom_perso'";
                            $res = $mysqli->query($sql);
                            if ($res->num_rows == 0) {
                                $sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso_maj'";
                                $res = $mysqli->query($sql);
                                $t = $res->fetch_assoc();
                                $ancien_nom = $t['nom_perso'];
                                $camp_p = $t['clan'];
                                $couleurs = [1 => 'blue', 2 => 'red', 3 => 'green'];
                                $couleur_clan_p = $couleurs[$camp_p];

                                $sql = "SELECT email_joueur FROM joueur, perso WHERE perso.idJoueur_perso = joueur.id_joueur AND perso.id_perso='$id_perso_maj'";
                                $res = $mysqli->query($sql);
                                $t = $res->fetch_assoc();
                                $email_joueur = $t["email_joueur"];

                                $mysqli->query("UPDATE perso SET nom_perso='$nouveau_nom_perso' WHERE id_perso='$id_perso_maj'");
                                $mysqli->query("INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement) VALUES ($id_perso_maj,'<b style=\"color:$couleur_clan_p\">$ancien_nom</b>','a été renommé en <b style=\"color:$couleur_clan_p\">$nouveau_nom_perso</b>',NOW())");
                                $mysqli->query("DELETE FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='1'");

                                mail_changement_nom($nouveau_nom_perso, $email_joueur);
                                $username_clean = utf8_clean_string($nouveau_nom_perso);
                                $mysqli->query("UPDATE ".$table_prefix."users SET username='$nouveau_nom_perso', username_clean='$username_clean' WHERE user_email='$email_joueur'");

                                $message_alerte = "<div class='alert alert-success'>Le nom de <b>$ancien_nom</b> a été changé en <b>$nouveau_nom_perso</b>.</div>";
                            } else {
                                $message_alerte = "<div class='alert alert-danger'>Impossible : le nom <b>$nouveau_nom_perso</b> est déjà pris.</div>";
                            }
                        }
                    } else {
                        $mysqli->query("DELETE FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='$type_demande_maj'");
                        $message_alerte = "<div class='alert alert-warning'>La demande a été refusée et supprimée.</div>";
                    }
                }
            }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Nord VS Sud - Animation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background: url('/public/img/backgrounds/gestPerso.png') no-repeat center center fixed;
            background-size: cover;
            background-color: #343a40;
        }
        .container {
            /* Rendu beaucoup plus transparent (0.4) avec un effet de flou pour la lisibilité */
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .table td { vertical-align: middle; }
        /* Transparence également pour les cartes et tableaux */
        .card { background-color: rgba(255, 255, 255, 0.6); border: none; }
        .bg-white { background-color: rgba(255, 255, 255, 0.7) !important; }
        .thead-dark th { background-color: rgba(44, 62, 80, 0.9); border-color: transparent; }
        h2, h4, label { text-shadow: 1px 1px 2px rgba(255,255,255,0.5); }
    </style>
</head>
<body>

<div class="container">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="display-4" style="font-size: 2.5rem; font-weight: bold;">Gestion des Demandes (<?php echo $nom_camp; ?>)</h2>
            <hr>
        </div>
    </div>

    <!-- Zone de messages -->
    <?php echo $message_alerte; ?>

    <div class="row mb-4 text-center">
        <div class="col-12">
            <a class='btn btn-info mx-1 shadow-sm' href='anim_infos_perso.php'>🔍 Infos Persos</a>
            <a class="btn btn-secondary mx-1 shadow-sm" href="animation.php">🏠 Retour Animation</a>
        </div>
    </div>

    <div class="card border-primary mb-5 shadow-sm">
        <div class="card-header bg-primary text-white">Rechercher un événement détaillé</div>
        <div class="card-body">
            <form method='POST' action='anim_event_perso.php' class="form-inline justify-content-center">
                <label class="my-1 mr-2 font-weight-bold" for="formSelectPerso">Personnage :</label>
                <select class="custom-select my-1 mr-sm-2 col-md-6" name='liste_perso_event' id="formSelectPerso">
                    <?php
                    $sql = "SELECT id_perso, nom_perso FROM perso WHERE clan='$camp' ORDER BY nom_perso ASC";
                    $res = $mysqli->query($sql);
                    while ($t = $res->fetch_assoc()) {
                        echo "<option value='".$t["id_perso"]."'>".$t["nom_perso"]." [".$t["id_perso"]."]</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary my-1 shadow-sm">Voir les logs</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h4 class="mb-3 font-weight-bold">Demandes en attente</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered shadow-sm bg-white">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th>Perso</th>
                            <th>Type</th>
                            <th>Détails</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "(SELECT pda.id_perso, pda.type_demande, pda.info_demande FROM perso_demande_anim pda, perso p
                            WHERE pda.id_perso = p.id_perso AND p.clan = '$camp' AND type_demande = 1)
                            UNION ALL
                            (SELECT pda.id_perso, pda.type_demande, pda.info_demande FROM perso_demande_anim pda, perso p
                            WHERE pda.id_perso = p.idJoueur_perso AND p.clan = '$camp' AND type_demande > 1 AND p.chef='1')
                            ORDER BY id_perso ASC";
                    $res = $mysqli->query($sql);

                    if($res->num_rows == 0) {
                        echo "<tr><td colspan='4' class='text-center text-muted font-italic'>Aucune demande en attente.</td></tr>";
                    }

                    while ($t = $res->fetch_assoc()) {
                        $id_p = $t['id_perso'];
                        $type = $t['type_demande'];
                        $info = $t['info_demande'];

                        $libelles = [1 => "Nom", 2 => "Suppression", 3 => "Bataillon", 4 => "Camp"];
                        $nom_demande = $libelles[$type] ?? "Inconnu";

                        if ($type == 1) $info = "Nouveau : <b>$info</b>";
                        if ($type == 4) $info = ($info == 1) ? "Vers Nord" : "Vers Sud";

                        $sql_c = ($type == 1) ? "SELECT id_perso, nom_perso FROM perso WHERE id_perso='$id_p'" : "SELECT id_perso, nom_perso FROM perso WHERE idJoueur_perso='$id_p' AND chef='1'";
                        $res_c = $mysqli->query($sql_c);
                        if ($t_c = $res_c->fetch_assoc()) {
                            ?>
                            <tr style="background: rgba(255,255,255,0.5);">
                                <td class="text-center font-weight-bold">
                                    <?php echo $t_c['nom_perso']; ?><br>
                                    <small class="text-muted">[<a href="evenement.php?infoid=<?php echo $t_c['id_perso']; ?>"><?php echo $t_c['id_perso']; ?></a>]</small>
                                </td>
                                <td class="text-center"><span class="badge badge-secondary"><?php echo $nom_demande; ?></span></td>
                                <td><?php echo $info; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a class='btn btn-success shadow-sm' href="anim_perso.php?id_perso=<?php echo $id_p; ?>&type=<?php echo $type; ?>&valid=ok" title="Accepter sans malus">✅</a>
                                        <a class='btn btn-outline-success shadow-sm' href="anim_perso.php?id_perso=<?php echo $id_p; ?>&type=<?php echo $type; ?>&valid=ok&malus" title="Accepter avec malus">⚖️</a>
                                        <a class='btn btn-danger shadow-sm' href="anim_perso.php?id_perso=<?php echo $id_p; ?>&type=<?php echo $type; ?>&valid=refus" title="Refuser">❌</a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
<?php
        } else {
            $mysqli->query("INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', 'Accès anim_perso sans droits')");
            header("Location:jouer.php");
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Veuillez vous loguer.</div>";
    }
} else {
    $_SESSION = array();
    session_destroy();
    header("Location:../index2.php");
}
?>