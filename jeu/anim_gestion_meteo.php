<?php
date_default_timezone_set('Europe/Paris');

// Connexion PDO
$host = 'localhost';
$db   = 'nvs';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$maintenant = new DateTime();
$success_msg = "";

// --- LOGIQUE DE LANCEMENT MANUEL DU CRON ---
if (isset($_POST['run_cron_manual'])) {
    // On inclut le fichier cron-meteo.php.
    // Note : On utilise l'output buffering pour capturer l'écho du fichier s'il y en a un.
    ob_start();
    include('cron_meteo.php');
    $output = ob_get_clean();
    $success_msg = "Le cycle météo a été régénéré avec succès !";
    // On rafraîchit la page pour mettre à jour les données
    header("Refresh: 2; url=anim_gestion_meteo.php");
}

// 1. Récupération des données (Triés par date de début)
$query = "SELECT * FROM meteo ORDER BY date_debut ASC";
$events = $pdo->query($query)->fetchAll();

// 2. Calcul du temps restant basé sur le début du cycle
$queryCheck = "SELECT MIN(date_debut) as debut_cycle FROM meteo WHERE date_fin > NOW()";
$check = $pdo->query($queryCheck)->fetch();
$debutCycle = ($check && $check['debut_cycle']) ? new DateTime($check['debut_cycle']) : null;

$alerteCron = false;
$secondesRestantes = 0;
$intervalleCron = 10 * 3600; // 10 heures

if ($debutCycle) {
    $timestampProchainRun = $debutCycle->getTimestamp() + $intervalleCron;
    $secondesRestantes = $timestampProchainRun - $maintenant->getTimestamp();

    if ($secondesRestantes < 0) {
        $alerteCron = true;
    }
} else {
    $alerteCron = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nord VS Sud - Animation Météo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a2e; color: #e0e0e0; margin: 20px; }
        .container { max-width: 1100px; margin: auto; }
        h1 { border-bottom: 2px solid #16213e; padding-bottom: 10px; color: #4ecca3; }
        .cron-status { padding: 15px; margin-bottom: 20px; border-radius: 5px; display: flex; align-items: center; justify-content: space-between; }
        .status-ok { background: rgba(46, 204, 113, 0.1); border: 1px solid #2ecc71; color: #2ecc71; }
        .status-ko { background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #e74c3c; }
        .blink { animation: blinker 2s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.3; } }
        table { width: 100%; background: #16213e; border-radius: 8px; overflow: hidden; margin-top: 20px;}
        th { background: #0f3460; color: #4ecca3; padding: 15px; }
        td { padding: 12px 15px; border-bottom: 1px solid #1a1a2e; }
        .badge { padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        .active { background: #2ecc71; color: white; }
        .pending { background: #f1c40f; color: black; }
        .finished { background: #95a5a6; color: white; opacity: 0.5; }
        #timer { font-family: monospace; font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center mb-4">
        <a class="btn btn-outline-info btn-sm" href="animation.php">Administration</a>
        <a class="btn btn-outline-light btn-sm" href="jouer.php">Retour Jeu</a>
    </div>

    <h1>Flux Météo Temps Réel</h1>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>

    <div id="cron-banner" class="cron-status <?= $alerteCron ? 'status-ko' : 'status-ok' ?>">
        <div>
            <strong>État du Cron :</strong>
            <span id="status-text"><?= $alerteCron ? '⚠️ CRON INACTIF' : '✅ FONCTIONNEL' ?></span>

            <?php if ($alerteCron): ?>
                <form method="POST" style="display:inline-block; margin-left: 20px;">
                    <button type="submit" name="run_cron_manual" class="btn btn-danger btn-sm blink">
                        🚀 Lancer le cycle manuellement
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <div class="text-right">
            <?php if ($debutCycle): ?>
                <div id="timer-container">
                    Prochain run dans : <span id="timer">--:--:--</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <table class="table-dark">
        <thead>
            <tr>
                <th>Type / Effet</th>
                <th>X , Y</th>
                <th>Rayon</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event):
                $debut = new DateTime($event['date_debut']);
                $fin = new DateTime($event['date_fin']);

                if ($maintenant >= $debut && $maintenant <= $fin) {
                    $class = 'active'; $label = 'En cours';
                } elseif ($maintenant < $debut) {
                    $class = 'pending'; $label = 'À venir';
                } else {
                    $class = 'finished'; $label = 'Terminé';
                }
            ?>
            <tr>
                <td><strong><?= strtoupper($event['type_meteo']) ?></strong></td>
                <td style="color:#4ecca3;"><?= $event['x_center'] ?> , <?= $event['y_center'] ?></td>
                <td><?= $event['rayon'] ?> cases</td>
                <td><?= $debut->format('d/m H:i') ?></td>
                <td><?= $fin->format('d/m H:i') ?></td>
                <td><span class="badge <?= $class ?>"><?= $label ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
let timeLeft = <?= (int)$secondesRestantes ?>;

function updateCountdown() {
    const timerDisplay = document.getElementById('timer');
    if (timeLeft <= 0) {
        if(timerDisplay) timerDisplay.innerHTML = "00:00:00";
        // On ne change pas le texte ici pour éviter les conflits si le bouton est cliqué
        return;
    }
    let h = Math.floor(timeLeft / 3600);
    let m = Math.floor((timeLeft % 3600) / 60);
    let s = timeLeft % 60;
    if(timerDisplay) {
        timerDisplay.innerHTML = (h<10?"0"+h:h)+":"+(m<10?"0"+m:m)+":"+(s<10?"0"+s:s);
    }
    timeLeft--;
}
setInterval(updateCountdown, 1000);
updateCountdown();
</script>
</body>
</html>