<?php
$title = "Classements";

/* ---Content--- */
ob_start();
?>
<div class="row justify-content-center bg-light bg-opacity-75">
	<div class="col-12 p-4">
		<h3>classements généraux</h3>
		<nav>
		</nav>
	</div>
	<div class='col-12'>
		<div class='row'>
			<div class='col-12'>
				<h4>Tops 5</h4>
			</div>
		</div>
		<div class="row row-cols-1 row-cols-sm-2 row-cols-md-4">
			<div class="col">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Derniers tombés</h5>
						<?php if(isset($lastKills) AND !empty($lastKills)): ?>
						<ul class="list-group list-group-flush">
							<?php foreach($lastKills as $kill): ?>
							<li class="list-group-item">
								<span class='fw-semibold'><?= $kill->killed_name ?> [<?= $kill->id_perso_capture ?>]</span><br>
								<?php $killDate = new DateTimeImmutable($kill->date_capture)?>
								<small>capturé par <?= $kill->killer_name ?> [<?= $kill->id_perso_captureur ?>] <?= $killDate->format('\l\e d-m-Y à H:i:s');?></small>
							</li>
							<?php endforeach; ?>
						</ul>
						<?php else: ?>
						<p>
							Fort heureusement, il semblerait que personne ne soit tombé au combat.
						</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Hauts gradés</h5>
						<?php if(isset($bestRanks) AND !empty($bestRanks)): ?>
						<ul class="list-group list-group-flush">
							<?php
								$i = 1;
								foreach($bestRanks as $rank):
								switch($i){
									case 1:
										$classColor = 'gold-medal';
										break;
									case 2:
										$classColor = 'silver-medal';
										break;
									case 3:
										$classColor = 'bronze-medal';
										break;
									default:
										$classColor = '';
								}
							?>
							<li class="list-group-item<?= ' '.$classColor ?>">
								#<?=$i?>. <span class='fw-semibold'><?= $rank->nom_perso ?> [<?= $rank->id_perso ?>]</span><br>
								<img src="../public/img/ranks/<?= $rank->image_grade ?>" alt="médaille" width='20px' height='20px'> <?= $rank->nom_grade ?>
							</li>
							<?php
								$i++;
								endforeach;
							?>
						</ul>
						<?php else: ?>
						<p>
							Quand les gradés ne sont pas là, les grouillots dansent.
						</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Machines à tuer</h5>
						<?php if(isset($bestKillers) AND !empty($bestKillers)): ?>
						<ul class="list-group list-group-flush">
							<?php
								$i = 1;
								foreach($bestKillers as $killer):
								switch($i){
									case 1:
										$classColor = 'gold-medal';
										break;
									case 2:
										$classColor = 'silver-medal';
										break;
									case 3:
										$classColor = 'bronze-medal';
										break;
									default:
										$classColor = '';
								}
							?>
							<li class="list-group-item<?= ' '.$classColor ?>">
								#<?=$i?>. <span class='fw-semibold'><?= $killer->nom_perso ?> [<?= $killer->id_perso ?>]</span><br>
								avec <?= $killer->nb_kill ?> capture(s)
							</li>
							<?php
								$i++;
								endforeach;
							?>
						</ul>
						<?php else: ?>
						<p>
							Fort heureusement, il semblerait que personne ne soit tombé au combat.
						</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Chasseurs</h5>
						<?php if(isset($bestHunters) AND !empty($bestHunters)): ?>
						<ul class="list-group list-group-flush">
							<?php 
								$i = 1;
								foreach($bestHunters as $hunter):
								switch($i){
									case 1:
										$classColor = 'gold-medal';
										break;
									case 2:
										$classColor = 'silver-medal';
										break;
									case 3:
										$classColor = 'bronze-medal';
										break;
									default:
										$classColor = '';
								}
							?>
							<li class="list-group-item<?= ' '.$classColor ?>">
								#<?=$i?>. <span class='fw-semibold'><?= $hunter->nom_perso ?> [<?= $hunter->id_perso ?>]</span><br>
								avec <?= $hunter->nb_pnj ?> capture(s)
							</li>
							<?php
								$i++;
								endforeach;
							?>
						</ul>
						<?php else: ?>
						<p>
							Les animaux vivent paisiblement dans la nature. L'humain serait-il en symbiose ?
						</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row justify-content-center bg-light bg-opacity-75">
	<div class="col-12 mt-3">
		<h4>Statistiques par camp</h4>
		<?php if(isset($activeCharacters) AND !empty($activeCharacters)): ?>
		<table class="table table-striped table-hover">
			<caption>Statistiques par camp</caption>
			<thead class='table-light'>
				<tr>
					<th scope="col">Camp</th>
					<th scope="col">Joueurs actifs</th>
					<th scope="col">Persos actifs</th>
					<th scope="col">ennemis capturés</th>
					<th scope="col">alliés capturés</th>
					<th scope="col">Points de victoire</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($activeCharacters as $camp):
					switch($camp->clan){
						case 1 :
							$colorClass = 'text-north';
							$alliesKilled = $northAlliesKilled;
							$enemiesKilled = $northEnemiesKilled;
							$VictoryPts = $totalVPNorth;
							break;
						case 2 ;
							$colorClass = 'text-south';
							$alliesKilled = $southAlliesKilled;
							$enemiesKilled = $southEnemiesKilled;
							$VictoryPts = $totalVPSouth;
							break;
						default :
							$colorClass = '';
							$alliesKilled = 0;
							$enemiesKilled = 0;
							$VictoryPts = 0;
					}
				?>
				<tr>
					<th scope="row">
						<span  class='<?= $colorClass ?>'><?= $camp->name ?></span>
					</th>
					<td>
						<?= $camp->activeChefs ?>
					</td>
					<td>
						<?= $camp->activeCharacters ?>
					</td>
					<td>
						<?= $enemiesKilled??0 ?>
					</td>
					<td>
						<?= $alliesKilled??0 ?>
					</td>
					<td>
						<?= $VictoryPts?>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
		<?php else: ?>
		<p>
			Aucune statistique disponible.
		</p>
		<?php endif; ?>
	</div>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/guest.php'); ?>
