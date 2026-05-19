<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	//recuperation des variables de sessions
	$session_id_perso = $_SESSION["id_perso"];
	
	$mess 		= "";
	$mess_err 	= "";
	
	$title = 'bataillon';
	
	if(isset($_GET["id_bataillon"])){
		$id_joueur_bataillon = intval($_GET["id_bataillon"]);

		// récupération des infos du perso connecté 
		$sql = "SELECT idJoueur_perso, clan, bataillon FROM perso WHERE id_perso=$session_id_perso";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$current_id_user = $t['idJoueur_perso'];
		$current_bataillon = $t['bataillon'];
		$current_camp_bataillon = $t['clan'];
		
		// récupération des infos du perso demandé 
		$sql = "SELECT id_perso, idJoueur_perso, clan, bataillon FROM perso WHERE idJoueur_perso=$id_joueur_bataillon";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$selected_id_user = $t['idJoueur_perso'] ?? '';
		$selected_bataillon = $t['bataillon'] ?? '';
		$selected_camp_bataillon = $t['clan'] ?? '';
		
		if($current_camp_bataillon == $selected_camp_bataillon){
			
			$sql = "SELECT perso.id_perso, nom_perso, grades.id_grade, nom_grade, nom_unite FROM perso, perso_as_grade, grades, type_unite
					WHERE perso.id_perso = perso_as_grade.id_perso 
					AND perso_as_grade.id_grade = grades.id_grade
					AND perso.type_perso = type_unite.id_unite
					AND perso.est_renvoye=0
					AND idJoueur_perso='$id_joueur_bataillon'";
			$res = $mysqli->query($sql);
			$battalion = $res->fetch_all(MYSQLI_ASSOC);

			if($current_id_user == $selected_id_user){
				if(isset($_POST["enregistrer"])){
					if(!empty(trim($_POST['nomBataillon']))) {
						$nouveau_nom_bataillon = htmlspecialchars(stripslashes(trim($_POST['nomBataillon'])));
						
						$sql = "INSERT INTO perso_demande_anim (id_perso, type_demande, info_demande) VALUES ('$id_joueur_bataillon', '3', '$nouveau_nom_bataillon')";
						$mysqli->query($sql);
						
						$mess .= "Demande de changement de nom de bataillon en '".$_POST['nomBataillon']."' tranmis avec succès.";
					}else{
						$mess_err= '<div class="alert alert-danger w-25">Le champ "Nouveau nom de bataillon" est obligatoire</div>';
					}
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Le jeu au tour par tour sur la guerre de sécession">
		<meta name="author" content="Equipe NvS">

        <title><?php if($title){echo $title.' - ';}?>Nord vs Sud</title><!--1861 : Blood and War-->
		
		<!--<link rel="shortcut icon" href="public/favicon.ico">-->
		<!--<link rel="icon" type="image/png" href="public/favicon.png">-->

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
		<!-- Bootstrap CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
		<link rel="stylesheet" href="../public/css/app.css">

        <!-- Scripts -->
		<!-- Bunddle Popper.js & Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" defer></script>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    </head>
	<body class='homepage-bg'>
		<div class="container">
			<div class="row">
				<div class='col-12 text-center mt-3'>
					<p><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
				</div>
				<div class="col-12 text-center title-ribbon mb-3">
					<h3>
						Bataillon<br>
						★ "<?php echo $selected_bataillon; ?>" ★
					</h3>
					<?php if ($current_id_user == $id_joueur_bataillon) { ?>
					<a href='bataillon.php?id_bataillon=<?=$id_joueur_bataillon?>&changer_nom=ok' class='btn btn-warning my-2'>Demander à changer le nom du bataillon</a>
					<?php }?>
				</div>
				<?php
				if (!empty(trim($mess))) {
						echo "<div class='alert alert-primary my-2'>".$mess."</div>";
					}
				if (!empty(trim($mess_err))) {
					echo "<div class='alert alert-danger my-2'>".$mess_err."</div>";
				}
				?>
			</div>
			<?php if ($current_id_user == $id_joueur_bataillon) { ?>
			<?php if (isset($_GET['changer_nom']) && $_GET['changer_nom'] == 'ok'): ?>
			<div class='row justify-content-center'>
				<div class='col-12 col-md-4 text-center p-3 bg-light rounded'>
					<form class='' method='POST' action='bataillon.php?id_bataillon=<?=$id_joueur_bataillon?>'>
						<div class=''>
							<label class='form-label' for='nomBataillon'>Nouveau nom de bataillon</label>
							<input type='text' class='form-control' id='nomBataillon' name='nomBataillon' maxlength='100'>
						</div>
						<input type='submit' class='btn btn-success mt-2' name='enregistrer' value='valider le changement de nom'>
					</form>
				</div>
			</div>
			<?php endif;?>
			<?php }?>
			
			<div class='row justify-content-center mt-4'>
<?php	if (!empty($battalion)) { ?>
					<table class="table table-striped">
						<tr>
							<th>Nom perso [matricule]</th>
							<th>Type d'unité</th>
							<th>Grade</th>
						</tr>
					<?php
					foreach($battalion as $t) {
						
						$id_perso	= $t['id_perso'];
						$nom_perso 	= $t['nom_perso'];
						$nom_grade 	= $t['nom_grade'];
						$nom_unite 	= $t['nom_unite'];
						$id_grade	= $t['id_grade'];
						
						// cas particuliers grouillot
						if ($id_grade == 101) {
							$id_grade = "1.1";
						}
						if ($id_grade == 102) {
							$id_grade = "1.2";
						}
						echo "<tr>";
						echo "	<td>";
						echo "		<a href=\"grades.php\" target='_blank'><img alt='". $nom_grade."' title='".$nom_grade."' src=\"../images/grades/" . $id_grade . ".gif\" width='40' height='40'></a>
									<a href=\"evenement.php?infoid=" . $id_perso . "\">". $nom_perso ." [" . $id_perso . "]";
						echo "	</td>";
						echo "	<td>" . $nom_unite . "</td>";
						echo "	<td>" . $nom_grade. "</td>";
						echo "</tr>";
					}
					}else{
						echo "<div class='mx-auto text-center alert alert-warning'>Pour voir le bataillon d'un joueur du camp adverse, vous devez disposer de la compétence \"Espionnage\" (évolution à venir)</div>";
					} ?>
					</table>
			</div>
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>
