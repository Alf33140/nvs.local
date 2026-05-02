<?php
$title = "Banque de compagnie";

/* ---Header--- */
ob_start();
?>
<div class='background-img bankBg'>
</div>
<div class="row justify-content-center">
	<div class="col mx-2 rounded bg-light py-3 bg-opacity-75">
		<div class='mb-3'>
			<a class='btn btn-outline-secondary' href='jouer.php'>Retour au jeu</a>
		</div>
		<h2 class='mb-4 pt-2'>Banque de compagnie</h2>
	</div>
</div>
<?php
$header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<?php if(isset($_SESSION['flash'])&& !empty($_SESSION['flash'])): ?>
<div class="row">
	<div class='col'>
		<div class='p-4 alert alert-<?= $_SESSION['flash']['class'] ?>' role="alert">
			<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span class='align-middle fw-semibold'><?= $_SESSION['flash']['message'] ?></span>
		</div>
	</div>
</div>
<?php endif ?>
<div class="row">
	<div class="col">
		<div class="card shadow">
			<div class='card-header'>
				<h5 class="card-title">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
					</svg>
					Erreur 404
				</h5>
			</div>
			<div class="card-body p-4">
				<h4>
					Vous êtes perdu soldat ?
				</h4>
				<p>
					Retournez dans les rangs. Il n'y a rien à voir ici.
				</p>
				<nav class='nav'>
					<a class="btn btn-primary me-3" href="compagnie.php">Ma compagnie</a>
					<a class="btn btn-primary me-3" href="bank.php?id=<?=$perso->bank_id?>&action=show">Mes thunes</a>
					<a class="btn btn-secondary" href="jouer.php">Retour au jeu</a>
				</nav>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>