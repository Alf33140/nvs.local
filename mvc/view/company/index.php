<?php
$title = "Compagnies";

/* ---Header--- */
ob_start();
?>
<div class='background-img homepage-bg'>
</div>
<div class="row justify-content-center">
	<div class="col mx-2 rounded bg-light py-3 bg-opacity-75">
		<nav class='mb-3'>
			<a class='btn btn-outline-primary' href='compagnie.php'>Page compagnie</a>
			<a class='btn btn-outline-secondary' href='/'>Retour au jeu</a>
		</nav>
		<h2 class='mb-3'>Les compagnies du camp <?= $character->camp_name ?></h2>
		<div class='bg-light p-4 rounded'>
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="warning-icon-lg mt-1 me-2 float-start">
			  <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
			</svg>
			<p>
				Dans Nord vs Sud, les compagnies sont le nerf de la guerre.<br>
				Bien que l'aventure puisse se faire en solo (indépendant), il est fortement recommandé d'en intégrer une.<br>
			</p>
			<p>
				Les compagnies apportent notamment plusieurs avantages :<br>
				<span class='fst-italic'>protection des thunes, possibilité d'emprunt pour l'achat d'équipement ou d'armes, coordination et stratégie simplifiées...</span><br>
				Pour plus d'informations, n'hésitez pas à contacter le chef de compagnie ou l'officier chargé du recrutement.
			</p>
			<p>
				Aucune compagnie ne vous convient ? Vous pouvez demander à en créer une.*<br>
				<small class='text-muted'>*Attention, votre demande sera étudiée par l'État Major et doit correspondre aux besoins et critères définis par celui-ci.</small><br>
			</p>
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
					Liste des compagnies existantes
				</h4>
			</div>
			<div class="card-body p-4">
				<a href="creer_compagnie.php" class='btn btn-secondary btn-sm mb-3'>Demande de création</a>
				<?php if(isset($companies) AND !empty($companies)):?>
				<div class="row row-cols-1 row-cols-md-4 g-4">
					<?php foreach($companies as $company): ?>
					<div class="col">
						<div class="card card-company h-100 bg-light">
									<?php
										if(empty($company->image_compagnie)){
										$img_company = 'Sample_logo.png';
										}else{
											$img_company = $company->image_compagnie;
										}
									?>
							<div class='card-img-top bg-light bg-opacity-75'>
								<div class='company-img d-flex mx-auto mt-3'>
									<img class='img-fluid h-100' src="../public/img/compagnies/<?= $img_company?>" alt='Image compagnie'>
								</div>
							</div>
							<div class="card-body bg-light bg-opacity-75">
								<h5 class="card-title fw-semibold">
									<?= $company->nom_compagnie?>
								</h5>
								<p class="card-text">
									<?= $company->resume_compagnie?>
								</p>
								<p>
									<span class='fw-semibold'>Membres :</span> <?= $company->countMembers?>
								</p>
							</div>
							<a class='card-footer text-center bg-primary text-light btn btn-primary' href='compagnie.php?action=show&id=<?= $company->id_compagnie?>'>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								Plus d'info
							</a>
						</div>
					</div>
					<?php endforeach;?>
				</div>
				<?php else: ?>
				<p>
					Aucune compagnie n'existe pour ce camp.
				</p>
				<p>
					Vous pouvez demander à l'État Major (EM) la création d'une compagnie si vous le souhaitez.*<br>
					<small class='text-muted'>*Attention, la création d'une compagnie doit correspondre aux critères imposés par l'EM</small><br>
					<a href="#" class='btn btn-primary mt-3'>Faire une demande de création</a>
				</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>