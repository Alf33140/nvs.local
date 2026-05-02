<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
	include ($phpbb_root_path .'config.php');
}

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Tableau des Multis déclarés</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<?php
			if (isset($_GET['detail_id']) && trim($_GET['detail_id']) != "") {
				
				$id_declaration = $_GET['detail_id'];
			?>
			<div class="row">
				<div class="col-12">
					<div align="center">
					<?php
					$sql = "SELECT situation FROM declaration_multi WHERE id_declaration='$id_declaration'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$situation = stripslashes($t['situation']);
					
					echo $situation;
					?>
					</div>
				</div>
			</div>
			<?php
			}
			?>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">					
						<div id="table_batiments" class="table-responsive">	
					
							<?php
							$sql = "SELECT id_declaration, user_id, target_id, user.nom_perso as user_name, target.nom_perso as target_name, user.clan as user_clan, target.clan as target_clan, situation, date_declaration FROM declaration_multi LEFT JOIN perso as user ON user_id=user.idJoueur_perso LEFT JOIN perso as target ON target_id=target.idJoueur_perso WHERE user.chef=1 AND target.chef=1 ORDER BY declaration_multi.user_id ASC";
							$res = $mysqli->query($sql);

							?>
							<table class='table'>
								<thead>
									<tr>
										<th style='text-align:center'>Perso qui déclare</th>
										<th style='text-align:center'>Multi</th>
										<th style='text-align:center'>Action</th>
									</tr>
								</thead>
								<tbody>
							<?php 
							while ($t = $res->fetch_assoc()) {
								
								$id_decla	= $t['id_declaration'];
								$id_perso	= $t['user_id'];
								$id_multi	= $t['target_id'];
								
								$nom_perso 	= $t['user_name'];
								$camp_perso	= $t['user_clan'];

								$nom_multi 	= $t['target_name'];
								$camp_multi	= $t['target_clan'];
								?>
									<tr>
										<td align='center'><?= $nom_perso?> [<?=$id_perso?>]</td>
										<td align='center'><?= $nom_multi?> [<?=$id_multi?>]</td>
										<td align='center'><a href='admin_multi.php?detail_id=<?=$id_decla?>' class='btn btn-primary'>Consulter le détail</a></td>
									</tr>
							<?php
							}
							?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
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
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>
