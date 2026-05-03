<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path)) {
                include ($phpbb_root_path .'config.php');
}

if(isset($_SESSION["id_perso"])){

                $id_perso = $_SESSION['id_perso'];
                $admin = admin_perso($mysqli, $id_perso);

                if($admin){
                                $mess_err = "";
                                $mess = "";

                                // --- TRAITEMENTS ---
                                if (isset($_POST["destruction_pont"]) && $_POST["destruction_pont"] == 'ok') { /* Logic ici */ }
?>
<!DOCTYPE HTML>
<html>
<head>
                <title>Nord VS Sud - Admin Bâtiments</title>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
                <style>
                                .card { margin-bottom: 2rem; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
                                .card-header h4 { margin-bottom: 0; font-weight: bold; }
                                .table thead th { border-top: none; background-color: #f8f9fa; }
                                .badge-id { font-family: monospace; font-size: 0.9rem; }
                                .progress { background-color: #e9ecef; }
                </style>
</head>
<body class="bg-light">
                <div class="container-fluid py-4">
                                <div class="row mb-4">
                                                <div class="col-12 text-center">
                                                                <h2 class="display-4">Administration des Bâtiments</h2>
                                                                <?php if($mess_err) echo "<div class='alert alert-danger'>$mess_err</div>"; ?>
                                                                <?php if($mess) echo "<div class='alert alert-success'>$mess</div>"; ?>
                                                </div>
                                </div>

                                <div class="row mb-4 text-center">
                                                <div class="col-12">
                                                                <a class="btn btn-outline-primary" href="admin_nvs.php">Retour Administration</a>
                                                                <a class="btn btn-outline-secondary" href="jouer.php">Retour au jeu</a>
                                                                <a class="btn btn-success" href="anim_creer_batiment.php">Créer un bâtiment</a>
                                                </div>
                                </div>

                                <?php
                                // 1. Récupération des données
                                $sql = "SELECT id_instanceBat, instance_batiment.id_batiment, nom_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance
                                                FROM instance_batiment, batiment
                                                WHERE instance_batiment.id_batiment = batiment.id_batiment
                                                ORDER BY camp_instance, nom_batiment ASC";
                                $res = $mysqli->query($sql);

                                $categories = [
                                                'fortifies'     => ['titre' => '1° Bâtiments Fortifiés (Forts / Fortins)', 'color' => 'bg-dark', 'data' => []],
                                                'transport'     => ['titre' => '2° Transport (Gares / Trains)', 'color' => 'bg-info', 'data' => []],
                                                'ressources'    => ['titre' => '3° Ressources (Entrepôts / Mines / Scieries)', 'color' => 'bg-success', 'data' => []],
                                                'fortifications'=> ['titre' => '4° Fortifications (Barricades / Tours / Ponts)', 'color' => 'bg-warning text-dark', 'data' => []]
                                ];

                                while ($t = $res->fetch_assoc()) {
                                                $id_type = $t['id_batiment'];

                                                if (in_array($id_type, [8, 9])) {
                                                                $categories['fortifies']['data'][] = $t;
                                                } elseif (in_array($id_type, [11, 12])) {
                                                                $categories['transport']['data'][] = $t;
                                                } elseif (in_array($id_type, [6, 15, 16, 17])) {
                                                                // Regroupement demandé : Entrepot(6), Or(15), Scierie(16), Fer(17)
                                                                $categories['ressources']['data'][] = $t;
                                                } else {
                                                                $categories['fortifications']['data'][] = $t;
                                                }
                                }

                                // Fonction de génération des tableaux
                                function genererTableau($cat) {
                                                $data = $cat['data'];
                                                if (empty($data)) return "<p class='text-muted p-3 text-center'>Aucun bâtiment répertorié dans cette catégorie.</p>";

                                                $html = "<div class='table-responsive'><table class='table table-hover mb-0'>";
                                                $html .= "<thead><tr>
                                                                                                <th style='width: 10%'>IDs</th>
                                                                                                <th style='width: 40%'>Nom & Instance</th>
                                                                                                <th style='width: 15%'>Coordonnées</th>
                                                                                                <th style='width: 15%'>État (PV)</th>
                                                                                                <th style='width: 20%'>Actions</th>
                                                                                        </tr></thead><tbody>";

                                                foreach ($data as $b) {
                                                                $color = ($b['camp_instance'] == 1) ? "#007bff" : (($b['camp_instance'] == 2) ? "#dc3545" : "#28a745");

                                                                $html .= "<tr>";
                                                                // Colonne IDs (Instance et Type)
                                                                $html .= "<td>
                                                                                        <span class='badge badge-secondary badge-id' title='ID Instance'>#".$b['id_instanceBat']."</span>
                                                                                        <small class='text-muted d-block'>Type: ".$b['id_batiment']."</small>
                                                                                    </td>";

                                                                // Colonne Nom & Renommer
                                                                $html .= "<td><form method='POST' class='form-inline'>";
                                                                $html .= "<input type='hidden' name='hid_id_instance_rename' value='".$b['id_instanceBat']."'>";
                                                                $html .= "<strong style='color:$color' class='mr-2'>".$b['nom_batiment']."</strong>";
                                                                $html .= "<input type='text' name='nom_batiment' class='form-control form-control-sm flex-grow-1 mr-2' value='".htmlentities($b['nom_instance'], ENT_QUOTES)."'>";
                                                                $html .= "<button type='submit' class='btn btn-primary btn-sm shadow-sm'>Renommer</button>";
                                                                $html .= "</form></td>";

                                                                // Colonne Coordonnées
                                                                $html .= "<td><span class='badge badge-light border px-2 py-1'>X: ".$b['x_instance']." | Y: ".$b['y_instance']."</span></td>";

                                                                // Colonne État / PV
                                                                $pct = ($b['pvMax_instance'] > 0) ? round(($b['pv_instance'] / $b['pvMax_instance']) * 100) : 0;
                                                                $barColor = ($pct < 30) ? "bg-danger" : "bg-success";
                                                                $html .= "<td>
                                                                                        <small class='d-block font-weight-bold'>".$b['pv_instance']." / ".$b['pvMax_instance']."</small>
                                                                                        <div class='progress shadow-sm' style='height: 6px;'><div class='progress-bar $barColor' style='width: $pct%'></div></div>
                                                                                    </td>";

                                                                // Colonne Actions
                                                                $html .= "<td><form method='POST' onsubmit='return confirm(\"Voulez-vous vraiment détruire ce bâtiment ?\");'>";
                                                                $html .= "<input type='hidden' name='id_instance_bat_destruction' value='".$b['id_instanceBat']."'>";
                                                                $html .= "<button type='submit' class='btn btn-outline-danger btn-sm'>Détruire</button>";
                                                                $html .= "</form></td>";

                                                                $html .= "</tr>";
                                                }
                                                $html .= "</tbody></table></div>";
                                                return $html;
                                }
                                ?>

                                <!-- Affichage des sections en pleine largeur l'une sous l'autre -->
                                <div class="row">
                                                <?php foreach ($categories as $key => $cat): ?>
                                                <div class="col-12 mb-4">
                                                                <div class="card shadow-sm border-0">
                                                                                <div class="card-header <?php echo $cat['color']; ?> text-white py-3">
                                                                                                <h4 class="h5 mb-0 text-uppercase"><?php echo $cat['titre']; ?></h4>
                                                                                </div>
                                                                                <div class="card-body p-0">
                                                                                                <?php echo genererTableau($cat); ?>
                                                                                </div>
                                                                </div>
                                                </div>
                                                <?php endforeach; ?>
                                </div>
                </div>

                <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
<?php
                }
}
?>