<?php
/**
 * cron_meteo.php - Génération automatique d'événements climatiques sans collision
 */

require_once(__DIR__ . "/config.php");

$mysqli = new mysqli(BDD_HOST, BDD_LOGIN, BDD_PASSWORD, BDD_NAME);

if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// --- 1. NETTOYAGE ---
$mysqli->query("DELETE FROM meteo WHERE date_fin < (NOW() - INTERVAL 1 DAY)");

// --- 2. CONFIGURATION ---
$nb_events = rand(3, 5);
$types = ['pluie', 'brouillard', 'tempete', 'orage'];
$carte_max_x = 100;
$carte_max_y = 100;

echo "<h2>Démarrage de la génération météo sans collisions...</h2>";

// --- 3. GÉNÉRATION ---
for ($i = 0; $i < $nb_events; $i++) {

    $collision = true;
    $essais = 0;
    $max_essais = 50; // Sécurité pour éviter les boucles infinies

    while ($collision && $essais < $max_essais) {
        $essais++;

        $type   = $types[array_rand($types)];
        $x      = rand(1, $carte_max_x);
        $y      = rand(1, $carte_max_y);
        $rayon  = rand(10, 25);

        // On vérifie si ce nouveau cercle touche un événement déjà existant en BDD qui est actif
        // Formule : Distance(C1, C2) < (R1 + R2)
        // En SQL, on utilise Pythagore pour éviter la racine carrée (plus rapide) : (x2-x1)² + (y2-y1)² < (r1+r2)²
        $sql_check = "SELECT id_meteo FROM meteo
                      WHERE date_fin > NOW()
                      AND (POWER(x_center - $x, 2) + POWER(y_center - $y, 2) < POWER(rayon + $rayon, 2))";

        $res_check = $mysqli->query($sql_check);

        if ($res_check->num_rows == 0) {
            $collision = false; // Aucune collision trouvée !
        }
    }

    if ($essais >= $max_essais) {
        echo "?? Impossible de trouver une place pour l'événement $i après $max_essais tentatives.<br>";
        continue; // On passe au suivant
    }

    // Calcul des dates
    $delai_debut = ($i == 0) ? 0 : rand(0, 7);
    $duree_event = rand(2, 4);
    $debut = date("Y-m-d H:i:s", strtotime("+$delai_debut hours"));
    $fin   = date("Y-m-d H:i:s", strtotime("+" . ($delai_debut + $duree_event) . " hours"));

    $type_sql = $mysqli->real_escape_string($type);

    $sql = "INSERT INTO meteo (type_meteo, x_center, y_center, rayon, date_debut, date_fin)
            VALUES ('$type_sql', $x, $y, $rayon, '$debut', '$fin')";

    if ($mysqli->query($sql)) {
        echo "? Créé : <b>$type</b> à ($x,$y) | Rayon: $rayon | Début: $debut<br>";
    } else {
        echo "? Erreur : " . $mysqli->error . "<br>";
    }
}

$mysqli->close();
echo "<br><b>Cycle de génération terminé !</b>";
?>