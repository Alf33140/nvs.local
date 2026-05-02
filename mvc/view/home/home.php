<?php
$title = "Accueil";

/* ---Content--- */
ob_start();
?>
<?php if($maintenance_mode['valeur_config']!=1): ?>
<div class="row justify-content-center mb-0">
	<div class='alert alert-warning fw-bold text-center col'>
		<svg xmlns="http://www.w3.org/2000/svg" class="me-2 mb-2 maintenance-icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
			<circle cx="12" cy="12" r="7.2490396"/>
			<path stroke-linecap="round" stroke-linejoin="round" d="M 11.076105,0.45483854 10.198151,2.9797418 7.4122271,4.322478 4.489785,3.182112 3.1821151,4.4897818 4.3482005,6.8963676 3.3356861,9.8157842 0.45484167,11.076102 v 1.847795 l 2.52490323,0.877955 1.3427363,2.785919 -1.1403661,2.922447 1.3076699,1.30767 2.4070084,-1.165729 2.9189942,1.012158 1.2603134,2.880845 h 1.849572 l 0.877042,-2.524829 2.785013,-1.341268 2.922487,1.138823 1.30767,-1.30767 -1.165729,-2.407009 1.012364,-2.919037 2.880638,-1.260272 V 11.076106 L 21.020266,10.198211 19.687882,7.4068314 20.817891,4.4897818 19.510221,3.182112 17.104916,4.3477781 14.188302,3.3175139 12.925676,0.45483854 Z" />
			<path stroke-linecap="round" stroke-linejoin="round" d="m 13.822851,8.942861 v 3.443154 L 12,13.246804 10.177149,12.386015 V 8.942861 c -3.6457021,0.860788 -3.6457021,6.02552 0,7.747097 v 1.721577 c 1.822851,0.86079 1.822851,0.86079 3.645702,0 v -1.721577 c 3.645702,-1.721577 3.645702,-6.886309 0,-7.747097 z" />
		</svg>
		<div class='w-75 m-auto pb-3 maintenance-msg'>
			<?= $maintenance_mode['msg'] ?>
		</div>
	</div>
</div>
<?php endif; ?>
<?php if($information_msg['valeur_config']==1): ?>
<div class="row justify-content-center mb-0">
	<div class='alert alert-info fw-bold col'>
		<div class='w-75 m-auto pb-2 text-center maintenance-msg'>
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon-lg">
				<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
			</svg> <?= $information_msg['msg'] ?>
		</div>
	</div>
</div>
<?php endif; ?>
<div class="row justify-content-center">
	<div class='col-11 col-sm-3 p-0'>
		<div class='justify-content-center bg-light bg-opacity-75 p-4'>
			<ul class="nav nav-tabs" id="myTab" role="tablist">
				<li id='connexion' class="nav-item" role="presentation">
					<button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-tab-pane" type="button" role="tab" aria-controls="login-tab-pane" aria-selected="true">
						<h4 class='fs-5'>
							Connexion
						</h4>
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-tab-pane" type="button" role="tab" aria-controls="register-tab-pane" aria-selected="false">
						<h4 class='fs-5'>
							Inscription
						</h4>
					</button>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade show active" id="login-tab-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
					<div class='pt-4'>
						<?php if(isset($_SESSION['flash']['slug'])&& $_SESSION['flash']['slug']=='new_turn'): ?>
						<div class="row">
							<div class='col'>
								<div class='fs-5 p-3 alert alert-<?= $_SESSION['flash']['class'] ?>' role="alert">
									<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
									</svg>
									<span class='align-middle fw-semibold'><?= $_SESSION['flash']['message'] ?></span>
								</div>
							</div>
						</div>
						<?php endif ?>
						<form class='' action="auth.php?action=login" method="post" name="login" id="login">
							<div class='row justify-content-center'>
								<div class="mb-4 col-10">
									<label for="pseudo" class='form-label fw-semibold'>Pseudo</label>
									<input type="text" class="form-control<?php if(isset($_SESSION['errors']['pseudo'])){?> is-invalid<?php };?>" id="pseudo" name='pseudo' placeholder="Pseudo" value="<?php if(isset($_SESSION['old_input']['pseudo'])){echo $_SESSION['old_input']['pseudo'];}?>">
								</div>
								<div class="mb-4 col-10">
									<label for="password" class='form-label fw-semibold'>Mot de Passe</label>
									<input type="password" class="form-control<?php if(isset($_SESSION['errors']['password'])){?> is-invalid<?php };?>" id="password" name='password' placeholder="Mot de Passe">
								</div>
								<div class="mb-4 col-10">
									<label for="captcha" class='form-label'>Etes-vous un robot ?</label>
									<div class=''>
										<div id='reload_captcha' class='mx-2'><img id='captcha_img' src="../captcha.php"/></div>
										<input id='captcha' name="captcha" type="text" class="form-control mt-2<?php if(isset($_SESSION['errors']['captcha'])){?> is-invalid<?php };?>" placeholder="Entrez le texte de l'image">
									</div>
								</div>
								<div class="mb-4 col-10">
									<input class='btn btn-primary' type="submit" name="Submit" value="Se connecter">
								</div>
								<div class='col-10'>
									<a href="../mdp_perdu.php" class=''>Mot de passe perdu ?</a>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="tab-pane fade" id="register-tab-pane" role="tabpanel" aria-labelledby="register-tab" tabindex="0">
				<?php if($maintenance_mode['valeur_config']!=1): ?>
					<p class='mt-4'>
						Le site étant en maintenance, les inscriptions sont suspendues.<br>
						Nous vous invitons à réessayer lorsque la maintenance sera terminée.
					</p>
				<?php else: ?>
					<h3 class='fs-5 mt-4'>
						Entrez dans l'histoire<br>
						<small>et prenez les rênes de la guerre de Sécession</small>
					</h3>
					<p class='mt-4'>
						Chaque décision compte, chaque bataille peut changer le destin d'une nation.
						Serez-vous le stratège qui fera la différence ?
					</p>
					<p>
						Venez relever le défi<br>
						<span class='fw-semibold'>Engagez-vous soldat !</span>
					</p>
					<p class='mt-4'>
						<a href="auth.php?action=register" class="btn btn-success w-100">S'inscrire</b></a>
					</p>
				<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<div class='col-11 col-sm-6 text-dark pt-3 pt-sm-0'>
		<div class='bg-light p-4 bg-opacity-75'>
			<h3 class='mb-4'>Bienvenue dans Nord versus Sud</h3>
			<h4 class='fw-semibold fs-6'>Amérique du Nord, printemps 1861</h4>
			<p>
				Depuis des années, les tensions montent entre l'armée de l'Union, commandée par <span class='text-north fw-bold'>Abraham Lincoln</span>, et l'armée des Etats confédérés, commandée par <span class='text-south fw-bold'>Jefferson Davis</span>.
			</p>
			<h4 class='fw-semibold fs-6'>Le 12 avril, La guerre est déclarée</h4>
			<p>
				L'armée confédérée lance les hostilités avec l'attaque du fort Sumter à Charleston (Caroline du Sud). Vous vous retrouvez malgré vous dans cette tourmente et devez choisir un camp.
			</p>
			<p class='fw-bold'>
				Alors, quel camp allez-vous faire gagner ?
			</p>
			
			<h3 class='fs-5 mt-4'>Un jeu de stratégie multi-joueurs sur navigateur</h3>
			<p>
				Chaque joueur commande un bataillon de quelques unités : <b>cavaliers, infanteries, soigneurs, artillerie, chiens militaires</b>
			</p>
			<p>
				Vous commencerez en tant que caporal et vous aurez sous vos ordres votre 1er grouillot.<br>
				Grâce à vos actions, vous monterez en grade et renforcerez votre bataillon en recrutant d'autres unités.
			</p>
			<p>
				Pour survivre, il faudra utiliser tous les moyens disponibles :<br>
				Spécificités des terrains, protection des bâtiments, achats d'armes et d'objets, accès à divers véhicules comme le train à vapeur...
			</p>
		</div>
	</div>
	<div class='col-11 col-sm-3 bg-light bg-opacity-75 p-4'>
		<div class='news'>
			<h4>
				<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 rotate-20deg align-bottom" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
				</svg>
				Les nouvelles du front
			</h4>
			<?php if(isset($news)):?>
				<ul>
				<?php foreach($news as $new): ?>
					<li>
						<?php
							$d = new DateTime($new->date);
							echo $d->format('d-m-Y');
						?>
						<br/>
						<?= $new->contenu ?><br/>
						-----
					</li>
				<?php endforeach ?>
				</ul>
			<?php else: ?>
			<p>
				Aucune nouvelle... espérons que nos soldats vont bien !
			</p>
			<?php endif; ?>		
			<p>
				<?= $usersNbr[0]->number; ?> joueur(s) inscrit(s)<br />
				Dernier inscrit :<br/> <span class='fw-bold <?php if($lastRegistered[0]->clan==1):?>text-north<?php elseif($lastRegistered[0]->clan==2):?>text-south<?php else:?>text-neutral<?php endif;?>'><?= $lastRegistered[0]->nom_perso?></span>
			</p>
			<p>
				Joueurs actifs : <br/><span class='text-north fw-bold'>nordistes : <?= $northActivePlayers; ?></span> / <span class='text-south fw-bold'>sudistes : <?= $southActivePlayers; ?></span>
			</p>
		</div>
	</div>
</div>
		
<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/guest.php'); ?>
