<?php
/**
 * meteo.php - Flux JSON final harmonisé
 * Structure BDD : x_center, y_center
 */

// 1. Désactiver l'affichage des erreurs pour ne pas polluer le flux JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Inclusion de la configuration (BDD_HOST, etc.)
require_once(__DIR__ . "/config.php");

// 4. Initialisation des données par défaut
$data = [
    "effet"       => "soleil",
    "x_center"    => 0,  // Harmonisé BDD (center avec ER)
    "y_center"    => 0,  // Harmonisé BDD
    "rayon"       => 0,
    "joueur_x"    => 0,
    "joueur_y"    => 0,
    "joueur_perc" => 10,
    "debug"       => ""
];

try {
    if (isset($_SESSION['id_perso'])) {

        $mysqli = new mysqli(BDD_HOST, BDD_LOGIN, BDD_PASSWORD, BDD_NAME);

        if (!$mysqli->connect_error) {
            $mysqli->set_charset("utf8");
            $id_joueur = (int)$_SESSION['id_perso'];
            $now = date("Y-m-d H:i:s");

            // Récupération de la position du joueur
            $sqlPos = "SELECT x_perso, y_perso, perception_perso FROM perso WHERE id_perso = $id_joueur";
            $resPos = $mysqli->query($sqlPos);

            if ($resPos && $rowPos = $resPos->fetch_assoc()) {
                $data['joueur_x']    = (int)$rowPos['x_perso'];
                $data['joueur_y']    = (int)$rowPos['y_perso'];
                $data['joueur_perc'] = (int)$rowPos['perception_perso'];

                $jx = $data['joueur_x'];
                $jy = $data['joueur_y'];
                $jp = $data['joueur_perc'];

                // Requête Météo avec colonnes exactes : x_center, y_center
                $sqlMeteo = "SELECT type_meteo, x_center, y_center, rayon
                             FROM meteo
                             WHERE ('$now' BETWEEN date_debut AND date_fin)
                             AND (POWER(x_center - $jx, 2) + POWER(y_center - $jy, 2) <= POWER(rayon + $jp, 2))
                             LIMIT 1";

                $resMeteo = $mysqli->query($sqlMeteo);

                if ($resMeteo && $rowM = $resMeteo->fetch_assoc()) {
                    $data['effet']    = strtolower(trim($rowM['type_meteo']));
                    $data['x_center'] = (int)$rowM['x_center'];
                    $data['y_center'] = (int)$rowM['y_center'];
                    $data['rayon']    = (int)$rowM['rayon'];
                    $data['debug']    = "Evenement trouve";
                }
            }
            $mysqli->close();
        }
    }
} catch (Exception $e) {
    $data['debug'] = "Erreur SQL";
}

/**
 * 5. NETTOYAGE DU TAMPON
 * Supprime tout texte parasite (espaces, warnings) avant l'envoi du JSON
 */
while (ob_get_level() > 0) {
    ob_end_clean();
}

// 6. Envoi du flux JSON propre
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
exit;