<?php
$title = "Personnage";

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
					<a class='btn btn-outline-secondary' href='?action=character'>Retour aux persos</a>
					<a class='btn btn-outline-secondary' href='/'>Retour au jeu</a>
				</nav>
			</div>
		</div>
		<div class='row mb-2'>
			<div class='col'>
				<h2 class='mb-3'><?= $character->nom_perso?> [<?=$character->id_perso?>]<br>
					<span><?= $character->nom_unite?></span>
				</h2>
				
				<nav>
					<div class="nav nav-tabs" id="nav-tab" role="tablist">
						<button class="nav-link<?php if(!isset($_SESSION['flash']['tab'])):?> active<?php endif?>" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="true">Profil</button>
						<a class="nav-link" href="equipement.php">Equiper son perso</a>
						<!-- facto de la page de gestion des équipements à intégrer à terme -->
						<!--<button class="nav-link<?php if(isset($_SESSION['flash']['tab']) AND $_SESSION['flash']['tab']=='equipment'):?> active<?php endif?>" id="nav-equipment-tab" data-bs-toggle="tab" data-bs-target="#nav-equipment" type="button" role="tab" aria-controls="nav-equipment" aria-selected="true">Equipement</button>-->
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
			<div class="col mb-3">
				<div class="card">
					<div class="card-header">
						<h3 class='fs-4'>Caractéristiques</h3>
					</div>
					<div class="card-body">
						<div class='row'>
							<div class='col'>
								<div class='row'>
									<div class='col text-center mb-2'>
										<div class='avatar mx-auto mt-3 mb-2 pt-3 rounded-circle bg-camp-<?=$camp?> bg-secondary-subtle'>
											<img class="avatar-img character img-fluid w-75" src="../public/img/characters/<?= $character->image_unite?>.png" alt="<?= $character->nom_unite?>">
											<div class="fs-5 align-top unit-name bg-white bg-opacity-75"><?= $character->nom_unite ?></div>
										</div>
										<div class="fw-semibold mb-1 fs-4"><?= $character->nom_perso ?></div>
										<button type="button" class="btn btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#changeNameModal">
											<span class='align-middle'>Modifier</span>
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
											</svg>
										</button>
									</div>
									<div class='col'>
										<p>
											<span class='fw-semibold'>Grade :</span>  <?= $character->nom_grade?> <img class="" src="../public/img/ranks/<?= $character->image_grade?>" alt="image <?= $character->nom_grade?>"><br>
										</p>
										<p>
											<span class='fw-semibold'>Expérience :</span> <?= $character->xp_perso ?> XP<br>
											<span class='fw-semibold'>Points de commandement :</span> <?= $character->pc_perso ?> PC<br>
											<span class='fw-bold'>Points d'investissement :</span> <?= $character->pi_perso ?> PI
										</p>
										<button type="button" class="btn btn-primary my-4" data-bs-toggle="modal" data-bs-target="#respawnModal">
											Définir les rapatriements
										</button>
									</div>
								</div>
								<div class='row'>
									<div class='col'>
										<table class="table table-striped">
											<thead>
												<tr>
													<th scope="col"></th>
													<th scope="col">Points actuels</th>
													<th scope="col">Améliorer ?</th>
												</tr>
											</thead>
											<tbody class='align-middle'>
												<form id='upgradeCapacities' name='upgradeCapacities' method="POST" action="?action=character&op=edit&id=<?=$character->id_perso?>">
												<tr>
													<th scope="row">
														<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
															<path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
														</svg>
														Santé
													</th>
													<td><?= $character->pvMax_perso?> PV</td>
													<td>
														<?php if($pvCost<=$character->pi_perso):?>
														<button class='btn btn-success' name='pv_cost' type="submit" value="<?=$pvCost?>">+1
															<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 18.75 7.5-7.5 7.5 7.5" />
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 7.5-7.5 7.5 7.5" />
															</svg>
														</button><br>
														<?php else: ?>
														PI manquants<br>
														<?php endif;?>
														(coût <?=$pvCost?> PI)
													</td>
												</tr>
												<tr>
													<th scope="row">
														<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
															<path d="M 13.766261,0.44204939 C 11.064563,5.561603 7.019071,9.8748632 2.6868554,14.016104 l 4.8805437,4.745792 c 0.7729701,0.557053 1.4953332,1.493483 2.3441157,3.196974 H 21.527188 c 0.183299,-0.897558 0.0095,-1.926861 -0.544333,-2.522208 -1.463756,0.127698 -4.493352,-0.204244 -4.493352,-0.204244 -2.978617,-1.351509 -3.264643,-3.723149 -3.87377,-5.952248 l 3.187575,-2.56872 -1.517809,-1.6455378 c -0.01051,-0.01138 -0.0098,-0.029 0.0016,-0.03951 l 0.348152,-0.3211331 c 0.01139,-0.010506 0.029,-0.00979 0.03951,0.00161 l 1.541795,1.6715699 0.476368,-0.3838788 -1.753288,-1.9008387 c -0.0105,-0.011385 -0.0098,-0.029005 0.0016,-0.039502 l 0.348152,-0.3211151 c 0.01139,-0.010506 0.02901,-0.00979 0.03951,0.00161 l 1.777272,1.9268528 0.507983,-0.409355 -1.92184,-2.0835956 c -0.01051,-0.011385 -0.0098,-0.029005 0.0016,-0.039502 l 0.348155,-0.3211161 c 0.01138,-0.010497 0.02901,-0.00979 0.0395,0.00161 l 1.945826,2.109592 4.256695,-3.4302669 C 21.028133,0.83490409 17.416535,4.5626595 13.766261,0.44204939 Z M 2.2435604,14.439438 C 2.0670342,14.607008 1.8932061,14.7762 1.7159111,14.943311 l 3.017634,2.801544 c 0.434243,0.0937 0.8275529,0.178283 1.1951494,0.277967 z m 7.9813536,8.16933 c 0.101137,0.217539 0.202132,0.434474 0.307502,0.674471 0,0 7.506265,0.640211 10.348326,-0.03405 0.179724,-0.175551 0.324839,-0.395291 0.437536,-0.640428 z" />
														</svg>
														Mouvement
													</th>
													<td><?= $character->pmMax_perso?> PM</td>
													<td>
														<?php if($pmCost<=$character->pi_perso):?>
														<button class='btn btn-success' name='pm_cost' type="submit" value="<?=$pmCost?>">+1
															<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 18.75 7.5-7.5 7.5 7.5" />
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 7.5-7.5 7.5 7.5" />
															</svg>
														</button><br>
														<?php else: ?>
														PI manquants<br>
														<?php endif;?>
														(coût <?=$pmCost?> PI)
													</td>
												</tr>
												<tr>
													<th scope="row">
														<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
															<path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule="evenodd" />
														</svg>
														Points d'action
													</th>
													<td><?= $character->paMax_perso?> PA</td>
													<td>
														<?php if($paCost<=$character->pi_perso):?>
														<button class='btn btn-success' name='pa_cost' type="submit" value="<?=$paCost?>">+1
															<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 18.75 7.5-7.5 7.5 7.5" />
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 7.5-7.5 7.5 7.5" />
															</svg>
														</button><br>
														<?php else: ?>
														PI manquants<br>
														<?php endif;?>
														(coût <?=$paCost?> PI)
													</td>
												</tr>
												<tr>
													<th scope="row">
														<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
															<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
															<path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
														</svg>
														Perception
													</th>
													<td><?= $character->perception_perso?></td>
													<td>
														<?php if($perceptionCost<=$character->pi_perso):?>
														<button class='btn btn-success' name='percep_cost' type="submit" value="<?=$perceptionCost?>">+1
															<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 18.75 7.5-7.5 7.5 7.5" />
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 7.5-7.5 7.5 7.5" />
															</svg>
														</button><br>
														<?php else: ?>
														PI manquants<br>
														<?php endif;?>
														(coût <?=$perceptionCost?> PI)
													</td>
												</tr>
												<tr>
													<th scope="row">
														<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
															<path d="M 12 1.68 A 10.32 10.32 0 0 0 1.68 12 A 10.32 10.32 0 0 0 12 22.32 A 10.32 10.32 0 0 0 22.32 12 A 10.32 10.32 0 0 0 12 1.68z M 12 3.36 A 8.6400003 8.6400003 0 0 1 20.64 12 A 8.6400003 8.6400003 0 0 1 12 20.64 A 8.6400003 8.6400003 0 0 1 3.36 12 A 8.6400003 8.6400003 0 0 1 12 3.36 z M 10.67625 6 L 10.67625 10.701 L 6 10.701 L 6 13.3485 L 10.67625 13.3485 L 10.67625 18 L 13.32375 18L 13.32375 13.3485 L 18 13.3485 L 18 10.701 L 13.32375 10.701 L 13.32375 6 L 10.67625 6 z" />
														</svg>
														Récupération
													</th>
													<td><?= $character->recup_perso?></td>
													<td class=''>
														<?php if($recupCost<=$character->pi_perso):?>
														<button class='btn btn-success' name='recup_cost' type="submit" value="<?=$recupCost?>">+1
															<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 18.75 7.5-7.5 7.5 7.5" />
																<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 7.5-7.5 7.5 7.5" />
															</svg>
														</button><br>
														<?php else: ?>
														PI manquants<br>
														<?php endif;?>
														(coût <?=$recupCost?> PI)
													</td>
												</tr>
												<input id="form" name="form" type="hidden" value="upgradeCapacities" />
												<input id="character" name="character" type="hidden" value="<?= $character->id_perso?>" />
												</form>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card">
					<div class="card-header">
						<h3 class='fs-4'>Roleplay (RP)</h3>
					</div>
					<div class="card-body">
						<form class='row mb-4' action="?action=character&op=edit&id=<?= $character->id_perso?>" method="post" name="characDescForm">
							<label for="character_desc" class="col-12">
								Description<br>
								<small class="text-muted">Raconte nous ton histoire soldat. Visible en cliquant sur le personnage</small>
							</label>
							<div class="col-12">
								<?php if(isset($_SESSION['errors'])&& !empty($_SESSION['errors']['character_desc'])): ?>
								<div class='p-2 alert alert-danger' role="alert">
									<?php foreach($_SESSION['errors']['character_desc'] as $error):?>
									<span class='align-middle fw-semibold'><?= $error?></span>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>
								<textarea class="form-control" name="character_desc" id="character_desc" rows="3" maxlength="650" required><?= $character->description_perso?></textarea>
								<small class="text-muted">650 caractères maximum (espaces compris)</small><br>
								<button class='mt-3 btn btn-primary'>actualiser</button>
							</div>
							<input id="form" name="form" type="hidden" value="characDescForm" />
							<input id="character" name="character" type="hidden" value="<?= $character->id_perso?>" />
						</form>
						<form class='row' action="?action=character&op=edit&id=<?= $character->id_perso?>" method="post" name="dailyMsgForm">
							<label for="character_message" class="col-12">
								Message du jour<br>
								<small class="text-muted">Visible en cliquant sur le personnage sur la carte</small>
							</label>
							<div class="col-sm-12">
								<?php if(isset($_SESSION['errors'])&& !empty($_SESSION['errors']['character_message'])): ?>
								<div class='p-2 alert alert-danger' role="alert">
									<?php foreach($_SESSION['errors']['character_message'] as $error):?>
									<span class='align-middle fw-semibold'><?= $error?></span>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>
								<textarea class="form-control" name="character_message" id="character_message" rows="2" maxlength="125" required><?= $character->message_perso?></textarea>
								<small class="text-muted">125 caractères maximum (espaces compris)</small><br>
								<button class='mt-3 btn btn-primary'>actualiser</button>
							</div>
							<input id="form" name="form" type="hidden" value="dailyMsgForm" />
							<input id="character" name="character" type="hidden" value="<?= $character->id_perso?>" />
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tab-pane fade<?php if(isset($_SESSION['flash']['tab']) AND $_SESSION['flash']['tab']=='equipment'):?> show active<?php endif?>" id="nav-equipment" role="tabpanel" aria-labelledby="nav-equipment-tab" tabindex="0">
		<div class="row">
			<div class="col mb-3">
				<div class="card mb-3">
					<div class="card-header">
						<h3 class='fs-4'>Equipement porté</h3>
					</div>
					<div class="card-body">
						<div class='row'>
							<div class='col-3'>
								<div class='row row-cols-1 g-2'>
									<?php foreach($equippedWeapons as $weapon): ?>
									<div class="col text-center">
										<img height='100px' width='100px' src="../public/img/weapons/<?= $weapon->image_arme?>" class="img-thumbnail p-2" alt="<?= $weapon->nom_arme?>">
										<p class=''>
												<span class='fw-semibold'><?= $weapon->nom_arme?></span><br>
												<span class='text-muted'><?php if($weapon->porteeMax_arme<2){echo "corps à corps";}else{echo "distance";};?></span>
										</p>
									</div>
									<?php endforeach;?>
								</div>
							</div>
							<div class='col text-center'>
								<div class='row'>
									<div class='col text-start'>
										<ul class="list-group list-group-flush">
											<li class="list-group-item">
												<span class='fw-semibold'>Attaque</span>
												<ul class="list-group list-group-flush">
													<li class="list-group-item">C à C :</li>
													<li class="list-group-item">Distance :</li>
												</ul>
											</li>
											<li class="list-group-item">
												<span class='fw-semibold'>Défense</span>
												<ul class="list-group list-group-flush">
													<li class="list-group-item">C à C :</li>
													<li class="list-group-item">Distance :</li>
												</ul>
											</li>
									</div>
									<div class='col'>
										<img class="character-img img-fluid" src="../public/img/characters/<?= $character->image_unite?>.png" alt="<?= $character->nom_unite?>">
									</div>
								</div>
								<div class="mt-2">
										<div class="card h-100">
											<div class='card-header'>
												<h5 class="">Accessoires</h5>
											</div>
											<div class="card-body">
												<div class='row row-cols-3 g-2'>
												<?php foreach($equippedEquipments as $equipment): ?>
													<div class="col">
														<img height='100px' width='100px' src="../public/img/items/<?= $equipment->image_objet?>" class="img-thumbnail p-2 w-75 mx-auto" alt="<?= $equipment->nom_objet?>">
														<div class="card-body py-0">
														</div>
														<p class=''>
															<span class='fw-semibold'><?= $equipment->nom_objet?></span>
														</p>
													</div>
												<?php endforeach;?>
												</div>
											</div>
										</div>
									</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class='col'>
				<div class="card mb-2">
					<div class="card-header">
						<h3 class='fs-4'>Armes dans le sac</h3>
					</div>
					<div class="card-body">
						<div class='row row-cols-2 row-cols-md-4 g-2'>
					<?php if(!empty($inBagWeapons)):
							foreach($inBagWeapons as $weapon):
								if(!in_array($weapon->id_arme,$allowedWeaponsIds)):
									$weapon_border_class = " border-danger";
									$weapon_opacity_class = " opacity-50";
								else:
									$weapon_border_class = "";
									$weapon_opacity_class = "";
								endif;
							?>
							<div class="col">
								<div class="card h-100<?= $weapon_border_class?><?= $weapon_opacity_class?>">
									<?php if(!in_array($weapon->id_arme,$allowedWeaponsIds)):?>
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 no-symbol fw-semibold text-danger bg-light rounded-circle">
										<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
									</svg>
									<?php endif;?>
									<img src="../public/img/weapons/<?= $weapon->image_arme?>" class="card-img-top p-2 w-75 mx-auto" alt="<?= $weapon->nom_arme?>">
									<div class="card-body pt-0">
									</div>
									<div class='card-footer'>
										<span class='fw-semibold'><?= $weapon->nom_arme?></span><br>
										<span class='text-muted'><?php if($weapon->porteeMax_arme<2){echo "corps à corps";}else{echo "distance";};?></span>
									<?php if(in_array($weapon->id_arme,$allowedWeaponsIds)):?>
										<button class='mt-2 btn btn-sm btn-success w-100'>Equiper</button>
									<?php else:?>
										<button class='mt-2 btn btn-sm btn-secondary w-100' disabled>Non équipable</button>
									<?php endif;?>										
									</div>
								</div>
							</div>
							<?php endforeach;
						else:?>
							<p>
								Aucune arme dans le sac...
							</p>
					<?php endif;?>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class='fs-4'>Accessoires dans le sac</h3>
					</div>
					<div class="card-body">
						<div class='row row-cols-2 row-cols-md-4 g-2'>
						<?php if(!empty($inBagEquipments)):
								foreach($inBagEquipments as $equipment):
								if(!in_array($equipment->id_objet,$allowedEquipmentsIds)):
									$equip_border_class = " border-danger";
									$equip_opacity_class = " opacity-50";
								else:
									$equip_border_class = "";
									$equip_opacity_class = "";
								endif;
							?>
							<div class="col">
								<div class="card h-100<?= $equip_border_class?><?= $equip_opacity_class?>">
									<?php if(!in_array($equipment->id_objet,$allowedEquipmentsIds)):?>
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 no-symbol fw-semibold text-danger bg-light rounded-circle">
										<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
									</svg>
									<?php endif;?>
									<img src="../public/img/items/<?= $equipment->image_objet?>" class="card-img-top p-2 w-75 mx-auto" alt="<?= $equipment->nom_objet?>">
									<div class="card-body pt-0">
									</div>
									<div class='card-footer'>
										<span class='fw-semibold'><?= $equipment->nom_objet?></span>
									<?php if(in_array($equipment->id_objet,$allowedEquipmentsIds)):?>
										<button class='mt-2 btn btn-sm btn-success w-100'>Equiper</button>
									<?php else:?>
										<button class='mt-2 btn btn-sm btn-secondary w-100' disabled>Non équipable</button>
									<?php endif;?>										
									</div>
								</div>
							</div>
							<?php endforeach;
							else:?>
								<p>
									Aucun accessoire dans le sac...
								</p>
						<?php endif;?>
							<!--
							<div class='col'>
								<div class="card h-100">
									<div class="card-header">
										<h3 class='fs-4'>Montures</h3>
									</div>
									<div class="card-body">
									</div>
								</div>
							</div>
							-->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="respawnModal" tabindex="-1" aria-labelledby="respawnChoice" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="respawnChoice">Choix d'un bâtiment pour le rapatriement</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id='respawns' name='respawns' method="POST" action="?action=character&op=edit&id=<?=$character->id_perso?>">
				<div class="modal-body">
					<p>
						Seuls les hôpitaux, les fortins et les forts peuvent servir de lieu de rapatriement.<br>
						un personnage ne peut pas être rapatrié dans un bâtiment en état de siège, en capacité maximale, bloqué ou capturé.
					</p>
					<h2 class='fs-5 mb-3'>
						Ordre de rapatriement en cas de capture :
					</h2>
					
					<ol class="list-group list-group-numbered">
						<?php foreach($respawnBuildings as $id => $buildings):?>
						<li class="list-group-item p-3 fw-semibold">
							<label for="<?= $id ?>_select" class="form-label"><?= $buildings['name']?></label>
							<select name='<?= $id ?>_select' id='<?= $id ?>_select' class="form-select" aria-label="sélection <?= $buildings['name']?>">
								<option value='0'>non défini</option>
								<?php foreach($buildings['buildings'] as $building):?>
								<option <?php if(in_array($building->id_instanceBat,$characRespawns)){echo 'selected';};?> value="<?=$building->id_instanceBat?>"><?= $buildings['name']?><?php if(!empty($building->nom_instance)):?> <?=$building->nom_instance?><?php endif;?> [<?= $building->id_instanceBat?>] (<?=$building->x_instance?>/<?=$building->y_instance?>)</option>
								<?php endforeach;?>
							</select>
						</li>
						<?php endforeach;?>
						<li class="list-group-item p-3 fw-semibold">
							Respawn aléatoire
						</li>
					</ol>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
					<button type="submit" class="btn btn-primary">Sauvegarder</button>
					<input id="form" name="form" type="hidden" value="respawns" />
					<input id="character" name="character" type="hidden" value="<?= $character->id_perso?>" />
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="changeNameModal" tabindex="-1" aria-labelledby="changeNameTitle" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="changeNameTitle">Personnaliser le nom du perso<br><span class='fw-semibold'><?= $character->nom_perso ?></span></h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id='changeNameForm' name='changeNameForm' method="POST" action="?action=character&op=edit&id=<?=$character->id_perso?>">
				<div class="modal-body">
					<h2 class='fs-5'>
						Quelques conseils pour un jeu plus immersif
					</h2>
					<p class='mb-0 mt-2'>
						Choisissez un nom :
					</p>
					<ul>
						<li>Qui correspond ou se rapproche de l'univers du jeu</li>
						<li>Qui n'est pas anachronique</li>
						<li>Qui correspond à votre RP (roleplay)</li>
					</ul>
					<label for="changeNameInput" class="form-label fs-5 fw-semibold">Nouveau nom</label>
					<input type="text" class="form-control" name='changeNameInput' id="changeNameInput" placeholder="<?= $character->nom_perso?>" minlength="3" maxlength="50" pattern="^[A-Za-zÀ-ÖØ-öø-ÿœŒæÆçÇÉéÈèÊêËëÀàÂâÎîÏïÔôÛûÙùÜüŸÿ][A-Za-zÀ-ÖØ-öø-ÿœŒæÆçÇÉéÈèÊêËëÀàÂâÎîÏïÔôÛûÙùÜüŸÿ'’\-–—«»&quot; ]{2,49}$" required>
					<small class='form-text'>De 3 à 50 caractères, commençant par une lettre. Espaces, tirets, guillemets et apostrophes autorisés.</small>
					<p class='alert alert-danger mt-4'>
						Attention :<br>
						Les noms considérés comme offensants, injurieux, haineux, ou ne correspondant pas à l'esprit du jeu seront modifiés ou supprimés
					</p>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
					<button type="submit" class="btn btn-primary">Sauvegarder</button>
					<input id="form" name="form" type="hidden" value="changeNameForm" />
					<input id="character" name="character" type="hidden" value="<?= $character->id_perso?>" />
				</div>
			</form>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>