<?php
$title = "Compagnie";

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
					<a class='btn btn-outline-primary' href='?all'>Toutes les compagnies</a>
					<?php if(!empty($character->id_compagnie) AND $character->id_compagnie!=$company->id_compagnie):?><a class='btn btn-outline-primary' href='?action=show&id=<?=$character->id_compagnie?>'>Ma compagnie</a><?php endif;?>
					<a class='btn btn-outline-secondary' href='/'>Retour au jeu</a>
				</nav>
			</div>
		</div>
		<div class='row'>
			<div class='col'>
				<h2 class=''><?= $company->nom_compagnie?></h2>
				<p>
					Bienvenue dans la tente de la compagnie "<?= $company->nom_compagnie?>" !
				</p>
			</div>
		</div>
		<div class='row'>
			<div class='col-12 col-sm'>
				<div class='company-img float-start'>
					<img src="../public/img/compagnies/<?=$img_company?>" class="img-fluid" alt="logo de compagnie">
				</div>
				<p>
					<span class='fw-semibold'>Membres :</span> <?= count($companyMembers)?>/80<br>
					<?php switch($character->role_level):
						case 1 :
						case 2 :
						case 4 :
					?>
					<?php if($waitingDemandIn>0):?><span class='fw-semibold badge bg-success'>Incorporation : <?= $waitingDemandIn ?> demande(s)</span><?php endif;?>
					<?php if($waitingDemandOut>0):?><span class='fw-semibold badge bg-danger'>Démission : <?= $waitingDemandOut ?> demande(s)</span><?php endif;?>
					<br>
					<?php endswitch;?>
					<span class='fw-semibold'>Résumé :</span><br>
					<?= $company->resume_compagnie?>
				</p>
			</div>
			<div class='col-12 col-sm-7'>
				<p>
					<span class='fw-semibold'>Description :</span><br>
					<?= $company->description_compagnie?>
				</p>
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
	<div class="col-12 col-md mt-3 mt-md-0 order-2">
		<div class="card">
			<div class="card-body">
				<?php if(isset($companyMembers) AND !empty($companyMembers)):?>
				<div class="table-responsive">
					<h3 class='fs-4'>Liste des membres</h3>
					<table class="table table-striped table-hover">
						<thead class='table-light'>
							<tr>
								<th class='w-50' scope="col">Nom</th>
								<th class='w-25' scope="col">Fonction</th>
								<?php if($character->id_compagnie==$company->id_compagnie AND $character->attenteValidation_compagnie==0): ?>
								<th class='w-25 text-center' scope="col">Position</th>
								<th scope="col"></th>
								<?php endif;?>
							</tr>
						</thead>
						<tbody>
						<?php foreach($companyMembers as $member):
								switch($member->role_level){
									case 1 :
										$companyLeader = $member->nom_perso.' ['.$member->id_perso.']';
										break;
									case 2 :
										$companySecond = $member->nom_perso.' ['.$member->id_perso.']';
										break;
									case 3 :
										$companytreasurer = $member->nom_perso.' ['.$member->id_perso.']';
										break;
									case 4 :
										$companyRecruiter = $member->nom_perso.' ['.$member->id_perso.']';
										break;
									case 5 :
										$companyDiplomat = $member->nom_perso.' ['.$member->id_perso.']';
										break;
								}
						?>
							<tr class=''>
								<th>
									<img class="img-fluid float-start me-3" src="../public/img/ranks/<?= $member->image_grade?>" alt='Image compagnie'>
									<span class='fw-normal'><?= $member->nom_grade?></span><br>
									<?= $member->nom_perso?> [<?= $member->id_perso?>]
								</th>
								<td>
									<?= $member->nom_poste?>
								</td>
								<?php if($character->id_compagnie==$company->id_compagnie AND $character->attenteValidation_compagnie==0):?>
								<td class='text-center'>
									<?= $member->x_perso?>/<?= $member->y_perso?>
								</td>
								<td class='text-center'>
									<a class='btn btn-outline-primary btn-sm mb-2' href='evenement.php?infoid=<?=$member->id_perso?>'>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
										</svg>
										Détails
									</a><br>
									<?php if(in_array($character->role_level,[1,2]) AND $character->id_perso<>$member->id_perso AND $member->role_level<>1):?>
										<button type="button" class="btn btn-light btn-sm text-danger" data-bs-toggle="modal" data-bs-target="#dismiss<?= $member->id_perso?>">
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
											</svg>
											Renvoyer 
										</button>
										<!-- Modal de recrutement -->
										<div class="modal fade" id="dismiss<?= $member->id_perso?>" tabindex="-1" aria-labelledby="dismissLabel<?= $member->id_perso?>" aria-hidden="true">
											<div class="modal-dialog modal-dialog-centered">
												<div class="modal-content">
													<div class="modal-header text-start">
														<h1 class="modal-title fs-5" id="dismissLabel<?= $member->id_perso?>">Renvoyer un membre<br><?= $member->nom_perso?> [<?= $member->id_perso?>]</h1>
														<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
													</div>
													<div class="modal-body">
														<p>
															Voulez-vous vraiment renvoyer <span class='fw-semibold'><?= $member->nom_perso?> [<?= $member->id_perso?>]</span> ?<br>
															Ce perso devra faire une nouvelle demande d'intégration s'il veut revenir.
														</p>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
														<form method="post" name="delete_member" action="?action=quit&id=<?= $company->id_compagnie?>">
															<input type="hidden" name="compId" value="<?= $company->id_compagnie?>">
															<input type="hidden" name="memberID" value="<?= $member->id_perso?>">
															<button type="submit" class="btn btn-danger">Congédier</button>
														</form>
													</div>
												</div>
											</div>
										</div>
									<?php endif;?>
								</td>
								<?php endif;?>
							</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				</div>
				<?php else: ?>
				<p>
					Cette compagnie ne contient aucun membre.
				</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="col-12 col-md-5 order-1">
		<div class="card">
			<div class="card-body">
				<div class='row'>
					<div class='col-12 col-sm'>
						<?php if($character->id_compagnie==$company->id_compagnie): ?>
							<?php if($character->attenteValidation_compagnie==1):?>
							<p class='alert alert-info'>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon-lg mt-1 me-2 float-start">
								  <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								Votre demande d'incorporation est en attente. Vous recevrez un message lorsque la décision sera prise.<br>
								<button type="button" class="btn btn-sm btn-outline-danger mt-3" data-bs-toggle="modal" data-bs-demandtype="cancel" data-bs-target="#quitCompModal">
										Annuler la demande
								</button>
							</p>
							<?php else: ?>
							<ul class="list-group list-group-flush">
								<li class="list-group-item list-group-item-action ps-0">
									<a class='btn btn-warning w-100 text-start' href="bank.php?id=<?= $company->id_bank?>&action=show">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
										</svg>
										Vos thunes
									</a>
								</li>
							<?php switch($character->role_level):
								case 1 :
								case 2 :
							?>
								<li class="list-group-item list-group-item-action ps-0">
									<a class="btn btn-outline-primary w-100 text-start" href="bank.php?id=<?= $company->id_bank?>&action=treasury">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" stroke-linecap="round" class="w-6 h-6">
											<rect x="2.6651" y="2.2536" width="18.67" height="19.493" rx="0" ry=".94274"/>
											<rect x="5.0463" y="4.7397" width="13.907" height="14.521" rx="0" ry=".70226"/>
											<path d="m8.5254 7.4453c-0.27551 0-0.55061 0.10725-0.76172 0.31836-0.42221 0.42221-0.42221 1.1012 0 1.5234l1.7656 1.7656a2.6517 2.6517 0 0 0-0.18164 0.94727 2.6517 2.6517 0 0 0 0.18164 0.94727l-1.7656 1.7656c-0.42221 0.42221-0.42221 1.1012 0 1.5234s1.1012 0.42221 1.5234 0l1.7656-1.7656a2.6517 2.6517 0 0 0 0.94727 0.18164 2.6517 2.6517 0 0 0 0.95117-0.17774l1.7617 1.7617c0.42221 0.42221 1.1012 0.42221 1.5234 0s0.42221-1.1012 0-1.5234l-1.7617-1.7617a2.6517 2.6517 0 0 0 0.17774-0.95117 2.6517 2.6517 0 0 0-0.17774-0.95117l1.7617-1.7617c0.42221-0.42221 0.42221-1.1012 0-1.5234-0.21111-0.21111-0.48621-0.31836-0.76172-0.31836-0.2755 0-0.55061 0.10725-0.76172 0.31836l-1.7617 1.7617a2.6517 2.6517 0 0 0-0.95117-0.17773 2.6517 2.6517 0 0 0-0.94727 0.18164l-1.7656-1.7656c-0.21111-0.21111-0.48621-0.31836-0.76172-0.31836z"/>
											<circle cx="12" cy="12" r="2.6517"/>
											<circle cx="12" cy="12" r=".76767" stroke-width=".5"/>										
										</svg>
										Trésorerie
										<?php if($waitingLoans->loans>0):?>
											<span class="ms-3 badge rounded-pill bg-warning text-black">
												<?= $waitingLoans->loans ?>
												<span class="visually-hidden">emprunts en attente</span>
											</span>
										<?php endif;?>
									</a>
								</li>
								<li class="list-group-item list-group-item-action ps-0">
									<a class='btn btn-primary w-100 text-start' href="admin_compagnie.php?id_compagnie=<?= $company->id_compagnie?>">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
											<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
										</svg>
										Administration
									</a>
								</li>
								<li class="list-group-item list-group-item-action ps-0">
									<a class="btn btn-outline-primary w-100 text-start" href="recrut_compagnie.php?id_compagnie=<?= $company->id_compagnie?>">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
										</svg>
										<span class='me-2'>Recrutement</span>
										<?php if($waitingDemandIn>0):?>
											<span class="me-1 badge rounded-pill bg-success">
												<?= $waitingDemandIn ?>
												<span class="visually-hidden">demandes d'incorporation en attente</span>
											</span>
										<?php endif;?>
										<?php if($waitingDemandOut>0):?>
											<span class="badge rounded-pill bg-danger">
												<?= $waitingDemandOut ?>
												<span class="visually-hidden">demandes de démission en attente</span>
											</span>
										<?php endif;?>
									</a>
								</li>
								<li class="list-group-item list-group-item-action ps-0">
									<a class='btn btn-outline-primary w-100 text-start' href='diplo_compagnie.php?id_compagnie=<?= $company->id_compagnie?>'>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
										</svg>
										Diplomatie
									</a>
								</li>
							<?php
									break;
								case 3 :
							?>
								<li class="list-group-item list-group-item-action ps-0">
									<a class="btn btn-outline-primary w-100 text-start" href="bank.php?id=<?= $company->id_bank?>&action=treasury">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" stroke-linecap="round" class="w-6 h-6">
											<rect x="2.6651" y="2.2536" width="18.67" height="19.493" rx="0" ry=".94274"/>
											<rect x="5.0463" y="4.7397" width="13.907" height="14.521" rx="0" ry=".70226"/>
											<path d="m8.5254 7.4453c-0.27551 0-0.55061 0.10725-0.76172 0.31836-0.42221 0.42221-0.42221 1.1012 0 1.5234l1.7656 1.7656a2.6517 2.6517 0 0 0-0.18164 0.94727 2.6517 2.6517 0 0 0 0.18164 0.94727l-1.7656 1.7656c-0.42221 0.42221-0.42221 1.1012 0 1.5234s1.1012 0.42221 1.5234 0l1.7656-1.7656a2.6517 2.6517 0 0 0 0.94727 0.18164 2.6517 2.6517 0 0 0 0.95117-0.17774l1.7617 1.7617c0.42221 0.42221 1.1012 0.42221 1.5234 0s0.42221-1.1012 0-1.5234l-1.7617-1.7617a2.6517 2.6517 0 0 0 0.17774-0.95117 2.6517 2.6517 0 0 0-0.17774-0.95117l1.7617-1.7617c0.42221-0.42221 0.42221-1.1012 0-1.5234-0.21111-0.21111-0.48621-0.31836-0.76172-0.31836-0.2755 0-0.55061 0.10725-0.76172 0.31836l-1.7617 1.7617a2.6517 2.6517 0 0 0-0.95117-0.17773 2.6517 2.6517 0 0 0-0.94727 0.18164l-1.7656-1.7656c-0.21111-0.21111-0.48621-0.31836-0.76172-0.31836z"/>
											<circle cx="12" cy="12" r="2.6517"/>
											<circle cx="12" cy="12" r=".76767" stroke-width=".5"/>										
										</svg>
										Trésorerie
										<?php if($waitingLoans->loans>0):?>
											<span class="ms-3 badge rounded-pill bg-warning text-black">
												<?= $waitingLoans->loans ?>
												<span class="visually-hidden">emprunts en attente</span>
											</span>
										<?php endif;?>
									</a>
								</li>
							<?php
									break;
								case 4 :
							?>
								<li class="list-group-item list-group-item-action ps-0">
									<a class="btn btn-outline-primary w-100 text-start" href="recrut_compagnie.php?id_compagnie=<?= $company->id_compagnie?>">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
										</svg>
										<span class='me-2'>Recrutement</span>
										<?php if($waitingDemandIn>0):?>
											<span class="me-1 badge rounded-pill bg-success">
												<?= $waitingDemandIn ?>
												<span class="visually-hidden">demandes d'incorporation en attente</span>
											</span>
										<?php endif;?>
										<?php if($waitingDemandOut>0):?>
											<span class="badge rounded-pill bg-danger">
												<?= $waitingDemandOut ?>
												<span class="visually-hidden">demandes de démission en attente</span>
											</span>
										<?php endif;?>
									</a>
								</li>
							<?php
									break;
								case 5 :
							?>
								<li class="list-group-item list-group-item-action ps-0">
									<a class='btn btn-outline-primary w-100 text-start' href='diplo_compagnie.php?id_compagnie=<?= $company->id_compagnie?>'>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
										</svg>
										Diplomatie
									</a>
								</li>
							<?php
									break;
								endswitch;
							?>
							</ul>
							<?php endif; ?>
						<?php else:?>
							<?php if(isset($character->id_compagnie)): ?>
							<small>
								Si vous souhaitez rejoindre cette compagnie vous devez quitter votre compagnie actuelle.<br>
								<a class="btn btn-sm btn-primary mt-3" href="?action=show&id=<?=$character->id_compagnie?>">Ma compagnie</a>
							</small>
							<?php else:?>
							<p>
								Intéressé ?<br>
								n'hésitez pas à faire une demande d'intégration.<br>
								<button type="button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#joinComp<?= $company->id_compagnie?>">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
										  <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
										</svg>
										Rejoindre 
								</button>
							</p>
							<!-- Modal de recrutement -->
							<div class="modal fade" id="joinComp<?= $company->id_compagnie?>" tabindex="-1" aria-labelledby="joinCompLabel<?= $company->id_compagnie?>" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered">
									<div class="modal-content">
										<div class="modal-header text-start">
											<h1 class="modal-title fs-5" id="joinCompLabel<?= $company->id_compagnie?>">Rejoindre la compagnie<br>"<?= $company->nom_compagnie?>"</h1>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<div class="modal-body">
											<p>Vous êtes sur le point de rejoindre la compagnie "<?= $company->nom_compagnie?>".</p>
											<p>
												résumé :<br>
												<span class='fst-italic'><?= $company->resume_compagnie?></span>
											</p>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
											<form method="post" name="join_comp" action="?action=join&id=<?= $company->id_compagnie?>">
												<input type="hidden" name="compId" value="<?= $company->id_compagnie?>">
												<button type="submit" class="btn btn-success">Rejoindre</button>
											</form>
										</div>
									</div>
								</div>
							</div>
							<?php endif;?>
						<?php endif;?>
					</div>
					<div class='col-12 col-sm lh-lg'>
						<span class='fw-semibold'>Chef de compagnie :</span> <?=$companyLeader?><br>
						<span class='fw-semibold'>Sous-chef :</span> <?=$companySecond?><br>
						<span class='fw-semibold'>Trésorier :</span> <?=$companytreasurer?><br>
						<span class='fw-semibold'>Recruteur :</span> <?=$companyRecruiter?><br>
						<span class='fw-semibold'>Diplomate :</span> <?=$companyDiplomat?>
					</div>
				</div>
				<?php if($character->id_compagnie==$company->id_compagnie): ?>
					<?php if($character->attenteValidation_compagnie==0): ?>
						<?php if($character->role_level==1 AND count($companyMembers)>2): ?>
							<button type="button" class="btn btn-sm btn-outline-danger mt-3" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
							data-bs-title="Vous ne pouvez pas quitter la compagnie car vous êtes chef et que vous n'êtes pas le dernier membre.
								Si vous souhaitez quitter la compagnie, vous devez désigner un autre chef.">
								Quitter la compagnie
							</button>
						<?php elseif($debts->montant_emprunt>0):?>
							<button type="button" class="btn btn-sm btn-outline-danger mt-3" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
							data-bs-title="Vous ne pouvez pas quitter la compagnie car vous avec un emprunt en cours. Réglez vos dettes avant de partir.">
								Quitter la compagnie
							</button>
						<?php else:?>
						<button type="button" class="btn btn-sm btn-outline-danger mt-3" data-bs-toggle="modal" data-bs-demandtype="quit" data-bs-target="#quitCompModal">
							Quitter la compagnie
						</button>
						<?php endif;?>
					<?php elseif($character->attenteValidation_compagnie==2):?>
							<p class='alert alert-info'>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon-lg mt-1 me-2 float-start">
								  <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
								</svg>
								Vous avez demandé à quitter la compagnie.<br>
								Le chef ou le recruteur doivent valider ce départ. N'hésitez pas à les contacter.
							</p>
					<?php endif;?>
				<?php endif;?>
			</div>
			<!-- Modal de démission -->
			<div class="modal fade" id="quitCompModal" tabindex="-1" aria-labelledby="quitCompLabel<?= $company->id_compagnie?>" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header text-start">
							<h1 class="modal-title fs-5" id="quitCompLabel<?= $company->id_compagnie?>">
								<span class='customModalTitle'>Quitter</span> la compagnie<br>
								<span class='fst-italic'>"<?= $company->nom_compagnie?>"</span>
							</h1>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<p>
								Vous êtes sur le point <span class='customModalContent'>de quitter</span> la compagnie <span class='fst-italic'>"<?= $company->nom_compagnie?>"</span>.
							</p>
							<?php if(count($companyMembers)<2): ?>
							<p class='fw-semibold'>
								Attention. Vous êtes le dernier membre de la compagnie. Si vous quittez la compagnie, celle-ci sera supprimée.<br>
								Êtes vous certain ?
							</p>
							<?php else:?>
							<p class='customModalDetails'>
								Votre départ devra être validé par un responsable de la compagnie, sauf si vous n'étiez pas encore intégré.
							</p>
							<p>
								Si vous partez, vous devrez faire une nouvelle demande pour réintégrer la compagnie.
								Êtes vous certain ?
							</p>
							<?php endif;?>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
							<form method="post" name="quit_comp" action="?action=quit&id=<?= $company->id_compagnie?>">
								<input type="hidden" name="compId" value="<?= $company->id_compagnie?>">
								<input type="hidden" name="memberID" value="<?= $character->id_perso?>">
								<button type="submit" class="btn btn-danger">
									<span class='customModalBtn'>Quitter</span>
								</button>
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