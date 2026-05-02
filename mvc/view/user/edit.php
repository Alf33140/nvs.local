<?php
$title = "Compte";

/* ---Header--- */
ob_start();
?>
<div class='background-img homepage-bg'>
</div>
<div class="row justify-content-center">
	<div class="col mx-2 rounded bg-light py-3 bg-opacity-75">
		<div class='row'>
			<div class='col-12'>
				<nav class='mb-3'>
					<a class='btn btn-outline-secondary' href='jouer.php'>Retour au jeu</a>
				</nav>
			</div>
		</div>
		<div class='row mb-2'>
			<div class='col'>
				<h2 class='mb-3'>Editer mon profil</h2>
				<nav>
					<a class='btn btn-secondary' href="?action=user&op=show&id=<?= $profile->id_joueur?>">Revenir au profil</a>
				</nav>
			</div>
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
	<div class="col mb-3">
		<div class="card">
			<div class="card-header">
				<h3 class='fs-4'>Profil</h3>
			</div>
			<div class="card-body">
				<div class='row'>
					<div class='col-3 text-center'>
						<div class='avatar mx-auto mb-3 mt-3 rounded-circle border border-2 border-secondary bg-secondary-subtle'>
							<img id="imgPreview150" class="avatar-img rounded-circle w-100 h-100" <?php if($profile->avatar):?>src="../public/img/users/<?=$profile->avatar?>"<?php endif;?> alt="<?= substr($profile->nom_perso,0,1);?>">  
						</div>
						<form action='?action=user&op=edit&id=<?= $profile->id_joueur?>' method='post' name="avatarForm" enctype="multipart/form-data">
							<div class='row justify-content-center'>
								<small class='text-muted'>PNG ou JPG, taille 150x150, 2Mo maximum</small>
								<label id="imgPreviewName" for="imgUpload" class="mt-2 btn btn-light col-8">Changer l'image de profil</label>
								<input class="visually-hidden" type="file" name="imgUpload" id="imgUpload" data-img-src="only">
							</div>
							<?php if(isset($_SESSION['errors']['imgUpload'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['imgUpload'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
							<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
							<input id="form" name="form" type="hidden" value="avatarForm" />
							<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
							<button class="btn btn-primary mt-3" type="submit">Sauvegarder</button>
						</form>
					</div>
					<div class='col'>
						<form action='?action=user&op=edit&id=<?= $profile->id_joueur?>' method='post' name="detailForm">
							<fieldset class='mb-2'>
								<legend class='bg-secondary-subtle p-2'>Détails</legend>
								<div class="row">
									<label for="user_name" class="col-sm-2 col-form-label">
										Nom d'utilisateur*<br>
										<small class='text-muted'>et de votre chef</small>
									</label>
									<div class="col-sm-7">
										<input type="text" class="form-control-plaintext fw-semibold fs-5" id="user_name" value='<?= $profile->nom_perso ?>' readonly>
									</div>
									<!--
									<div class="col-sm-3">
										<button class='btn btn-secondary btn-sm mt-1 w-full' type='button' disabled>Changer de nom</button>
									</div>
									-->
								</div>
								<div class="row mb-3">
									<label for="user_camp" class="col-sm-2 col-form-label">
										Camp*
									</label>
									<div class="col-sm-7">
										<input type="text" class="form-control-plaintext" id="user_camp" value='<?= $profile->camp ?>' readonly>
									</div>
									<!--
									<div class="col-sm-3">
										<button class='btn btn-secondary btn-sm mt-1 w-full' type='button' disabled>Changer de camp</button>
									</div>
									-->
								</div>
								<div class="row mb-3">
									<label for="user_email" class="col-sm-2 col-form-label">E-mail*</label>
									<div class="col-sm-10">
										<input type="email" class="form-control<?php if(isset($_SESSION['errors']['user_email'])){?> is-invalid<?php };?>" name="user_email" id="user_email" value='<?= $profile->email_joueur ?>'>
										<?php if(isset($_SESSION['errors']['user_email'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['user_email'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
								</div>
							</fieldset>
							<fieldset>
								<legend class='bg-secondary-subtle p-2'>A propos de vous <small class='fs-6 text-muted'>(optionnel)</small></legend>
								<div class="row mb-3">
									<label for="user_age" class="col-sm-2 col-form-label">Âge</label>
									<div class="col-sm-10">
										<input type="number" class="form-control<?php if(isset($_SESSION['errors']['user_age'])){?> is-invalid<?php };?>" name="user_age" id="user_age" value='<?= $profile->age_joueur ?>'>
										<?php if(isset($_SESSION['errors']['user_age'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['user_age'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
								</div>
								<div class="row mb-3">
									<label for="user_desc" class="col-sm-2 col-form-label">
										Description<br>
										<small class='text-muted'>Quelques mots sur votre profil joueur</small>
									</label>
									<div class="col-sm-10">
										<textarea class="form-control<?php if(isset($_SESSION['errors']['user_desc'])){?> is-invalid<?php };?>" name="user_desc" id="user_desc" rows="3"><?= $profile->description_joueur ?></textarea>
										<?php if(isset($_SESSION['errors']['user_desc'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['user_desc'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
								</div>
								<div class="row mb-3">
									<label for="user_country" class="col-sm-2 col-form-label">Pays</label>
									<div class="col-sm-10">
										<input type="text" class="form-control<?php if(isset($_SESSION['errors']['user_country'])){?> is-invalid<?php };?>" name="user_country" id="user_country" value='<?= $profile->pays_joueur ?>'>
										<?php if(isset($_SESSION['errors']['user_country'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['user_country'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
								</div>
								<div class="row mb-3">
									<label for="user_district" class="col-sm-2 col-form-label">Région</label>
									<div class="col-sm-10">
										<input type="text" class="form-control<?php if(isset($_SESSION['errors']['user_district'])){?> is-invalid<?php };?>" name="user_district" id="user_district" value='<?= $profile->region_joueur ?>'>
										<?php if(isset($_SESSION['errors']['user_district'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['user_district'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
								</div>
							</fieldset>
							<input id="form" name="form" type="hidden" value="detailForm" />
							<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
							<button class="btn btn-primary" type="submit">Sauvegarder</button>
						</form>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>