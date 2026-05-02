<?php
$title = "Etat Major";

/* ---Header--- */
ob_start();
?>
<div class='background-img emBg'>
</div>
<div class="row justify-content-center">
	<div class="col mx-2 rounded bg-light py-3 bg-opacity-75">
		<div class='mb-3'>
			<a class='btn btn-outline-secondary' href='/'>Retour au jeu</a>
		</div>
		<img class='float-start me-3' src='../images/<?php echo $image_em; ?>' width="80" height="60" alt="">
		<h2 class='mb-4 pt-2'>Etat Major</h2>
		<?= $nb_persos_em ?> membres dans l'Etat Major :
		<?php if(isset($em_members) && !empty($em_members)):?>
		<ul class='list-group list-group-horizontal mt-1 mb-3'>
			<?php foreach($em_members as $member): ?>
			<li class='list-group-item list-group-item-primary'><?= $member->nom_perso?> [<a href='evenement.php?infoid=<?= $member->id_perso?>' class='text-decoration-none'><?= $member->id_perso?></a>]</li>
			<?php endforeach;?>
		</ul>
		<?php endif; ?>
		<nav class='nav nav-tabs mb-3'>
			<a class="nav-link active" href="#">Gestion des compagnies</a>
			<div class="dropdown">
				<a class="nav-link dropdown-toggle" href="#" role="button" id="message_menu" data-bs-toggle="dropdown" aria-expanded="false">
					Messages
				</a>
				<div class="dropdown-menu" aria-labelledby="message_menu">
					<a class="dropdown-item" href="em_message.php?cible=camp">Message au camp</a>
					<a class="dropdown-item" href="em_message.php?cible=compagnie">Message aux chefs de compagnie/section</a>
					<a class="dropdown-item" href="em_message.php?cible=em">Messages aux membres de l'Etat Major</a>
				</div>
			</div>
		</nav>
	</div>
</div>
<?php
$header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<?php if(isset($error)): ?>
<p class='alert alert-danger'>
	<?= $error ?>
</p>
<?php endif; ?>
<?php if(isset($_SESSION['flash'])&& !empty($_SESSION['flash'])): ?>
<div class="row">
	<div class='col'>
		<div class='alert alert-<?= $_SESSION['flash']['class'] ?>'>
			<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span class='align-middle'><?= $_SESSION['flash']['message'] ?></span>
		</div>
	</div>
</div>
<?php endif ?>
<div class="row">
	<div class="col">
		<div class="card shadow">
			<div class='card-header'>
				<h4>Gestion des compagnies</h4>
				<nav>
					<div class='nav nav-tabs mt-3' id="comp-nav" role="tablist">
						<button class="nav-link active" id="nav-compagnies-tab" data-bs-toggle="tab" data-bs-target="#nav-compagnies" type="button" role="tab" aria-controls="nav-compagnies" aria-selected="true">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
							</svg>
							Liste des compagnies
						</button>
						<button class="nav-link" id="nav-demands-tab" data-bs-toggle="tab" data-bs-target="#nav-demands" type="button" role="tab" aria-controls="nav-demands" aria-selected="false">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
							</svg>
							Demandes de création <span class='badge rounded-pill bg-danger'><?php if(isset($waiting_votes) && $waiting_votes>0): echo $waiting_votes; endif ?></span>
						</button>
					</div>
				</nav>
			</div>
			<div class="card-body">
				<div class="tab-content" id="nav-compContent">
					<div class='tab-pane fade show active' id="nav-compagnies" role="tabpanel" aria-labelledby="nav-compagnies-tab" tabindex="0">
						<h5 class="card-title mb-3">Compagnies</h5>
						<?php if(isset($companies) && !empty($companies)): ?>
						<div class="table-responsive">
							<table class="table table-striped">
								<caption>Liste des compagnies</caption>
								<thead class='table-light'>
									<tr>
										<th scope="col">Nom</th>
										<th class='w-25' scope="col">Résumé</th>
										<th class='w-25' scope="col">Catégorie</th>
										<th class='text-center' scope="col">membres</th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
								<?php foreach($companies as $company):?>
									<tr>
										<th scope="row" class='p-4'>
											<?= $company->nom_compagnie?><br>
											<?php if(empty($company->image_compagnie)){
												$img_company = 'Sample_logo.png';
											}else{
												$img_company = $company->image_compagnie;
											}
											?>
											<img class="img-fluid" src="../public/img/compagnies/<?= $img_company?>" alt='Image compagnie'>
										</th>
										<td>
											<?= $company->resume_compagnie?>
										</td>
										<td>
											<?= (isset($company->genie_civil) AND $company->genie_civil==1)?'Génie militaire':''?>
										</td>
										<td class='text-center'>
											<?= $company->countMembers?>
										</td>
										<td>
											<a class='btn btn-info mb-2' href='compagnie.php?action=show&id=<?= $company->id_compagnie?>'>Plus d'info</a><br>
											<!-- Bouton "suspendre" utile ou non ? -->
											<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteComp<?= $company->id_compagnie?>">
												Dissoudre 
											</button>
											<!-- Modal de suppression -->
											<div class="modal fade" id="deleteComp<?= $company->id_compagnie?>" tabindex="-1" aria-labelledby="deleteCompLabel<?= $company->id_compagnie?>" aria-hidden="true">
												<div class="modal-dialog modal-dialog-centered">
													<div class="modal-content">
														<div class="modal-header">
															<h1 class="modal-title fs-5" id="deleteCompLabel<?= $company->id_compagnie?>">Dissoudre la compagnie "<?= $company->nom_compagnie?>"</h1>
															<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body">
															Vous êtes sur le point de dissoudre la compagnie "<?= $company->nom_compagnie?>".<br>
															<ul class='mt-3'>
																<li>La compagnie sera supprimée</li>
																<li>L'argent de la compagnie sera perdu</li>
																<li>Tous les membres de la compagnie deviendront indépendants</li>
															</ul>
															<div class='alert alert-danger text-center'>
																<span class='fw-bold'>Attention</span><br>
																Cette action est irréversible
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
															<form method="post" name="delete_comp" action="?action=delete_comp&id=<?= $company->id_compagnie?>">
																<input type="hidden" name="compId" value="<?= $company->id_compagnie?>">
																<button type="submit" class="btn btn-danger">Dissoudre</button>
															</form>
														</div>
													</div>
												</div>
											</div>
										</td>
									</tr>
								<?php endforeach;?>
								</tbody>
							</table>
						</div>
						<?php else: ?>
							<p class=''>Aucune compagnie existante</p>
						<?php endif ?>
					</div>
					<div class='tab-pane fade' id="nav-demands" role="tabpanel" aria-labelledby="nav-demands-tab" tabindex="0">
						<h5 class="card-title mb-3">Demandes de création</h5>
						<?php if(isset($company_demands) && !empty($company_demands)): ?>
						<div class="table-responsive">
							<table class="table table-striped">
								<caption>Demandes de compagnie</caption>
								<thead class='table-light'>
									<tr>
										<th scope="col">Demandeur</th>
										<th class='w-25' scope="col">Nom</th>
										<th class='text-center' scope="col">Description</th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($company_demands as $demand): ?>
									<tr>
										<th scope="row" class='p-4'>
												<?= $demand->nom_perso?> [<?=$demand->id_perso?>]
										</th>
										<td class='p-4 w-25'>
											<p>
												<?= $demand->nom_compagnie ?>
											</p>
										</td>
										<td class='w-50 p-4'>
											<p>
												<span class='fw-semibold'>Résumé : </span><br>
												<?= $demand->description_compagnie?>
											</p>
										</td>				
										<td class='p-4'>
											<?php if(isset($demand->votes_result) AND $demand->votes_result!=0):
												if($demand->votes_result==1){
													$voteText = 'demande acceptée et compagnie créée';
													$voteClass='text-success';
													$icon = 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
												}elseif($demand->votes_result==-1){
													$voteText = 'demande rejetée';
													$voteClass='text-danger';
													$icon = 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
												}
											?>
												<p class='fw-bold <?= $voteClass ?>'>
													<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
														<path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>" />
													</svg> <?= $voteText ?>
												</p>
												<form method="post" name="delete_demand" action="?action=delete_demand&id=<?= $demand->id?>">
													<input type="hidden" name="compId" value="<?= $demand->id?>">
													<button type="submit" name="deleteDemand" value="1" class='btn btn-danger btn-sm'>
														<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
														  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
														</svg>
														Supprimer
													</button>
												</form>
											<?php elseif(isset($demand->alreadyVoted) AND $demand->alreadyVoted==1):?>
												<?php
													$pour = '';
													$contre = '';
													
													foreach($demand->individualVotes as $vote):
														if($vote['vote']==1)
														{$pour++;}
														else{$contre++;}
														
														if($vote['id_perso']==$perso->id_perso):
															if($vote['vote']==1){
																$voteText = 'Pour';
																$voteClass='text-success';
																$icon = 'M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z';
															}elseif($vote['vote']==-1){
																$voteText = 'Contre';
																$voteClass='text-danger';
																$icon = 'M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54';
															}else{
																$voteText = 'Abstention';
																$voteClass='text-secondary';
																$icon = 'M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z';
															}
														?>
														<span class='fw-semibold'>Vous avez voté</span><br>
														<span class='fw-bold <?= $voteClass ?>'>
															<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
																<path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>" />
															</svg> <?= $voteText ?>
														</span>
														<?php endif;
													endforeach;?>
												<br><br>
												<span class='fw-semibold'>Résultats des votes :</span><br>
												<?= ($pour)?$pour:0 ?> pour / <?= ($contre)?$contre:0 ?> contre
												<?php if(count($demand->individualVotes)==$nb_persos_em):?>
												<form method="post" name="comp_validation" action="?action=comp_validation&id=<?= $demand->id?>">
													<input type="hidden" name="compId" value="<?= $demand->id?>">
													<?php
														if($pour==$contre):
													?>
														Vote nul.<br>
														<button type="submit" name="validateChoice" value="0" class="btn btn-secondary mt-2">Recommencer le vote ?</button>
													<?php
														elseif($pour>$contre):
													?>
														<button type="submit" name="validateChoice" value="1" class="btn btn-success mt-2">Créer la compagnie</button>
														<?php if($contre>0):?>
														<button type="submit" name="validateChoice" value="0" class="btn btn-secondary btn-sm mt-2">Recommencer le vote ?</button>
														<?php endif;?>
													<?php
														else:
													?>
														<button type="submit" name="validateChoice" value="-1" class="btn btn-danger mt-2">Refuser la compagnie</button>
														<?php if($pour>0):?>
														<button type="submit" name="validateChoice" value="0" class="btn btn-secondary btn-sm mt-2">Recommencer le vote ?</button>
														<?php endif;?>
													<?php
														endif;
													?>
												</form>
												<?php
													else:
												?>
													<br><br>
													<span class='fw-semibold'>Votants :</span> <?= count($demand->individualVotes);?>/<?=$nb_persos_em?>
													<button class='btn btn-dark btn-sm mt-2' disabled>En attente des autres votes</button>
												<?php
													endif;
											else:?>
											<?php if(isset($demand->votes_result) AND $demand->votes_result==0):?>
												<p class='fw-bold text-secondary'>
													<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
														<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
													</svg> Vote réinitialisé
												</p>
											<?php endif;?>
											<form name='company<?= $demand->id?>_vote' method='post' action='?action=vote&id=<?= $demand->id?>'>
												<input type="hidden" name="compVoteId" value="<?= $demand->id?>">
												<div class="form-check mb-2">
													<input class="form-check-input vote-input" type="radio" name="compVoteOption" id="comp<?= $demand->id?>voteOption1" value="1">
													<label class="form-check-label btn btn-success w-100" for="comp<?= $demand->id?>voteOption1">
														<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
														  <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
														</svg>
														<small>Pour</small>
													</label>
												</div>
												<div class="form-check">
													<input class="form-check-input vote-input" type="radio" name="compVoteOption" id="comp<?= $demand->id?>voteOption2" value="-1">
													<label class="form-check-label btn btn-danger w-100" for="comp<?= $demand->id?>voteOption2">
														<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
														  <path stroke-linecap="round" stroke-linejoin="round" d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
														</svg>
														<small>Contre</small>
													</label>
												</div>
												<button type='submit' class='w-100 btn btn-primary mt-3'>Voter</button>
											</form>
											<?php endif;?>
										</td>
									</tr>
									<?php endforeach ?>
								</tbody>
							</table>
						</div>
						<?php else: ?>
							<p class=''>Aucune demande en cours</p>
						<?php endif ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>