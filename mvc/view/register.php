<?php
$title = "Inscription";

/* ---Content--- */
ob_start();
?>
<div class='row justify-content-center bg-light bg-opacity-75 pt-4'>
<?php if(isset($error)):?>
	<div class='col-6 alert alert-danger'>
		<?= $error ?>
	</div>
<?php endif; ?>
</div>
<div class='row justify-content-center bg-light bg-opacity-75 pt-4'>
	<?php if(isset($_SESSION['flash'])&& !empty($_SESSION['flash'])): ?>
	<div class='col-6 mt-4'>
		<div class='alert alert-<?= $_SESSION['flash']['class'] ?>'>
			<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span class='align-middle'><?= $_SESSION['flash']['message'] ?></span>
		</div>
	</div>
	<?php endif ?>
	<div class='col-11 col-lg-8 p-4'>
		<form method="post" action="?action=register" class='row justify-content-center'>
			<div class='col-12 col-md-6'>
				<h1 class=''>INSCRIPTION</h1>
				<div class="mb-3">
					<label for="email_joueur" class='form-label'>Adresse email:</label>
					<input type="email" class="form-control form-control <?php if(isset($_SESSION['errors']['email_joueur'])){?> is-invalid<?php };?>" id="email_joueur" name='email_joueur' value="<?php if(isset($_SESSION['old_input']['email_joueur'])){echo $_SESSION['old_input']['email_joueur'];}?>" placeholder="exemple@dns.fr" >
					<?php if(isset($_SESSION['errors']['email_joueur'])):?>
					<div class="invalid-feedback">
						<?php foreach($_SESSION['errors']['email_joueur'] as $error): ?>
							<?= $error ?>
						<?php endforeach; ?>
					</div>
					<?php endif;?>
				</div>
				<div class="mb-3">
					<label for="mdp_joueur" class='form-label'>Mot de passe:</label>
					<input type="password" class="form-control<?php if(isset($_SESSION['errors']['mdp_joueur'])){?> is-invalid<?php };?>" id="mdp_joueur" name='mdp_joueur' placeholder="****" >
					<?php if(isset($_SESSION['errors']['mdp_joueur'])):?>
					<div class="invalid-feedback">
						<?php foreach($_SESSION['errors']['mdp_joueur'] as $error): ?>
							<?= $error ?>
						<?php endforeach; ?>
					</div>
					<?php endif;?>
					<small class='text-muted'>minimum 8 caractères, 1 Majuscule, 1 minuscule, 1 chiffre, 1 des caractères suivants <span class='fst-italic'>!?*-+_#%</span></small>
				</div>
				<div class="mb-3">
					<label for="nom_perso" class='form-label'>Nom de votre personnage:</label>
					<input type="text" class="form-control<?php if(isset($_SESSION['errors']['nom_perso'])){?> is-invalid<?php };?>" id="nom_perso" name='nom_perso' value="<?php if(isset($_SESSION['old_input']['nom_perso'])){echo $_SESSION['old_input']['nom_perso'];}?>" placeholder="Alfred le borgne" >
					<?php if(isset($_SESSION['errors']['nom_perso'])):?>
					<div class="invalid-feedback">
						<?php foreach($_SESSION['errors']['nom_perso'] as $error): ?>
							<?= $error ?>
						<?php endforeach; ?>
					</div>
					<?php endif;?>
					<small class='text-muted'>2 caractères minimum, 25 caractères maximum. accents français autorisés</small>
				</div>
				<div class="mb-3">
					<label for="nom_bataillon" class='form-label'>Nom de votre bataillon:</label>
					<input type="text" class="form-control<?php if(isset($_SESSION['errors']['nom_bataillon'])){?> is-invalid<?php };?>" id="nom_bataillon" name='nom_bataillon' value="<?php if(isset($_SESSION['old_input']['nom_bataillon'])){echo $_SESSION['old_input']['nom_bataillon'];}?>" placeholder="Les souris en claquette" >
					<?php if(isset($_SESSION['errors']['nom_bataillon'])):?>
					<div class="invalid-feedback">
						<?php foreach($_SESSION['errors']['nom_bataillon'] as $error): ?>
							<?= $error ?>
						<?php endforeach; ?>
					</div>
					<?php endif;?>
					<small class='text-muted'>2 caractères minimum, 35 caractères maximum. accents français autorisés</small>
				</div>				
				<div class="mb-3">
					<label for="camp_perso" class='form-label<?php if(isset($_SESSION['errors']['camp_perso'])){?> is-invalid<?php };?>'>Choisissez votre camp:</label>
					<?php if(isset($_SESSION['errors']['camp_perso'])):?>
					<div class="invalid-feedback">
						<?php foreach($_SESSION['errors']['camp_perso'] as $error): ?>
							<?= $error ?>
						<?php endforeach; ?>
					</div>
					<?php endif;?>
					<div class='row row row-cols-2'>
						<div class='col text-center card-selection'>
							<input class="btn-check" type="radio" id="northCamp" name="camp_perso" value="1"<?php if(isset($_SESSION['old_input']['camp_perso'])AND $_SESSION['old_input']['camp_perso']=='1'){echo ' checked';}?><?php if(isset($desactivatedCamp) AND $desactivatedCamp==1){ echo ' disabled';}?>>
							<label for="northCamp">
								<div class="card card-north">
									<?php if(isset($desactivatedCamp) AND $desactivatedCamp==1): ?>
									<span class='position-absolute top-50 fw-semibold'>
										Désactivé pour équilibrage
									</span>
									<?php endif; ?>
									<div class='bg-north'>
										<img src="../public/img/characters/nordiste.png" class="card-img-top pt-2" alt="personnage sudiste">
									</div>
									<div class='card-footer'>
										<h5 class="card-title text-center text-north fw-semibold">Nord</h5>
									</div>
								</div>
							</label>
						</div>
						<div class='col text-center card-selection'>
							<input class="btn-check" type="radio" id="southCamp" name="camp_perso" value="2"<?php if(isset($_SESSION['old_input']['camp_perso'])AND $_SESSION['old_input']['camp_perso']=='2'){echo ' checked';}?><?php if(isset($desactivatedCamp) AND $desactivatedCamp==2){ echo ' disabled';}?>>
							<label for="southCamp">
								<div class="card card-south">
									<?php if(isset($desactivatedCamp) AND $desactivatedCamp==2): ?>
									<span class='position-absolute top-50 fw-semibold'>
										Désactivé pour équilibrage
									</span>
									<?php endif; ?>
									<div class='bg-south'>
										<img src="../public/img/characters/sudiste.png" class="card-img-top pt-2" alt="personnage sudiste">
									</div>
									
									<div class='card-footer'>
										<h5 class="card-title text-center text-south fw-semibold">Sud</h5>
									</div>
								</div>
							</label>
						</div>
					</div>
					<div class='text-center mt-1'>
						<span class='text-primary fw-bold'>Joueurs actifs au Nord : <?= $northActivePlayers ?></span> / <span class='text-danger fw-bold'>Joueurs actifs au Sud : <?= $southActivePlayers?> </span>
					</div>
				</div>
			</div>
			<div class='col-12'>
				<h3>Charte des joueurs</h3>
				<div class='bg-light rounded shadow p-3 mb-3'>
					<p>
						« Nord vs Sud » est un jeu.<br/>
						je m'engage à :
					</p>
					<ul>
						<li>Faire preuve de fair-play,</li>
						<li>Equilibrer les équipes si nécessaire,</li>
						<li>Ne pas tricher,</li>
						<li>Signaler les bugs et ne pas en tirer avantage,</li>
						<li>Respecter la bonne ambiance du jeu et être bienveillant envers tous les joueurs, qu'ils soient très impliqués ou non,</li>
						<li>Éviter toute attitude ou comportement toxique ou pouvant nuire au bon déroulement du jeu et du bien-être de chacun,</li>
						<li>respecter les règles et lieux Role Play (RP) et Hors Role Play (HRP)</li>
					</ul>
					<p>
					Tout multicompte ou usage de VPN légitime est à déclarer publiquement.<br/>
					L'équipe d'animation sera prompte à sanctionner tout contrevenant.
					</p>
				</div>
				
				<div class="form-check mb-3 fw-semibold">
					<input class="form-check-input<?php if(isset($_SESSION['errors']['charte'])){?> is-invalid<?php };?>" type="checkbox" id="charte" name="charte" >
					<label class="form-check-label" for="charte">
						En cochant cette case j'accepte sans réserve la charte des joueurs.
					</label>
					<?php if(isset($_SESSION['errors']['charte'])):?>
					<div class="invalid-feedback">
						<?php foreach($_SESSION['errors']['charte'] as $error): ?>
							<?= $error ?>
						<?php endforeach; ?>
					</div>
					<?php endif;?>
				</div>
				<div class="form-check mb-3 fw-semibold">
					<input class="form-check-input<?php if(isset($_SESSION['errors']['cgu'])){?> is-invalid<?php };?>" type="checkbox" id="cgu" name="cgu" >
					<label class="form-check-label" for="cgu">
						En cochant cette case je confirme avoir lu les <a href='../CGU.pdf' target='_blank'>Conditions générales d'utilisation</a>
					</label>
					<?php if(isset($_SESSION['errors']['cgu'])):?>
					<div class="invalid-feedback">
						<?php foreach($_SESSION['errors']['cgu'] as $error): ?>
							<?= $error ?>
						<?php endforeach; ?>
					</div>
					<?php endif;?>
				</div>
			</div>
			<div class='col-12 text-center'>
				<p>
					En vous inscrivant, vous acceptez l'utilisation de vos données<br>conformément à <a href="../CUDP.pdf">la charte d'utilisation des données personnelles</a>
				</p>
				<p class=''>
					<input class='btn btn-success w-50' type="submit" value="S'incrire">
				</p>
			</div>
		</form>
	</div>	
</div>
	
<?php $content = ob_get_clean(); ?>

<?php require('layouts/guest.php'); ?>
