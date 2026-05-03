<?php
@session_start();
require_once("../fonctions.php");
$mysqli = db_connexion();

$id_clan_joueur = $_SESSION['id_clan'] ?? 1;

/**
 * 1. DÉFINITION DES COÛTS (Rappel des contraintes de ton projet)
 */
// Coûts pour construire une unité/train ou améliorer
$couts = [
    12 => ['or' => 0,  'bois' => 0, 'fer' => 30], // TRAINS (Convois)
    11 => ['or' => 10, 'bois' => 50, 'fer' => 30], // GARES (Prochaine amélioration)
    8  => ['or' => 30,  'bois' => 50, 'fer' => 50],  // FORTINS

];

/**
 * 2. RÉCUPÉRATION DES DONNÉES
 */
$qRes = $mysqli->prepare("
    SELECT ib.id_instanceBat, ib.id_batiment, ib.nom_instance as nom, ib.x_instance as x, ib.y_instance as y,
           re.stock_or, re.stock_bois, re.stock_fer
    FROM instance_batiment ib
    LEFT JOIN ressources_entrepot re ON ib.id_instanceBat = re.id_instance_bat
    WHERE ib.camp_instance = ? AND ib.id_batiment IN (6, 15, 16, 17)
");
$qRes->bind_param("i", $id_clan_joueur); $qRes->execute();
$batimentsRessources = $qRes->get_result()->fetch_all(MYSQLI_ASSOC);

$qTrans = $mysqli->prepare("
    SELECT ib.*, ib.nom_instance as nom, ib.x_instance as x, ib.y_instance as y, ib.pv_instance as pv, ib.pvMax_instance as pv_max,
           re.stock_or, re.stock_bois, re.stock_fer
    FROM instance_batiment ib
    LEFT JOIN ressources_entrepot re ON ib.id_instanceBat = re.id_instance_bat
    WHERE ib.camp_instance = ? AND ib.id_batiment IN (11, 12)
");
$qTrans->bind_param("i", $id_clan_joueur); $qTrans->execute();
$batimentsTransports = $qTrans->get_result()->fetch_all(MYSQLI_ASSOC);

$qMil = $mysqli->prepare("
    SELECT ib.*, ib.nom_instance as nom, ib.x_instance as x, ib.y_instance as y, ib.pv_instance as pv, ib.pvMax_instance as pv_max,
           re.stock_or, re.stock_bois, re.stock_fer
    FROM instance_batiment ib
    LEFT JOIN ressources_entrepot re ON ib.id_instanceBat = re.id_instance_bat
    WHERE ib.camp_instance = ? AND ib.id_batiment IN (8, 9, 10)
");
$qMil->bind_param("i", $id_clan_joueur); $qMil->execute();
$batimentsMilitaires = $qMil->get_result()->fetch_all(MYSQLI_ASSOC);

/**
 * 3. FONCTIONS DE STATUT
 */
function getGlobalStatusBadge($id_batiment_type, $liste_instances, $couts) {
    if (!isset($couts[$id_batiment_type])) return "";

    $c = $couts[$id_batiment_type];
    $peut_produire = false;

    foreach ($liste_instances as $inst) {
        if ($inst['id_batiment'] == $id_batiment_type) {
            if (($inst['stock_or'] ?? 0) >= $c['or'] &&
                ($inst['stock_bois'] ?? 0) >= $c['bois'] &&
                ($inst['stock_fer'] ?? 0) >= $c['fer']) {
                $peut_produire = true;
                break;
            }
        }
    }

    if ($peut_produire) {
        return '<span class="badge bg-success ms-2" style="font-size:0.6rem; vertical-align:middle;">DISPONIBLE</span>';
    } else {
        return '<span class="badge bg-danger ms-2" style="font-size:0.6rem; vertical-align:middle;">STOCKS BAS</span>';
    }
}

function getHealthBadge($pv, $max) {
    if ($max <= 0) return ['c' => 'bg-secondary', 't' => 'Inconnu'];
    $p = ($pv / $max) * 100;
    if ($p >= 100) return ['c' => 'bg-success', 't' => 'Intact'];
    if ($p >= 50) return ['c' => 'bg-warning text-dark', 't' => 'Endommagé'];
    return ['c' => 'bg-danger', 't' => 'Critique'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Infrastructures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('../public/img/backgrounds/emgestionmoyens.png');
            background-size: cover; background-attachment: fixed; background-position: center;
            background-color: #1a1a1a; min-height: 100vh; color: #eee;
        }
        h2 { color: #ffffff !important; text-shadow: 3px 3px 5px #000; letter-spacing: 4px; font-weight: bold; }
        .card-custom { background-color: rgba(44, 62, 80, 0.9); border: 1px solid #444; border-radius: 8px; height: 100%; }
        .card-header { font-weight: bold; text-transform: uppercase; background-color: #34495e !important; color: #fff; }
        .list-group-item { background: transparent; color: #ccc; border-color: rgba(62, 79, 95, 0.5); }
        .loc-tag { color: #f1c40f; font-family: monospace; font-weight: bold; }
        .type-header { background-color: rgba(0,0,0,0.6) !important; color: #3498db; font-size: 0.8rem; border-top: 1px solid #444; display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <h2 class="text-center mb-5 text-uppercase">Rapport des Infrastructures - Camp <?= ($id_clan_joueur == 1) ? 'NORD' : 'SUD' ?></h2>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-custom shadow">
                <div class="card-header text-info">📦 Stocks & Productions</div>
                <ul class="list-group list-group-flush">
                    <?php
                    $catRes = ["Entrepôts" => [6], "Mines d'or" => [15], "Scieries" => [16], "Mines de Fer" => [17]];
                    foreach ($catRes as $titre => $ids):
                        $filtre = array_filter($batimentsRessources, fn($b) => in_array($b['id_batiment'], $ids));
                        if (!empty($filtre)): ?>
                            <li class="list-group-item type-header fw-bold text-uppercase"><?= $titre ?></li>
                            <?php foreach ($filtre as $b): ?>
                                <li class="list-group-item border-0">
                                    <div class="d-flex justify-content-between small">
                                        <span><small class="text-info">#<?= $b['id_instanceBat'] ?></small> <strong><?= htmlspecialchars($b['nom']) ?></strong></span>
                                        <span class="loc-tag">[<?= $b['x'] ?>|<?= $b['y'] ?>]</span>
                                    </div>
                                    <div class="mt-1 d-flex gap-1 flex-wrap">
                                        <span class="badge bg-warning text-dark" style="font-size:0.65rem;">Or: <?= $b['stock_or'] ?? 0 ?></span>
                                        <span class="badge bg-secondary" style="font-size:0.65rem;">Bois: <?= $b['stock_bois'] ?? 0 ?></span>
                                        <span class="badge bg-info" style="font-size:0.65rem;">Fer: <?= $b['stock_fer'] ?? 0 ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?><hr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-custom shadow">
                <div class="card-header text-warning">    🚂 Gares & Transports</div>
                <ul class="list-group list-group-flush">
                    <?php
                    $catTrans = ["Gares" => 11, "Trains & Convois" => 12];
                    foreach ($catTrans as $titre => $id_type):
                        $filtre = array_filter($batimentsTransports, fn($b) => $b['id_batiment'] == $id_type);
                        if (!empty($filtre)): ?>
                            <li class="list-group-item type-header fw-bold text-uppercase">
                                <span><?= $titre ?></span>
                                <?= getGlobalStatusBadge($id_type, $batimentsTransports, $couts) ?>
                            </li>
                            <?php foreach ($filtre as $b):
                                $badgeHealth = getHealthBadge($b['pv'], $b['pv_max']); ?>
                                <li class="list-group-item border-0 d-flex justify-content-between align-items-center">
                                    <div><small class="text-info">#<?= $b['id_instanceBat'] ?></small> <span class="small"><?= htmlspecialchars($b['nom']) ?></span></div>
                                    <div class="text-end">
                                        <span class="loc-tag x-small" style="font-size:0.7rem;"><?= $b['x'] ?>|<?= $b['y'] ?></span>
                                        <span class="badge <?= $badgeHealth['c'] ?> ms-1" style="font-size:0.6rem;"><?= $badgeHealth['t'] ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?><hr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-custom shadow">
                <div class="card-header text-danger">🏰 Forts & Fortins</div>
                <ul class="list-group list-group-flush">
                    <?php
                    $catMil = ["Forts" => 9, "Fortins" => 8, "Pénitenciers" => 10];
                    foreach ($catMil as $titre => $id_type):
                        $filtre = array_filter($batimentsMilitaires, fn($b) => $b['id_batiment'] == $id_type);
                        if (!empty($filtre)): ?>
                            <li class="list-group-item type-header fw-bold text-uppercase">
                                <span><?= $titre ?></span>
                                <?= getGlobalStatusBadge($id_type, $batimentsMilitaires, $couts) ?>
                            </li>
                            <?php foreach ($filtre as $b):
                                $badgeHealth = getHealthBadge($b['pv'], $b['pv_max']); ?>
                                <li class="list-group-item border-0 d-flex justify-content-between align-items-center">
                                    <div><small class="text-info">#<?= $b['id_instanceBat'] ?></small> <span class="small"><?= htmlspecialchars($b['nom']) ?></span></div>
                                    <div class="text-end">
                                        <span class="loc-tag x-small" style="font-size:0.7rem;"><?= $b['x'] ?>|<?= $b['y'] ?></span>
                                        <span class="badge <?= $badgeHealth['c'] ?> ms-1" style="font-size:0.6rem;"><?= $badgeHealth['t'] ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?><hr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 mb-4">
        <a href="command.php" class="btn btn-outline-info px-5 fw-bold shadow-lg" style="letter-spacing: 1px; background-color: rgba(0,0,0,0.3);">
            RETOUR À L'ÉTAT-MAJOR
        </a>
    </div>
</div>

</body>
</html>