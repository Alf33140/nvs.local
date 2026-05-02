<?php
$title = "Permission";

/* ---Header--- */
ob_start();
?>
<div class='background-img homepage-bg'>
</div>
<div class="row justify-content-center">
	<div class="col rounded bg-light mx-2 p-4 bg-opacity-75">
		<h2>Bon retour soldat !</h2>
		<p>
			Vous êtes en permission <?php if($totalDays->format('%a')>15): ?>longue <?php endif;?>depuis <span class="fw-semibold"><?= $totalDays->format('%a jour(s) %H heure(s) et %I minute(s)') ?></span>
		</p>
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
<?php if($totalDays->format('%a')>=5): ?>
<div class="row">
	<div class='col'>
		<div class='p-4 alert alert-info' role="alert">
			<h3 class='fs-4'>
				<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
				  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
				</svg>
				<span class='align-middle fw-semibold'>Bonne nouvelle !</span>
			</h3>
			<p>
				Vous pouvez sortir de permission. Vous serez rapatrié dans <span class="fw-semibold">le <?php if($totalDays->format('%a')>15): ?>fort<?php else:?>bâtiment (fort, fortin, hôpital)<?php endif;?></span> le plus proche de votre dernière position.
			</p>
			<p>
				Si vous ne souhaitez pas revenir maintenant, aucun problème. Il vous suffit de vous déconnecter et de revenir plus tard.<br>
				Attention, les comptes inactifs depuis plus d'un an seront supprimés automatiquement.
			</p>
			<nav class="nav mt-4">
				<form action='index.php?action=user&op=edit&id=<?= $user->id_joueur?>' method='post' name="returnInGameForm">
					<button class='btn btn-primary me-3' type="submit" name="returnInGameBtn" value='yes'>Sortir de permission ?</button>
					<input id="form" name="form" type="hidden" value="returnInGameForm" />
					<input id="profile" name="profile" type="hidden" value="<?= $user->id_joueur?>" />
				</form>
				<a class='btn btn-outline-secondary' href='index.php?action=logout'>Se déconnecter</a>
			</nav>
		</div>
	</div>
</div>
<?php else: ?>
<div class="row">
	<div class='col'>
		<div class='p-4 alert alert-secondary' role="alert">
			<h3 class='fs-4'>
				<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
				  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
				</svg>
				<span class='align-middle fw-semibold'>Un peu de patience...</span>
			</h3>
			<p class="mb-5">
				Les combats féroces et le front instable mobilisent le commandement. Votre retour de permission n'a pas encore été validé.<br>
				Vous pourrez revenir de permission dans <span class="fw-semibold"><?= 4-$totalDays->format('%a').' jour(s) '. 23-$totalDays->format('%H').' heure(s) et '. 60-$totalDays->format('%I').' minute(s)'?></span>.
			</p>
			<nav>
				<a class='btn btn-secondary me-3' href='index.php?action=logout'>Se déconnecter</a>
				<a class='btn btn-outline-secondary' href='https://discord.gg/EMqRMzHKjZ'>Un soucis ? Contactez-nous</a>
			</nav>
		</div>
	</div>
</div>
<?php endif; ?>


<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>