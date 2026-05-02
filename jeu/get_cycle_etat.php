<?php

require_once("config.php");

// NETTOYAGE JSON DU BRUIT, TOUT ce qui a pu être écrit (espaces, erreurs PHP cachées)
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

// Sécurité verification si $is_night_now est normalement déjà calculée dans config.php
// Verif si booléen pour le JavaScript
$is_night = isset($is_night_now) ? (bool)$is_night_now : false;

$data = [
    "date_jeu" => $date_historique_complete ?? "01 Mars 1861",
    "is_night" => $is_night,
    "saison"   => $saison ?? "Printemps"
];

echo json_encode($data);
exit;