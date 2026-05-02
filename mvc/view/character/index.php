<?php
$title = "Gestion des persos";

/* ---Header--- */
ob_start();
?>
<div class='background-img homepage-bg'>
</div>
<div class="row justify-content-center">
	<div class="col mx-2 rounded bg-light py-3 bg-opacity-75">
		<nav class='mb-3'>
			<a class='btn btn-outline-secondary' href='/'>Retour au jeu</a>
		</nav>
		<h2 class='mb-3'>Gestion de vos personnages</h2>
		<div class='bg-light p-4 rounded'>
			<nav class='mb-3'>
				<a class='btn btn-outline-primary nav-item' href='recrutement.php'>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
					  <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
					</svg>
					Recrutement
				</a>
			</nav>
		</div>
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
				<h4 class="card-title p-2">
					Vos personnages
				</h4>
				<p>
					Points de recrutement restants : <?= $remaining_pg?>/<?= $mainCharacPG->point_armee_grade ?> PR<br>
					Coût de réactivation : 2 Points d'Action (<?= $mainCharacPG->pa_perso ?> PA disponibles)
				</p>
			</div>
			<div class="card-body p-4">
				<?php if(isset($characters) AND !empty($characters)):?>
				<div class="row row-cols-1 row-cols-sm-2 row-cols-md-6 g-4">
					<?php foreach($characters as $character): 
						if($character->camp_name=="Nord"){$camp="north";};
						if($character->camp_name=="Sud"){$camp="south";};
					?>
					<div class="col">
						<div class="card card-camp card-camp-<?=$camp?> h-100 bg-light">
							<?php if($character->chef==0 AND $character->est_renvoye==1):?>
							<div class='card-ribbon card-ribbon-top-left'>
								<span class='bg-danger bg-opacity-75 text-light fw-semibold'>désactivé</span>
							</div>
							<?php endif;?>
							<a class="card-edit-btn p-2 text-end fw-semibold text-dark text-decoration-none h-75" href="?action=character&op=show&id=<?= $character->id_perso?>">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
								</svg>
								Editer
							</a>
							<div class='card-img-top bg-light bg-opacity-75'>
								<div class='camp-img d-flex mx-auto h-100 pt-3'>
									<img class='img-fluid h-100' src="../public/img/characters/<?= $character->image_unite ?>.png" alt='Image perso'>
								</div>
							</div>
							<div class="card-body bg-light bg-opacity-75 py-2">
								<h5 class="card-title fw-semibold">
									<?= $character->nom_perso?> [<?= $character->id_perso?>]<br>
									<?= $character->nom_unite?>
								</h5>
							</div>
							<?php 
								
								if($character->est_renvoye==1){
									$activation = "activate";
									$active_btn = "success";
									$activate_label = "Activer";
									$activate_sign = 'coût';
									$svg = "M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9";
								}else{
									$activation = "desactivate";
									$active_btn = "danger";
									$activate_label = "Désactiver";
									$activate_sign = 'gain';
									$svg = "M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636";
								}
								
								if($character->chef==0 AND ($character->est_renvoye==0 OR $remaining_pg>=$character->cout_pg)):
								?>
							<form class='card-footer text-center bg-<?=$active_btn?> btn btn-<?=$active_btn?> bg-opacity-75 card-btn-<?=$activation?> p-0' id="activateCharacter" name="activateCharacter" method="POST" action="?action=character&op=edit&id=<?= $character->id_perso?>">
								<input id="form" name="form" type="hidden" value="activateCharacter">
								<input id="character" name="character" type="hidden" value="<?= $character->id_perso?>">
								<button name='activationBtn'id='activationBtn_<?= $character->id_perso?>' type='submit' class='btn rounded-0 text-decoration-none text-light fw-semibold w-100 p-2' value='<?=$activation?>'>
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round" d="<?=$svg?>" />
									</svg>
									<?=$activate_label?><br><span class='fw-lighter'>(<?=$activate_sign?> : <?= $character->cout_pg?> PR)</span>
								</button>
							</form>
							<?php 
								elseif($character->chef==0 AND $remaining_pg<$character->cout_pg):
							?>
							<div class='card-footer text-center bg-secondary btn btn-secondary bg-opacity-75 p-0'>
								<button name='activationBtn'id='activationBtn_<?= $character->id_perso?>' type='button' class='btn rounded-0 text-decoration-none text-light fw-semibold w-100 p-2' disabled>
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
									</svg>
									Pas assez de PR<br><span class='fw-lighter'> (<?=$activate_sign?> : <?= $character->cout_pg?> PR)</span>
								</button>
							</div>
							<?php else:?>
							<div class='card-footer text-center bg-primary btn btn-primary bg-opacity-75 p-0'>
								<button class='btn rounded-0 text-decoration-none fw-semibold w-100 p-2' disabled>
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
										<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
									</svg>
									Chef<br>(personnage principal)
								</button>
							</div>
							<?php endif;?>
						</div>
					</div>
					<?php endforeach;?>
				</div>
				<?php else: ?>
				<p>
					Vous n'avez aucun perso.
				</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>