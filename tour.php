<?php
@session_start();
require_once("fonctions.php");
require_once("jeu/f_carte.php");
require_once("mvc/model/Building.php");

$mysqli = db_connexion();

include ('nb_online.php');

if(isset($_SESSION["ID_joueur"])){

    $id_joueur = $_SESSION['ID_joueur'];

    $sql = "SELECT id_perso, nom_perso, pv_perso, x_perso, y_perso, clan, image_perso, convalescence, est_gele, UNIX_TIMESTAMP(DLA_perso) as DLA, UNIX_TIMESTAMP(date_gele) as DG FROM perso WHERE idJoueur_perso=$id_joueur AND chef=1";
    $res = $mysqli->query($sql);
    $t_chef = $res->fetch_assoc();

    $id          = $t_chef["id_perso"];
    $dla         = $t_chef["DLA"];
    $clan        = $t_chef["clan"];
    $est_gele    = $t_chef["est_gele"];
    $date_gele   = $t_chef["DG"];
    $pseudo      = $t_chef["nom_perso"];
    $pv          = $t_chef["pv_perso"];
    $image_perso = $t_chef["image_perso"];
    $x_perso     = $t_chef["x_perso"];
    $y_perso     = $t_chef["y_perso"];

    $couleur_clan_p = ($clan == '1') ? 'blue' : 'red';

    if (isset($_SESSION["id_perso"]) && $_SESSION["id_perso"] != $id) {

        $id_perso = $_SESSION["id_perso"];
        $sql = "SELECT count(id_perso) as nb_perso FROM perso WHERE id_perso = '$id_perso'";
        $res = $mysqli->query($sql);
        $tab = $res->fetch_assoc();

        if ($tab["nb_perso"] == 1) {

            $sql = "SELECT idJoueur_perso, nom_perso, x_perso, y_perso, pv_perso, pvMax_perso, image_perso, chef, convalescence FROM perso WHERE id_perso='$id_perso'";
            $res = $mysqli->query($sql);
            $t_perso = $res->fetch_assoc();

            if ($t_perso["idJoueur_perso"] == $id_joueur) {
                $date = time();
                if (nouveau_tour($date, $dla)) {
                    $new_dla = $date + DUREE_TOUR;
                    nouveau_tour_joueur($mysqli, $id_joueur, $new_dla, $clan, $couleur_clan_p);
                    header("Location:jeu/jouer.php");
                    die();
                } else {
                    if ($t_perso["pv_perso"] <= 0) {
                        respawn_perso($mysqli, $id_perso, $t_perso["nom_perso"], $t_perso["x_perso"], $t_perso["y_perso"], $t_perso["image_perso"], $clan, $couleur_clan_p, $t_perso["chef"]);
                        header("location:jeu/jouer.php");
                    } else {
                        header("location:jeu/jouer.php");
                    }
                }
            } else {
                $_SESSION = array();
                session_destroy();
                header("location:index.php");
            }
        } else {
            $_SESSION["id_perso"] = $id;
            header("location:jeu/jouer.php");
        }
    } else {

        $_SESSION["id_perso"]  = $id;
        $_SESSION["nom_perso"] = $pseudo;
        $date = time();

        if($est_gele && temp_degele($date, $date_gele)){
            // Logique de dégèl complet
            $sql_d = "SELECT id_perso, nom_perso, x_perso, y_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
            $res_d = $mysqli->query($sql_d);
            while ($t_persos = $res_d->fetch_assoc()) {
                $id_p_d = $t_persos["id_perso"];
                $mysqli->query("UPDATE perso SET est_gele='0', date_gele=NULL, a_gele='0' WHERE id_perso='$id_p_d'");
                $id_instance_bat = selection_bat_retour_perm($mysqli, $id_p_d, $t_persos["x_perso"], $t_persos["y_perso"], $clan);
                if ($id_instance_bat) {
                    $enterInBat = new Building();
                    $enterInBat->insertCharacters([$id_p_d], $id_instance_bat);
                }
            }
            header("location:jeu/jouer.php");
        } else {
            if (!$est_gele && nouveau_tour($date, $dla)) {
                $new_dla = $date + DUREE_TOUR;
                nouveau_tour_joueur($mysqli, $id_joueur, $new_dla, $clan, $couleur_clan_p);
                header("location:jeu/jouer.php");
                die();
            } else {
                if ($pv > 0) {
                    header("location:jeu/jouer.php");
                } else {
                    respawn_perso($mysqli, $id, $pseudo, $x_perso, $y_perso, $image_perso, $clan, $couleur_clan_p, 1);
                    header("location:jeu/jouer.php");
                }
            }
        }
    }
} else {
    echo "<font color=red>Veuillez vous logguer.</font>";
}

function nouveau_tour_joueur($mysqli, $id_joueur, $new_dla, $clan, $couleur_clan_p) {
    // 1. SELECT (On garde compteur_pa et compteur_pm, pas besoin de compteur_pv)
    $sql = "SELECT id_perso, pa_perso, paMax_perso, pm_perso, pmMax_perso, bonusPM_perso, pv_perso, pvMax_perso,
                   recup_perso, bonusRecup_perso, bonus_perso, type_perso, chef, est_renvoye,
                   compteur_pm, compteur_pa, UNIX_TIMESTAMP(DLA_perso) as dla_timestamp
            FROM perso WHERE idJoueur_perso='$id_joueur'";
    $res = $mysqli->query($sql);

    while ($t_persos = $res->fetch_assoc()) {
        $id_p = $t_persos["id_perso"];
        $dla_old = $t_persos["dla_timestamp"];

        // --- 1. RÈGLE RÉCUPÉRATION PV (Arrondi à l'entier supérieur via ceil) ---
        $recup_totale = $t_persos["recup_perso"] + $t_persos["bonusRecup_perso"];
        $gain_pv = ($recup_totale > 0) ? ceil($recup_totale / 44) : 0;
        $new_pv  = min($t_persos["pvMax_perso"], $t_persos["pv_perso"] + $gain_pv);

        // --- 2. RÈGLE RÉCUPÉRATION PA (Avec Compteur) ---
        $pa_max = $t_persos["paMax_perso"];
        $c_pa   = $t_persos["compteur_pa"];
        $new_pa = $t_persos["pa_perso"];

        if ($new_pa < $pa_max) {
            $c_pa += ($pa_max / 44);
            if ($c_pa >= 1) {
                $new_pa += 1;
                $c_pa -= 1;
            }
        } else {
            $c_pa = 0;
        }

        // --- 3. RÈGLE RÉCUPÉRATION PM (Avec Compteur) ---
        $pm_max = $t_persos["pmMax_perso"] + $t_persos["bonusPM_perso"];
        $c_pm   = $t_persos["compteur_pm"];
        $new_pm = $t_persos["pm_perso"];

        if ($new_pm < $pm_max) {
            $c_pm += ($pm_max / 44);
            if ($c_pm >= 1) {
                $new_pm += 1;
                $c_pm -= 1;
            }
        } else {
            $c_pm = 0;
        }

        // --- 4. GAINS OR / PC ---
        $g_or = 0; $g_pc = 0;
        if (($new_dla - $dla_old) >= (44 * 3600)) {
            if ($t_persos["chef"] == '1') {
                $g_or = 3; $g_pc = 1;
            } elseif (!$t_persos["est_renvoye"]) {
                $g_or = gain_or_grouillot($t_persos["type_perso"]);
            }
        }

        $new_b = ($t_persos["bonus_perso"] + 5 <= 0) ? $t_persos["bonus_perso"] + 5 : 0;

        // --- MAJ CARACT---
        $mysqli->query("UPDATE perso SET
            pv_perso='$new_pv',
            pa_perso='$new_pa',
            pm_perso='$new_pm',
            compteur_pm='$c_pm',
            compteur_pa='$c_pa',
            or_perso=or_perso+$g_or,
            pc_perso=pc_perso+$g_pc,
            bonus_perso='$new_b',
            DLA_perso=FROM_UNIXTIME($new_dla)
            WHERE id_perso='$id_p'");
    }
}

                // --- Si le perso est RIP ---
                if ($new_pv_perso <= 0) {

            // ---------------------- //
            //    RESPAWN BATIMENT    //
            // ---------------------- //

            // Récupération du batiment de rappatriement le plus proche du perso
            $id_instance_bat = selection_bat_rapat($mysqli, $id_perso_nouveau_tour, $x_perso_nouveau_tour, $y_perso_nouveau_tour, $clan);

            // Batiment trouvé
            if ($id_instance_bat != null && $id_instance_bat != 0) {

                // récupération coordonnées batiment
                $sql_b = "SELECT x_instance, y_instance, id_batiment FROM instance_batiment WHERE id_instanceBat='$id_instance_bat'";
                $res_b = $mysqli->query($sql_b);
                $t_b = $res_b->fetch_assoc();

                $x         = $t_b['x_instance'];
                $y         = $t_b['y_instance'];
                $id_bat    = $t_b['id_batiment'];

                // On met le perso dans le batiment
                $enterInBat = new Building();
                $enterInBat = $enterInBat->insertCharacters([$id_perso_nouveau_tour],$id_instance_bat);

                // mise a jour des evenements
                $sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special)
                        VALUES ('$id_perso_nouveau_tour','<font color=$couleur_clan_p><b>$nom_perso_nouveau_tour</b></font>','a été rapatrié',NULL,'','dans le bâtiment $id_instance_bat en $x/$y',NOW(),'0')";
                $mysqli->query($sql);

                // Rapat Chef dans Fort ou Fortin
                if ($chef_perso_nouveau_tour && ($id_bat == 8 || $id_bat == 9)) {

                    // recup grade / pc chef
                    $sql_chef = "SELECT perso.id_perso, pc_perso, perso_as_grade.id_grade FROM perso, perso_as_grade
                                    WHERE perso.id_perso = perso_as_grade.id_perso AND perso.id_perso='$id_perso_nouveau_tour'";
                    $res_chef = $mysqli->query($sql_chef);
                    $t_chef = $res_chef->fetch_assoc();

                    $id_perso_chef = $t_chef["id_perso"];
                    $pc_perso_chef = $t_chef["pc_perso"];
                    $id_grade_chef = $t_chef["id_grade"];

                    // Verification passage de grade
                    $sql_grade = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
                    $res_grade = $mysqli->query($sql_grade);
                    $t_grade = $res_grade->fetch_assoc();

                    $id_grade_final     = $t_grade["id_grade"];
                    $nom_grade_final    = $t_grade["nom_grade"];

                    if ($id_grade_chef < $id_grade_final) {

                        // Passage de grade
                        $sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_nouveau_tour'";
                        $mysqli->query($sql);

                        // mise a jour des evenements
                        $sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special)
                                VALUES ('$id_perso_nouveau_tour','<font color=$couleur_clan_p><b>$nom_perso_nouveau_tour</b></font>','a été promu <b>$nom_grade_final</b> !',NULL,'','',NOW(),'0')";
                        $mysqli->query($sql);

                        // maj CV
                        $sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>', '$nom_grade_final', NULL, NULL, NULL, NOW(), 9)";
                        $mysqli->query($sql);
                    }
                }

            } else {

                // Récupération des zones de respawn hors batiment
                $sql = "SELECT * FROM zone_respawn_camp WHERE id_camp='$clan'";
                $res = $mysqli->query($sql);
                $t = $res->fetch_assoc();

                $x_min_zone_def = $t['x_min_zone'];
                $x_max_zone_def = $t['x_max_zone'];
                $y_min_zone_def = $t['y_min_zone'];
                $y_max_zone_def = $t['y_max_zone'];

                if (isset($x_min_zone_def)) {
                    $x_min_respawn = $x_min_zone_def;
                    $x_max_respawn = $x_max_zone_def;
                    $y_min_respawn = $y_min_zone_def;
                    $y_max_respawn = $y_max_zone_def;
                }
                else {
                    // Récupération coordonnées MAX de la carte
                    $sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
                    $res = $mysqli->query($sql);
                    $t = $res->fetch_assoc();

                    $X_MAX     = $t['x_max'];
                    $Y_MAX  = $t['y_max'];

                    if ($clan == 1){
                        // bleu
                        $x_min_respawn = $X_MAX - 40;
                        $x_max_respawn = $X_MAX;
                        $y_min_respawn = $Y_MAX - 40;
                        $y_max_respawn = $Y_MAX;
                    }

                    if ($clan == 2){
                        // rouge
                        $x_min_respawn = 0;
                        $x_max_respawn = 40;
                        $y_min_respawn = 0;
                        $y_max_respawn = 40;
                    }
                }

                // on le replace aleatoirement sur la carte
                $occup = 1;
                while ($occup == 1)
                {
                    $x = pos_zone_rand_x($x_min_respawn, $x_max_respawn);
                    $y = pos_zone_rand_y($y_min_respawn,$y_max_respawn);
                    $occup = verif_pos_libre($mysqli, $x, $y);
                }

                $sql = "UPDATE carte SET occupee_carte = '1', image_carte='$image_perso_nouveau_tour', idPerso_carte='$id_perso_nouveau_tour' WHERE x_carte='$x' AND y_carte='$y'";
                $mysqli->query($sql);

                // mise a jour des evenements
                $sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special)
                        VALUES ('$id_perso_nouveau_tour','<font color=$couleur_clan_p><b>$nom_perso_nouveau_tour</b></font>','a été rapatrié',NULL,'','en $x/$y',NOW(),'0')";
                $mysqli->query($sql);
            }

            $sql = "SELECT fond_carte FROM carte WHERE x_carte=$x AND y_carte=$y";
            $res_map = $mysqli->query($sql);
            $t_carte1 = $res_map->fetch_assoc();

            $fond = $t_carte1["fond_carte"];

            // calcul bonus perception perso
            $bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso_nouveau_tour);

            // Calcul PM avec malus rapat
            $pm_nouveau = ($pm_max_perso_nouveau_tour / 2) + $bonusPM_nouveau_tour;

            // MAJ perso avec malus rapat
            // Modification : ajout compteur_pm=0 et mise en commentaire des PA
            $sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=$pm_nouveau, compteur_pm=0, /* pa_perso=paMax_perso/2 + bonusPA_perso, */ pv_perso=pvMax_perso, or_perso=or_perso+$gain_or, bonusPerception_perso=$bonus_visu, bonus_perso=0, bourre_perso=0, convalescence=0, est_gele=0, gain_xp_tour=0, DLA_perso=FROM_UNIXTIME($new_dla)
                    WHERE id_perso='$id_perso_nouveau_tour'";
            $mysqli->query($sql);
        }
        else {

            $sql = "SELECT fond_carte FROM carte WHERE x_carte=$x_perso_nouveau_tour AND y_carte=$y_perso_nouveau_tour";
            $res_map = $mysqli->query($sql);
            $t_carte1 = $res_map->fetch_assoc();

            $fond = $t_carte1["fond_carte"];

            // Gestion convalescence
            if ($convalescence_nouveau_tour) {
                $pm_nouveau = ($pm_max_perso_nouveau_tour / 2) + $bonusPM_nouveau_tour;
                $pa_nouveau    = $pa_max_perso_nouveau_tour / 2;
            }
            else {
                $pm_nouveau = $pm_max_perso_nouveau_tour + $bonusPM_nouveau_tour;
                $pa_nouveau    = $pa_max_perso_nouveau_tour;
            }

            // Prise en compte malus PM des bousculades (PM négatifs)
            if ($pm_perso_nouveau_tour < 0) {
                $pm_nouveau += $pm_perso_nouveau_tour;
            }

            // Bonus recup batiment
            $bonus_recup_bat = get_bonus_recup_bat_perso($mysqli, $id_perso_nouveau_tour);

            // bonus recup terrain
            $bonus_recup_terrain = get_malus_recup($fond);

            // Calcul pv nouveau tour
            $pv_nouveau = $pv_perso_nouveau_tour + $recup_perso_nouveau_tour + $bonus_recup_bat + $bonus_recup_terrain;
            if ($pv_nouveau > $pv_max_perso_nouveau_tour) {
                $pv_nouveau = $pv_max_perso_nouveau_tour;
            } else if ($pv_nouveau <= 0) {
                // ne tue pas le perso
                $pv_nouveau = 1;
            }

            // calcul bourre perso
            $bourre_perso = $bourre_perso_nouveau_tour - 1;
            if ($bourre_perso < 0) {
                $bourre_perso = 0;
            }

            $sql = "UPDATE perso SET pm_perso=$pm_nouveau, pa_perso=$pa_nouveau+bonusPA_perso, pv_perso=$pv_nouveau, or_perso=or_perso+$gain_or, pc_perso=pc_perso+$gain_pc, bonusRecup_perso=0, bonus_perso=$new_bonus_perso, bourre_perso=$bourre_perso, convalescence=0, est_gele=0, gain_xp_tour=0, DLA_perso=FROM_UNIXTIME($new_dla)
                    WHERE id_perso='$id_perso_nouveau_tour'";
            $mysqli->query($sql);


        }

        // On decremente le compteur genie si il est > 1
        if ($genie_nouveau_tour > 1) {
            $sql = "UPDATE perso SET genie = genie - 1 WHERE id_perso = '$id_perso_nouveau_tour'";
            $mysqli->query($sql);
        }
    


            /**
             * Fonction qui gère le respawn d'un perso sans nouveau tour
             */
            function respawn_perso($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $image_perso, $clan, $couleur_clan_p, $chef_perso) {

                // ---------------------- //
                //    RESPAWN BATIMENT    //
                // ---------------------- //

                // Récupération du batiment de rappatriement le plus proche du perso
                $id_instance_bat = selection_bat_rapat($mysqli, $id_perso, $x_perso, $y_perso, $clan);

                if ($id_instance_bat != null && $id_instance_bat != 0) {

                    // récupération coordonnées batiment
                    $sql_b = "SELECT x_instance, y_instance, id_batiment FROM instance_batiment WHERE id_instanceBat='$id_instance_bat'";
                    $res_b = $mysqli->query($sql_b);
                    $t_b = $res_b->fetch_assoc();

                    $x         = $t_b['x_instance'];
                    $y         = $t_b['y_instance'];
                    $id_bat    = $t_b['id_batiment'];

                    // On supprime le perso de la carte s'il y est toujours
                    $sql = "UPDATE carte SET occupee_carte = '0', image_carte=NULL, idPerso_carte=NULL, save_info_carte=NULL WHERE idPerso_carte='$id_perso'";
                    $mysqli->query($sql);

                    // On met le perso dans le batiment
                    $enterInBat = new Building();
                    $enterInBat = $enterInBat->insertCharacters([$id_perso],$id_instance_bat);

                    // mise a jour des evenements
                    $sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special)
                            VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été rapatrié',NULL,'','dans le bâtiment $id_instance_bat en $x/$y',NOW(),'0')";
                    $mysqli->query($sql);

                    // Rapat Chef dans Fort ou Fortin
                    if ($chef_perso && ($id_bat == 8 || $id_bat == 9)) {

                       // 1. Récupération des données (On ajoute xp_perso dans le SELECT)
                    $sql = "SELECT perso.id_perso, pc_perso, xp_perso, perso_as_grade.id_grade 
                            FROM perso, perso_as_grade 
                            WHERE perso.id_perso = perso_as_grade.id_perso 
                            AND perso.id_perso='$id_perso'";
                    $res = $mysqli->query($sql);
                    $t_chef = $res->fetch_assoc();

                    $id_perso_chef = $t_chef["id_perso"];
                    $pc_perso_chef = $t_chef["pc_perso"];
                    $xp_perso_chef = $t_chef["xp_perso"]; // Nouvelle variable pour les grouillots
                    $id_grade_chef = $t_chef["id_grade"];

                    $id_grade_final = $id_grade_chef;
                    $nom_grade_final = "";

                    // 2. DISTINCTION : Grouillot (Grade 1 ou 101+) vs Chef (Grade 2 à 18)
                    if ($id_grade_chef == 1 || $id_grade_chef >= 101) {
                        
                        // --- LOGIQUE GROUILLOT (XP) ---
                        $paliers = get_paliers_xpi_grouillots();
                        
                        // Test spécifique pour l'Appelé (ID 1)
                        if ($id_grade_chef == 1 && $xp_perso_chef >= 300) {
                            $id_grade_final = 101;
                        } 
                        // Test pour les autres paliers (101 -> 107)
                        else {
                            foreach ($paliers as $id_g => $xp_seuil) {
                                if ($xp_perso_chef >= $xp_seuil && $id_g >= $id_grade_chef) {
                                    $id_grade_final = $id_g + 1;
                                }
                            }
                        }
                        
                        // Récupération du nom si le grade a changé
                        if ($id_grade_final != $id_grade_chef) {
                            $res_n = $mysqli->query("SELECT nom_grade FROM grades WHERE id_grade = '$id_grade_final'");
                            $t_n = $res_n->fetch_assoc();
                            $nom_grade_final = $t_n['nom_grade'];
                        }

                    } else {
                        
                        // --- LOGIQUE CHEF (PC) ---
                        $sql_grade = "SELECT id_grade, nom_grade FROM grades 
                                    WHERE pc_grade <= $pc_perso_chef AND pc_grade != 0 
                                    ORDER BY pc_grade DESC LIMIT 1";
                        $res_grade = $mysqli->query($sql_grade);
                        $t_grade = $res_grade->fetch_assoc();

                        $id_grade_final  = $t_grade["id_grade"];
                        $nom_grade_final = $t_grade["nom_grade"];
                    }
                    }
                    // 3. MISE À JOUR COMMUNE (si promotion il y a)
                    if ($id_grade_final > $id_grade_chef) {

                        // Passage de grade en base
                        $sql_up = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso'";
                        $mysqli->query($sql_up);

                        // Mise à jour des événements
                        $sql_ev = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, date_evenement, special)
                                VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été promu <b>$nom_grade_final</b> !', NOW(),'0')";
                        $mysqli->query($sql_ev);

                        // Mise à jour CV
                        $sql_cv = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, date_cv, special) 
                                VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>', '$nom_grade_final', NOW(), 9)";
                        $mysqli->query($sql_cv);
                    }
                } else {

                    // Récupération des zones de respawn hors batiment
                    $sql = "SELECT * FROM zone_respawn_camp WHERE id_camp='$clan'";
                    $res = $mysqli->query($sql);
                    $t = $res->fetch_assoc();

                    $x_min_zone_def = $t['x_min_zone'];
                    $x_max_zone_def = $t['x_max_zone'];
                    $y_min_zone_def = $t['y_min_zone'];
                    $y_max_zone_def = $t['y_max_zone'];

                    if (isset($x_min_zone_def)) {
                        $x_min_respawn = $x_min_zone_def;
                        $x_max_respawn = $x_max_zone_def;
                        $y_min_respawn = $y_min_zone_def;
                        $y_max_respawn = $y_max_zone_def;
                    }
                    else {
                        // Récupération coordonnées MAX de la carte
                        $sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
                        $res = $mysqli->query($sql);
                        $t = $res->fetch_assoc();

                        $X_MAX     = $t['x_max'];
                        $Y_MAX  = $t['y_max'];

                        if ($clan == 1){
                            // bleu
                            $x_min_respawn = $X_MAX - 40;
                            $x_max_respawn = $X_MAX;
                            $y_min_respawn = $Y_MAX - 40;
                            $y_max_respawn = $Y_MAX;
                        }

                        if ($clan == 2){
                            // rouge
                            $x_min_respawn = 0;
                            $x_max_respawn = 40;
                            $y_min_respawn = 0;
                            $y_max_respawn = 40;
                        }
                    }

                    // on le replace aleatoirement sur la carte
                    $occup = 1;
                    while ($occup == 1)
                    {
                        $x = pos_zone_rand_x($x_min_respawn, $x_max_respawn);
                        $y = pos_zone_rand_y($y_min_respawn,$y_max_respawn);
                        $occup = verif_pos_libre($mysqli, $x, $y);
                    }

                    $sql = "UPDATE carte SET occupee_carte = '1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x' AND y_carte='$y'";
                    $mysqli->query($sql);

                    // mise a jour des evenements
                    $sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special)
                            VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été rapatrié',NULL,'','en $x/$y',NOW(),'0')";
                    $mysqli->query($sql);
                }

                $sql = "SELECT fond_carte FROM carte WHERE x_carte=$x AND y_carte=$y";
                $res_map = $mysqli->query($sql);
                $t_carte1 = $res_map->fetch_assoc();

                $fond = $t_carte1["fond_carte"];

                // calcul bonus perception perso
                $bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);

                // MAJ perso rapat (Prise en compte recup progressive caract : mise à zéro PM, PA, compteur_pm ET compteur_pa)
                $sql = "UPDATE perso SET
                            x_perso='$x',
                            y_perso='$y',
                            pm_perso=0,
                            pa_perso=0,
                            compteur_pm=0,
                            compteur_pa=0,
                            pv_perso=pvMax_perso,
                            bonusPerception_perso=$bonus_visu,
                            bourre_perso=0,
                            bonus_perso=0,
                            convalescence=1,
                            est_gele=0
                        WHERE id_perso='$id_perso'";
                $mysqli->query($sql);
            }
            ?>