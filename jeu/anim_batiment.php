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
            $b_camp = ($camp == '1') ? 'b' : (($camp == '2') ? 'r' : 'g');

            $mess = "";
            $mess_erreur = "";

            // --- Logique de renommage ---
            if (isset($_POST['hid_id_instance_rename']) && isset($_POST['nom_batiment']) && $_POST['nom_batiment'] != "") {
                $id_instance_bat_rename = $_POST['hid_id_instance_rename'];
                $nouveau_nom_bat = addslashes($_POST['nom_batiment']);
                $sql = "UPDATE instance_batiment SET nom_instance='$nouveau_nom_bat' WHERE id_instanceBat='$id_instance_bat_rename'";
                $mysqli->query($sql);
                $mess .= "Le bâtiment $id_instance_bat_rename a été renommé en $nouveau_nom_bat";
            }
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Nord VS Sud - Animation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-header { background-color: #343a40; color: white; }
        .table thead th { vertical-align: middle; border-bottom: 2px solid #dee2e6; font-size: 0.9rem; }
        .stock-badge { font-size: 1.1em; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid #d39e00; }
        .badge-status { font-weight: bold; padding: 6px; border-radius: 4px; text-transform: uppercase; font-size: 0.75rem; }
        .progress { border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="card shadow border-0 mb-5">
            <div class="card-header text-center py-3">
                <h2 class="mb-0">Animation - Gestion des bâtiments (<?php echo $nom_camp; ?>)</h2>
            </div>
            <div class="card-body">

                <div class="text-center mb-4">
                    <a class="btn btn-outline-secondary" href="animation.php">Retour Animation</a>
                </div>

                <?php if ($mess): ?>
                    <div class="alert alert-success"><?php echo $mess; ?></div>
                <?php endif; ?>

                <h4 class="border-bottom pb-2 mb-3 text-primary">Bâtiments de production et logistique</h4>
                <div class="table-responsive mb-5">
                    <table class="table table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Bâtiment</th>
                                <th>Nom de l'instance</th>
                                <th style="width: 180px;">PV</th>
                                <th class="text-center">Positon sur la carte</th>
                                <th class="text-center">État / Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Requête avec JOIN : id_instance_bat avec underscores pour la table ressources
                            $sql = "SELECT i.id_instanceBat, b.nom_batiment, i.nom_instance, i.pv_instance, i.pvMax_instance,
                                           i.x_instance, i.y_instance, i.id_batiment, i.stock_actuel,
                                           r.stock_or, r.stock_bois, r.stock_fer
                                    FROM instance_batiment i
                                    INNER JOIN batiment b ON b.id_batiment = i.id_batiment
                                    LEFT JOIN ressources_entrepot r ON i.id_instanceBat = r.id_instance_bat
                                    WHERE i.camp_instance='$camp'
                                    AND i.id_batiment NOT IN (1, 2, 5, 7, 8, 9, 12, 13)
                                    ORDER BY i.id_batiment";

                            $res = $mysqli->query($sql);
                            while ($t = $res->fetch_assoc()) {
                                renderRow($t, $b_camp);
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <h4 class="border-bottom pb-2 mb-3 text-danger">Bâtiments militaires et de défense</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Bâtiment</th>
                                <th>Nom de l'instance</th>
                                <th style="width: 180px;">PV</th>
                                <th class="text-center">Pos</th>
                                <th class="text-center">État / Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT i.id_instanceBat, b.nom_batiment, i.nom_instance, i.pv_instance, i.pvMax_instance, i.x_instance, i.y_instance, i.id_batiment, i.stock_actuel
                                    FROM batiment b, instance_batiment i WHERE b.id_batiment = i.id_batiment AND i.camp_instance='$camp'
                                    AND i.id_batiment IN (1, 2, 5, 7, 8, 9) ORDER BY i.id_batiment";
                            $res = $mysqli->query($sql);
                            while ($t = $res->fetch_assoc()) {
                                renderRow($t, $b_camp);
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
<?php
        } else {
            header("Location:jouer.php");
        }
    }
}

// Fonction pour générer une ligne
function renderRow($t, $b_camp) {
    $id_instance = $t['id_instanceBat'];
    $id_bat      = $t['id_batiment'];
    $image_bat   = "b".$id_bat.$b_camp.".png";
    $pv_c = $t['pv_instance'];
    $pv_m = $t['pvMax_instance'];
    $pourc = ($pv_m > 0) ? ($pv_c / $pv_m) * 100 : 0;
    $color = ($pourc > 70) ? 'bg-success' : (($pourc > 30) ? 'bg-warning' : 'bg-danger');

    echo "<tr>";
    echo "<td><div class='d-flex align-items-center'><img src='../images_perso/$image_bat' width='35' class='mr-2'><div><strong>".$t['nom_batiment']."</strong><br><small class='text-muted'>#$id_instance</small></div></div></td>";
    echo "<td class='align-middle'><form method='post' class='form-inline'><input type='hidden' name='hid_id_instance_rename' value='$id_instance'><input type='text' name='nom_batiment' class='form-control form-control-sm mr-1' style='width:130px;' value=\"".htmlentities($t['nom_instance'], ENT_QUOTES)."\"><button type='submit' class='btn btn-primary btn-sm'>OK</button></form></td>";
    echo "<td class='align-middle'><div class='progress' style='height: 12px;'><div class='progress-bar progress-bar-striped $color' role='progressbar' style='width: $pourc%'></div></div><div class='text-center mt-1 small font-weight-bold'>$pv_c / $pv_m</div></td>";
    echo "<td class='text-center align-middle'><span class='badge badge-light border'>".$t['x_instance'].",".$t['y_instance']."</span></td>";
    echo "<td class='align-middle text-center'>";

    // Cas spécifique de l'Entrepôt (ID 6)
    if ($id_bat == 6) {
        $s_or   = $t['stock_or'] ?? 0;
        $s_bois = $t['stock_bois'] ?? 0;
        $s_fer  = $t['stock_fer'] ?? 0;

        echo "<div class='small text-left' style='display:inline-block;'>";
        echo "💰 <span class='badge badge-warning mb-1'>Or : $s_or</span><br>";
        echo "🌲 <span class='badge badge-secondary mb-1'>Bois : $s_bois</span><br>";
        echo "⛓️ <span class='badge badge-dark'>Fer : $s_fer</span>";
        echo "</div>";
    }

    // Stock pour Mines Or/Fer/Scierie (15, 16, 17)
    if (in_array($id_bat, [15, 16, 17])) {
        $val_stock = $t['stock_actuel'] ?? 0;
        echo "<div class='mb-1'><span class='badge badge-warning p-1 stock-badge d-block text-dark'>📦 Stock : $val_stock</span></div>";
    }

    // Badges d'état
    if ($id_bat == 11 && $pourc < 50) {
        echo "<span class='badge badge-danger badge-status d-block'>❌ Hors Service</span>";
    } elseif ($pourc > 50 && $pourc < 100) {
        echo "<span class='badge badge-danger badge-status d-block'>⚠️ Endommagé</span>";
    } elseif ($pourc <= 50 && $id_bat != 11) {
        echo "<span class='badge badge-dark badge-status d-block'>☢️ Critique</span>";
    } else {
        echo "<span class='text-muted small italic'>Fonctionnel</span>";
    }
    echo "</td></tr>";
}
?>