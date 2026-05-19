<?php
session_start();
require_once("../fonctions.php");
require_once("../mvc/model/Account.php");
require_once("../mvc/model/Company.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
    include ($phpbb_root_path .'config.php');
}

$dispo = config_dispo_jeu($mysqli);

if(isset($_SESSION["id_perso"])){

    $admin = admin_perso($mysqli, $_SESSION["id_perso"]);

    if($dispo == '1' || $admin){

        $id = $_SESSION["id_perso"];

        $sql = "SELECT pv_perso, clan FROM perso WHERE id_perso='$id'";
        $res = $mysqli->query($sql);
        $tpv = $res->fetch_assoc();

        $testpv = $tpv['pv_perso'];
        $clan_perso = $tpv['clan'];

        if ($testpv <= 0) {
            echo "<font color=red>Vous êtes mort...</font>";
        }
        else {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Nord VS Sud - Recrutement</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <style>
            body {
                background: url("../public/img/backgrounds/recruteur.png");
               background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-color: #333;
    }
    .main-container {
        /* On passe de 0.95 à 0.70 pour plus de transparence */
        background-color: rgba(255, 255, 255, 0.7) !important;
        border-radius: 15px;
        padding: 30px;
        margin-top: 50px;
        margin-bottom: 50px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        /* Ajout d'un léger flou derrière la carte pour garder le texte lisible */
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }
    .table {
        /* Les tableaux restent opaques pour que les données soient faciles à lire */
        background-color: rgba(255, 255, 255, 0.9);
    }
    h2, h4 {
        color: #000;
        font-weight: bold;
        text-shadow: 1px 1px 1px rgba(255,255,255,0.8);
    }
</style>
    </head>

    <body>
        <div class="container main-container">
            <div align="center" class="mt-2"><h2>Recrutement et Gestion</h2></div>

    <?php
    if(isset($_GET["id_compagnie"])) {

        $id_compagnie = $_GET["id_compagnie"];
        if(preg_match("#^[0-9]+$#i", $id_compagnie)){

            $sql = "SELECT id_compagnie, poste_compagnie FROM perso_in_compagnie WHERE id_perso='$id' AND id_compagnie='$id_compagnie' AND (poste_compagnie='4' OR poste_compagnie='1' OR poste_compagnie='2')";
            $res = $mysqli->query($sql);

            if($res->num_rows){

                $company_model = new Company();
                $company = $company_model->select('compagnies.id_compagnie, compagnies.genie_civil, compagnies.nom_compagnie, banque_as_compagnie.id as bank_id')
                                         ->leftJoin('banque_as_compagnie','compagnies.id_compagnie','=','banque_as_compagnie.id_compagnie')
                                         ->find($id_compagnie);

                $genie_compagnie = $company->genie_civil;
                $nom_compagnie   = $company->nom_compagnie;
                $nb_max = ($genie_compagnie) ? 60 : 80;

                // --- TRAITEMENT RECRUTEMENT ---
                if (isset($_POST["valider_recrue"]) && isset($_POST["id_recrue"])){
                    $new_recrue = $_POST["id_recrue"];
                    $sql_n = "SELECT nom_perso FROM perso WHERE id_perso='$new_recrue'";
                    $res_n = $mysqli->query($sql_n);
                    $t_n = $res_n->fetch_assoc();
                    $nom_recrue = $t_n['nom_perso'];

                    $mysqli->query("UPDATE perso_in_compagnie SET attenteValidation_compagnie='0' WHERE id_perso=$new_recrue");

                    $account = new Account();
                    $account->id_perso = $new_recrue;
                    $account->bank_id = $company->bank_id;
                    $account->save();

                    if ($genie_compagnie) {
                        $comp_ids = [23, 24, 27, 28, 63, 64];
                        foreach($comp_ids as $c_id) {
                            $mysqli->query("INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$new_recrue', '$c_id', '1')");
                        }
                        $mysqli->query("UPDATE perso SET genie='8' WHERE id_perso='$new_recrue'");
                    }

                    echo "<div class='alert alert-success text-center'>$nom_recrue a rejoint la compagnie !</div>";
                }

                // --- TABLEAU 1 : DEMANDES D'INCORPORATION (TA COMPAGNIE) ---
                echo "<div class='mt-4'>
                        <h4 class='text-center text-success'>Demandes d'incorporation reçues</h4>
                        <table class='table table-bordered text-center'>
                            <thead class='thead-light'>
                                <tr>
                                    <th>Pseudo</th>
                                    <th>Matricule (ID)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>";

                $sql_demandes = "SELECT p.nom_perso, p.id_perso
                                 FROM perso p, perso_in_compagnie pic
                                 WHERE p.id_perso = pic.id_perso
                                 AND pic.id_compagnie = '$id_compagnie'
                                 AND pic.attenteValidation_compagnie = '1'";
                $res_demandes = $mysqli->query($sql_demandes);

                if($res_demandes->num_rows > 0) {
                    while($t_d = $res_demandes->fetch_assoc()){
                        echo "<tr>
                                <td><b>{$t_d['nom_perso']}</b></td>
                                <td>{$t_d['id_perso']}</td>
                                <td>
                                    <form method='post'>
                                        <input type='hidden' name='id_recrue' value='{$t_d['id_perso']}'>
                                        <input type='submit' name='valider_recrue' class='btn btn-success btn-sm' value='Valider l’incorporation'>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Aucune demande en attente pour votre compagnie.</td></tr>";
                }
                echo "</tbody></table></div>";

                // --- TABLEAU 2 : NOUVEAUX DU CLAN SANS COMPAGNIE ---
                echo "<div class='mt-5'>
                        <h4 class='text-center'>Personnages du Clan sans compagnie</h4>
                        <table class='table table-striped table-bordered mt-3 small'>
                            <thead class='thead-dark'><tr><th>Nom [ID]</th><th>Statut</th><th>Compagnie ciblée</th></tr></thead>
                            <tbody>";

                $sql_clan = "SELECT p.id_perso, p.nom_perso, pic.attenteValidation_compagnie, c.nom_compagnie
                             FROM perso p
                             LEFT JOIN perso_in_compagnie pic ON p.id_perso = pic.id_perso
                             LEFT JOIN compagnies c ON pic.id_compagnie = c.id_compagnie
                             WHERE p.clan = '$clan_perso'
                             AND (pic.id_perso IS NULL OR pic.attenteValidation_compagnie = '1')
                             AND (pic.id_compagnie != '$id_compagnie' OR pic.id_compagnie IS NULL)
                             ORDER BY p.id_perso DESC LIMIT 15";

                $res_clan = $mysqli->query($sql_clan);
                while($t_c = $res_clan->fetch_assoc()){
                    $statut = ($t_c['attenteValidation_compagnie'] == '1') ? "<span class='badge badge-warning'>En attente</span>" : "<span class='badge badge-secondary'>Libre</span>";
                    $nom_cie = $t_c['nom_compagnie'] ?? '-';
                    echo "<tr><td>{$t_c['nom_perso']} [{$t_c['id_perso']}]</td><td>$statut</td><td>$nom_cie</td></tr>";
                }
                echo "</tbody></table></div>";

                echo "<div class='text-center mt-4 mb-4'><a href='compagnie.php' class='btn btn-outline-secondary'>Retour</a></div>";

            } else { echo "<div class='alert alert-danger text-center'>Droits insuffisants.</div>"; }
        }
    }
    ?>
        </div> <!-- Fin main-container -->

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    </body>
</html>
<?php
        }
    }
}
?>