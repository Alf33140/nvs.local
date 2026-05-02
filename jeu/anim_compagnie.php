<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path)) {
    include ($phpbb_root_path .'config.php');
}

$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
    if (isset($_SESSION["id_perso"])) {
        $id = $_SESSION["id_perso"];

        if (anim_perso($mysqli, $id)) {
            // Récupération du camp
            $sql = "SELECT clan FROM perso WHERE id_perso='$id'";
            $res = $mysqli->query($sql);
            $t = $res->fetch_assoc();
            $camp = $t['clan'];

            $nom_camp = ($camp == '1') ? 'Nord' : (($camp == '2') ? 'Sud' : 'Indien');

            // --- LOGIQUE DE VALIDATION ---
            if (isset($_GET['id_compagnie'], $_GET['type'], $_GET['valid'])) {
                $id_compagnie_maj = $_GET['id_compagnie'];
                $type_demande_maj = $_GET['type'];
                $valid_maj        = $_GET['valid'];

                if (preg_match("#^[0-9]+$#", $id_compagnie_maj) && preg_match("#^[0-9]+$#", $type_demande_maj)) {
                    if ($valid_maj == 'ok') {
                        if ($type_demande_maj == 1) {
                            $sql = "SELECT info_demande FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie_maj' AND type_demande='1'";
                            $res = $mysqli->query($sql);
                            $t = $res->fetch_assoc();
                            $nouveau_nom_compagnie = addslashes($t['info_demande']);

                            $mysqli->query("UPDATE compagnies SET nom_compagnie='$nouveau_nom_compagnie' WHERE id_compagnie='$id_compagnie_maj'");
                            $mysqli->query("DELETE FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie_maj' AND type_demande='$type_demande_maj'");

                            $texte = addslashes("La demande de changement de nom en $nouveau_nom_compagnie est validée");
                            $mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_compagnie.php', 'validation changement nom', '$texte')");
                        }
                        else if ($type_demande_maj == 2) {
                            // Suppression compagnie (Logique conservée)
                            $res = $mysqli->query("SELECT nom_compagnie FROM compagnies WHERE id_compagnie=$id_compagnie_maj");
                            $sec = $res->fetch_assoc();
                            $nom_compagnie = addslashes($sec["nom_compagnie"]);

                            $res_perso = $mysqli->query("SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie_maj'");
                            while ($tp = $res_perso->fetch_assoc()) {
                                $id_p = $tp['id_perso'];
                                $mysqli->query("DELETE FROM perso_in_compagnie WHERE id_perso='$id_p'");
                                $mysqli->query("DELETE FROM banque_compagnie WHERE id_perso='$id_p'");
                            }

                            $mysqli->query("DELETE FROM compagnies WHERE id_compagnie='$id_compagnie_maj'");
                            $mysqli->query("DELETE FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie_maj'");
                            $mysqli->query("DELETE FROM banque_log WHERE id_compagnie='$id_compagnie_maj'");
                            $mysqli->query("DELETE FROM histobanque_compagnie WHERE id_compagnie='$id_compagnie_maj'");
                            $mysqli->query("DELETE FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie_maj'");

                            $texte = "Validation suppression compagnie $nom_compagnie ($id_compagnie_maj)";
                            $mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_compagnie.php', 'validation suppression', '$texte')");
                        }
                        header("Location:anim_compagnie.php");
                        exit;
                    }
                    else if ($valid_maj == 'refus') {
                        $mysqli->query("DELETE FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie_maj' AND type_demande='$type_demande_maj'");
                        $mysqli->query("INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_compagnie.php', 'refus demande', 'Compagnie ID: $id_compagnie_maj')");
                        header("Location:anim_compagnie.php");
                        exit;
                    }
                }
            }

            // Récupération des demandes
            $sql = "SELECT cda.*, c.nom_compagnie FROM compagnie_demande_anim cda
                    JOIN compagnies c ON cda.id_compagnie = c.id_compagnie
                    WHERE c.id_clan='$camp'
                    ORDER BY cda.id_compagnie ASC";
            $res = $mysqli->query($sql);
?>
<!DOCTYPE HTML>
<html lang="fr">
<head>
    <title>Nord VS Sud - Animation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background: url('/public/img/backgrounds/gestCompagnie.png') no-repeat center center fixed;
            background-size: cover;
            background-color: #343a40; /* Couleur de secours */
        }
        .container {
            margin-top: 50px;
            margin-bottom: 50px;
        }
        /* Semi-transparence pour la carte pour laisser voir le fond tout en restant lisible */
        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
        }
        .card-header {
            background-color: rgba(0, 0, 255, 0.85);
            color: white;
        }
        .table thead th { border-top: none; }
        .btn-action { min-width: 100px; margin: 2px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow-lg">
        <div class="card-header text-center">
            <h3 class="mb-0">Gestion des Demandes Compagnies (<?php echo $nom_camp; ?>)</h3>
        </div>
        <div class="card-body text-center">
            <a class="btn btn-outline-secondary mb-4" href="animation.php">
                &larr; Retour à l'animation
            </a>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Compagnie</th>
                            <th>Type de demande</th>
                            <th>Détails</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res->num_rows == 0): ?>
                            <tr>
                                <td colspan="4" class="text-muted italic">Aucune demande en attente.</td>
                            </tr>
                        <?php endif; ?>

                        <?php while ($t = $res->fetch_assoc()):
                            $id_c = $t['id_compagnie'];
                            $type = $t['type_demande'];

                            if ($type == 1) {
                                $badge = '<span class="badge badge-info">Changement de nom</span>';
                                $details = "<strong>Nouveau :</strong> " . htmlspecialchars($t['info_demande']);
                            } else {
                                $badge = '<span class="badge badge-danger">Suppression</span>';
                                $details = "Demande de dissolution définitive";
                            }
                        ?>
                        <tr>
                            <td class="align-middle text-left">
                                <strong><?php echo htmlspecialchars($t['nom_compagnie']); ?></strong>
                                <br><small class="text-muted">ID: <a href="compagnie.php?id_compagnie=<?php echo $id_c; ?>&voir_compagnie=ok"><?php echo $id_c; ?></a></small>
                            </td>
                            <td class="align-middle"><?php echo $badge; ?></td>
                            <td class="align-middle text-left"><?php echo $details; ?></td>
                            <td class="align-middle">
                                <a class="btn btn-success btn-sm btn-action"
                                   href="anim_compagnie.php?id_compagnie=<?php echo $id_c; ?>&type=<?php echo $type; ?>&valid=ok"
                                   onclick="return confirm('Valider cette demande ?');">Accepter</a>

                                <a class="btn btn-danger btn-sm btn-action"
                                   href="anim_compagnie.php?id_compagnie=<?php echo $id_c; ?>&type=<?php echo $type; ?>&valid=refus"
                                   onclick="return confirm('Refuser cette demande ?');">Refuser</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
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
            $mysqli->query("INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', 'Accès interdit page anim_compagnie')");
            header("Location:jouer.php");
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Veuillez vous connecter.</div>";
    }
} else {
    $_SESSION = array();
    session_destroy();
    header("Location:../index2.php");
}
?>