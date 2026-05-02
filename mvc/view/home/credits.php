<?php
$title = "Crédits";

/* ---Content--- */
ob_start();
?>
<div class="row justify-content-center">
	<div class="col-12 bg-light bg-opacity-75 p-4">
		<h3>Crédits</h3>
		<p>
			Nord VS Sud est le fruit d'un travail collectif de joueurs bénévoles et passionnés.<br>
			C'est un projet open source reprenant les concepts des jeux de stratégie et jeux de guerres sur plateau tels que &copy;Risk ou &copy;Warhammer.<br>
			La version actuelle du jeu est communément appelée la version 3 (V3). Elle est librement inspirée du projet original datant des années 2000 et de sa relance en 2020 par Romain P.
		</p>
		<h3>Remerciements</h3>
		Nous tenons à remercier :
		<ul>
			<li>Romain P., développeur à l'origine de la version 2 (V2) du jeu et administrateur historique de celui-ci. Reviens quand tu veux, jouer ou contribuer ;)</li>
			<li>Les joueurs $kulL, James Winter, Augustus Winter, Geoff McDubh, Charly Cœur, Martin Luther King, Jedd Elzey, Furie pour leur implication et leur aide durant l'Alpha,</li>
			<li>Tous les joueurs qui participent ou ont participé au développement et à l'ambiance du jeu,</li>
			<li>L'ensemble de nos proches qui nous soutiennent et nous supportent dans cette aventure riche en rebondissements.</li>
		</ul>
		<h3>D'après une idée originale</h3>
		<p>
			 de	GrOOn et Keldrilh, créateurs du jeu Nord versus Sud version 1 (V1). 
		</p>
	</div>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/guest.php'); ?>
