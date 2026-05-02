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
				<nav class='nav mb-3'>
					<a class='btn btn-outline-secondary' href='?action=character'>Retour aux persos</a>
					<a class='btn btn-outline-secondary ms-4' href='/'>Retour au jeu</a>
				</nav>
			</div>
		</div>
		<div class='row mb-2'>
			<div class='col'>
				<h2 class='mb-3'>Mon compte</h2>
				<nav>
					<div class="nav nav-tabs" id="nav-tab" role="tablist">
						<button class="nav-link <?php if(!isset($_SESSION['flash']['tab'])):?> active<?php endif?>" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="true">Profil</button>
						<button class="nav-link<?php if(isset($_SESSION['flash']['tab']) AND $_SESSION['flash']['tab']=='security'):?> active<?php endif?>" id="nav-security-tab" data-bs-toggle="tab" data-bs-target="#nav-security" type="button" role="tab" aria-controls="nav-security" aria-selected="false">Sécurité</button>
					</div>
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
<div class="tab-content" id="nav-compContent">
	<div class="tab-pane fade <?php if(!isset($_SESSION['flash']['tab'])):?> show active<?php endif?>" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
		<div class="row">
			<div class="col-12 col-sm-6 col-lg-3 mb-3">
				<div class="card">
					<div class="card-header">
						<h3 class='fs-4'>Profil</h3>
					</div>
					<div class="card-body text-center">
						<div class='avatar mx-auto mb-3 mt-3 rounded-circle bg-secondary-subtle'>
							<img class="avatar-img img-fluid rounded-circle" <?php if($profile->avatar):?>src="../public/img/users/<?=$profile->avatar?>"<?php endif;?> alt="<?= substr($profile->nom_perso,0,1);?>">
						</div>
						<div class="fw-semibold mb-1 fs-4"><?= $profile->nom_perso ?></div>
						<div class="mb-4 fs-5">camp <?= $profile->camp ?></div>
						<div class='mb-3 text-start'>
							<ul class="list-group list-group-flush">
								<li class="list-group-item py-3">Email : <?= $profile->email_joueur ?></li>
								<li class="list-group-item py-3">Pays : <?= $profile->pays_joueur ?></li>
								<li class="list-group-item py-3">Région : <?= $profile->region_joueur ?></li>
							</ul>
						</div>
						<a class="btn btn-primary" href="?action=user&op=edit&id=<?= $profile->id_joueur?>">Editer</a>
					</div>
				</div>
			</div>
			<div class='col'>
				<div class="card mb-3">
					<div class="card-header">
						<h3 class='fs-4'>Notifications par mail</h3>
					</div>
					<div class="card-body">
						<form action="?action=user&op=edit&id=<?= $profile->id_joueur?>" method="post" name="notificationsForm">
							<fieldset class="row mb-4">
								<div class="col-sm-10">
									<div class="form-check mb-3">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['mail_pm'])){?> is-invalid<?php };?>" type="checkbox" name="mail_pm" id="mail_pm" <?php if($profile->mail_mp==1){echo "checked";}?>>
										<label class="form-check-label" for="mail_pm">
											Notification lors de la réception d'un message privé
										</label>
										<?php if(isset($_SESSION['errors']['mail_pm'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['mail_pm'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
									<div class="form-check mb-3">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['mail_attack'])){?> is-invalid<?php };?>" type="checkbox" name="mail_attack" id="mail_attack" <?php if($profile->mail_info==1){echo "checked";}?>>
										<label class="form-check-label" for="mail_attack">
											Notification en cas d'attaque sur l'un de vos persos
										</label>
										<?php if(isset($_SESSION['errors']['mail_attack'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['mail_attack'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
								</div>
							</fieldset>
							<input id="form" name="form" type="hidden" value="notificationsForm" />
							<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
							<button class="btn btn-primary" type="submit">Sauvegarder</button>
						</form>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class='fs-4'>Options de jeu</h3>
					</div>
					<div class='card-body'>
						<form action="?action=user&op=edit&id=<?= $profile->id_joueur?>" method="post" name="gameOptsForm">
							<fieldset class="row mb-4">
								<div class="col-sm-10">
									<div class="form-check mb-3">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['rosace'])){?> is-invalid<?php };?>" type="checkbox" name="rosace" id="rosace" <?php if($profile->afficher_rosace==1){echo "checked";}?>>
										<label class="form-check-label" for="rosace">
											Afficher la rosace de déplacement
										</label>
										<?php if(isset($_SESSION['errors']['rosace'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['rosace'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
									<div class="form-check mb-3">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['validate_move'])){?> is-invalid<?php };?>" type="checkbox" name="validate_move" id="validate_move" <?php if($profile->valid_case==1){echo "checked";}?>>
										<label class="form-check-label" for="validate_move">
											Validation avant un déplacement de perso
										</label>
										<?php if(isset($_SESSION['errors']['validate_move'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['validate_move'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
									<div class="form-check mb-3">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['allow_push'])){?> is-invalid<?php };?>" type="checkbox" name="allow_push" id="allow_push" <?php if($profile->bousculade_deplacement==1){echo "checked";}?>>
										<label class="form-check-label" for="allow_push">
											Autoriser les bousclades automatiques lors d'un déplacement
										</label>
										<?php if(isset($_SESSION['errors']['allow_push'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['allow_push'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
									<?php endif;?>
									</div>
									<label class="form-check-label mb-2" for="img_versions">
										Version des images perso à utiliser
									</label>
									<select name="img_versions" id="img_versions" class="form-select<?php if(isset($_SESSION['errors']['img_versions'])){?> is-invalid<?php };?>" aria-label="Version des images perso à utiliser">
										<option selected>Choisir une option</option>
										<option value="v1" <?php if($profile->dossier_img=="v1"){echo "selected";}?>>V1</option>
										<option value="v2" <?php if($profile->dossier_img=="v2"){echo "selected";}?>>V2</option>
									</select>
									<?php if(isset($_SESSION['errors']['img_versions'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['img_versions'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
									<?php endif;?>
								</div>
							</fieldset>
							<input id="form" name="form" type="hidden" value="gameOptsForm" />
							<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
							<button class="btn btn-primary" type="submit">Sauvegarder</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tab-pane fade<?php if(isset($_SESSION['flash']['tab']) AND $_SESSION['flash']['tab']=='security'):?> show active<?php endif?>" id="nav-security" role="tabpanel" aria-labelledby="nav-security-tab" tabindex="0">
		<div class="row">
			<div class="col mb-3">
				<div class="card mb-3">
					<div class="card-header">
						<h3 class='fs-4'>Changer son mot de passe</h3>
					</div>
					<div class="card-body">
						<form action="?action=password&op=new&id=<?= $profile->id_joueur?>" method="post" name="newPasswordForm">
							<fieldset class="row mb-4">
								<div class="col-sm-10">
									<div class="mb-3">
										<label class="form-label" for="actual_pwd">
											Mot de passe actuel
										</label>
										<input type="password" class="form-control <?php if(isset($_SESSION['errors']['actual_pwd'])){?> is-invalid<?php };?>" id="actual_pwd" name='actual_pwd'>
										<?php if(isset($_SESSION['errors']['actual_pwd'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['actual_pwd'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
									<div class="mb-3">
										<label class="form-label" for="new_pwd">
											Nouveau mot de passe
										</label>
										<input type="password" class="form-control <?php if(isset($_SESSION['errors']['new_pwd'])){?> is-invalid<?php };?>" id="new_pwd" name='new_pwd' placeholder="****" >
										<?php if(isset($_SESSION['errors']['new_pwd'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['new_pwd'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
										<small class='text-muted'>minimum 8 caractères, 1 Majuscule, 1 minuscule, 1 chiffre, 1 des caractères spéciaux suivants <span class='fst-italic'>&@!?*-+_#%</span></small>
									</div>
									<div class="mb-3">
										<label class="form-label" for="confirm_pwd">
											Confirmer le mot de passe
										</label>
										<input type="password" class="form-control <?php if(isset($_SESSION['errors']['confirm_pwd'])){?> is-invalid<?php };?>" id="confirm_pwd" name='confirm_pwd' placeholder="****" >
										<?php if(isset($_SESSION['errors']['confirm_pwd'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['confirm_pwd'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
								</div>
							</fieldset>
							<input id="form" name="form" type="hidden" value="newPasswordForm" />
							<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
							<input id="from" name="from" type="hidden" value="user" />
							<button class="btn btn-primary" type="submit">Sauvegarder</button>
						</form>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class='fs-4'>Déclarer un multi-compte</h3>
						<p>
							Le multi-compte est le fait pour plusieurs personnes de jouer sur la même connexion internet et/ou le même équipement.
							Pour éviter tout avantage, fraude ou triche, l'utilisation du multi-compte est strictement encadré.<br>
						</p>
						<p>
							Si vous êtes dans cette situation, vous devez le déclarer sur cette page.<br>
							Dans le cas contraire, les comptes impliqués seront sanctionnés, allant jusqu'à la suppression d'un ou des comptes concernés.
						</p>
					</div>
					<div class="card-body">
						<?php if($multiAccounts): ?>
						<h4 class='fs-5'>Vos multi-comptes déclarés</h4>
						<table class="table table-striped table-sm">
							<thead>
								<tr>
									<th scope="col">Joueur concerné</th>
									<th scope="col">Justification</th>
									<th scope="col">Date effective</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($multiAccounts as $array => $account):?>
								<tr>
									<td>
										[<?= $account['target_id']?>] <?= $account['target_name']?>
									</td>
									<td>
										<?= $account['situation']?>
									</td>
									<td>
										<?= $account['date_declaration']?>
									</td>
								</tr>
								<?php endforeach;?>
							</tbody>
						</table>
						<hr class='my-4 mx-5'>
						<?php endif;?>
						<form action="?action=user&op=edit&id=<?= $profile->id_joueur?>" method="post" name="multiAccountForm" id='multiAccountForm'>
							<fieldset class="row mb-4">
								<div class="col-sm-10">
									<div class="mb-3">
										<label class="form-label" for="target_id">
											Matricule du joueur avec lequel vous déclarez un multi-compte
										</label>
										<div class="input-group mb-3 search-area">
											<input class="form-control<?php if(isset($_SESSION['errors']['target_id'])){?> is-invalid<?php };?>" type="number" min="1" name="target_id" id="target_id" list='ac_suggestions' data-auto-completion='character' autocomplete="off" aria-label="matricule du joueur" aria-describedby="search-btn search-invalid-feedback">
											<button class="btn btn-secondary" type="button" id="search-btn">Rechercher</button>
											<?php if(isset($_SESSION['errors']['target_id'])):?>
											<div id='search-invalid-feedback' class="invalid-feedback">
												<?php foreach($_SESSION['errors']['target_id'] as $error): ?>
													<?= $error ?>
												<?php endforeach; ?>
											</div>
											<?php endif;?>
										</div>
										<datalist id='ac_suggestions'></datalist>
									</div>
									<div class="mb-3">
										<label class="form-label" for="explanation">
											Merci de nous expliquer en quelques mots votre demande
										</label>
										<textarea class="form-control<?php if(isset($_SESSION['errors']['explanation'])){?> is-invalid<?php };?>" name="explanation" id="explanation" rows="3"></textarea>
										<?php if(isset($_SESSION['errors']['explanation'])):?>
										<div class="invalid-feedback">
											<?php foreach($_SESSION['errors']['explanation'] as $error): ?>
												<?= $error ?>
											<?php endforeach; ?>
										</div>
										<?php endif;?>
									</div>
								</div>
							</fieldset>
							<input id="form" name="form" type="hidden" value="multiAccountForm" />
							<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
							<button class="btn btn-primary" type="submit">Déclarer</button>
						</form>
					</div>
				</div>
			</div>
			<div class="col col-sm-4">
				<div class="card mb-3">
					<div class="card-header">
						<h3 class='fs-4'>Partir en permission</h3>
					</div>
					<div class="card-body">
						<?php if($profile->permission):?>
							<?php if(empty($profile->demande_perm)):?>
							<p>
								Vous êtes en permission.
							</p>
							<?php elseif($profile->demande_perm==-1): ?>
							<p class='alert alert-dark'>
								Votre dernière permission date du <?= $permissionDate->format("d-m-Y à H:i:s"); ?>
							<p>
							<p>
								La mise en permission vous permet de protéger vos persos lorsque vous êtes absent du jeu pendant une longue période.
							</p>
							<p>
								Elle dure au minimum 7 jours et vous serez rapatrié dans le bâtiment le plus proche en revenant (fort, fortin ou hôpital).
							</p>
							<p class='alert alert-info'>
								Attention, la permission s'active au bout de 2 jours et ne peut pas être annulée après ce délai et avant 7 jours.<br>
								Au delà de 15 jours de permission, vous êtes en permission longue. A votre prochaine connexion vous serez alors rapatrié au fort le plus proche.
							</p>
							<button class='btn btn-secondary' type='button' data-bs-toggle="modal" data-bs-target="#permissionModal">Partir en permission</button>
							<?php else: ?>
							<p>
								Vous avez fait une demande de permission.<br>
								Celle-ci sera effective le <span class='fw-semibold'><?= $lastDays?></span>.
							</p>
							<p class='alert alert-dark'>
								Il vous reste <span class='fw-semibold'><?= $remainingTime->format('%a jour(s) et %H:%I:%S')?></span> pour annuler.
							</p>
							<button class='btn btn-secondary' type='button' data-bs-toggle="modal" data-bs-target="#permissionModal">Annuler la permission</button>
							<?php endif;?>
						<?php else:?>
						<p>
							La mise en permission vous permet de protéger vos persos lorsque vous êtes absent du jeu pendant une longue période.
						</p>
						<p>
							Elle dure au minimum 7 jours et au maximum 15 jours.
						</p>
						<p class='alert alert-info'>
							Attention, la permission s'active au bout de 2 jours et ne peut pas être annulée après ce délai et avant 7 jours.<br>
							Au delà de 15 jours de permission, vous êtes en permission longue. A votre prochaine connexion vous serez alors rapatrié au fort le plus proche.
						</p>
						<button class='btn btn-secondary' type='button' data-bs-toggle="modal" data-bs-target="#permissionModal">Partir en permission</button>
						<?php endif;?>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class='fs-4'>Suppression de compte</h3>
					</div>
					<div class="card-body">
						<p>
							Supprimer son compte est une action définitive. Vos persos, vos statistiques et votre évolution seront perdus.
						</p>
						<p>
							Vous devrez recréer un compte pour jouer de nouveau.
						</p>
						<button class='btn btn-danger' type='button' data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Supprimer son compte</button>
					</div>
				</div>
			</div>
			<!-- Modal permission -->
			<div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h1 class="modal-title fs-5" id="permissionModalLabel"><?php if($profile->permission AND $profile->demande_perm==1):?>Annuler la demande de permission<?php else:?>Partir en permission<?php endif;?></h1>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<?php if($profile->permission AND $profile->demande_perm==1):?>
							<p>
								Voulez vous vraiment annuler votre permission ?<br>
								Il vous reste <span class='fw-semibold'><?= $remainingTime->format('%a jour(s) et %H:%I:%S')?></span> pour annuler.
							</p>
							<?php else:?>
							<p>
							Vous êtes sur le point de partir en permission.
							</p>
							<p>
								Vous pouvez annuler cette demande jusqu'au <?= $nowPlus2Days?>.<br>
								Passé ce délai, vous ne pourrez vous reconnecter qu'à partir du <?= $nowPlus8Days?>.
							</p>
							<?php endif;?>
						</div>
						<div class="modal-footer">
							<?php if($profile->permission AND $profile->demande_perm==1):?>
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
							<form action="?action=user&op=edit&id=<?= $profile->id_joueur?>" method="post" name="permissionForm">
								<input id="form" name="form" type="hidden" value="permissionForm" />
								<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
								<button type="submit" name='cancelPermBtn' class="btn btn-primary" value='yes'>Annuler la permission</button>
							</form>
							<?php else:?>
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">annuler</button>
							<form action="?action=user&op=edit&id=<?= $profile->id_joueur?>" method="post" name="permissionForm">
								<input id="form" name="form" type="hidden" value="permissionForm" />
								<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
								<button type="submit" name='validPermBtn' class="btn btn-primary" value='yes'>Partir en permission</button>
							</form>
							<?php endif;?>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal suppression compte -->
			<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-danger-subtle">
							<h1 class="modal-title fs-5" id="deleteAccountModalLabel">Supprimer son compte</h1>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<p>
							Vous êtes sur le point de supprimer votre compte.
							</p>
							<p>
								Cette action est définitive. Vos persos, vos statistiques et votre évolution seront perdus.
							</p>
							<p>
								Vous devrez recréer un compte pour jouer de nouveau. 
							</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">annuler</button>
							<form action="?action=user&op=edit&id=<?= $profile->id_joueur?>" method="post" name="deleteAccountForm">
								<input id="form" name="form" type="hidden" value="deleteAccountForm" />
								<input id="profile" name="profile" type="hidden" value="<?= $profile->id_joueur?>" />
								<button type="submit" class="btn btn-danger">Supprimer</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>