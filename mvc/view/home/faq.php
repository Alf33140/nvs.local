<?php
$title = "FAQ";

/* ---Content--- */
ob_start();
?>
<div class="row justify-content-center">
	<div class="col-12 bg-light bg-opacity-75 p-4">
		<h2>FAQ</h2>
		<div class="accordion" id="accordionFAQ">
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
						Qu'est ce que Nord VS Sud ?
					</button>
				</h2>
				<div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						<strong>Nord VS Sud est un jeu de stratégie au tour par tour</strong> sur le thème de la guerre de Sécession américaine.<br>
						Vous dirigez un bataillon composé d'un gradé ainsi que ses grouillots, qu'il sera possible de personnaliser grâce à votre expérience.<br>
						Le but du jeu est d'être le premier camp à gagner 1000 points de victoire en détruisant des infrastructures ennemies afin de remporter la bataille qui se déroule sur une carte.
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
						A quoi correspondent les PM et PA ?
					</button>
				</h2>
				<div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						<ul>
							<li>
								<strong>Les PM sont les Points de Mouvement de vos personnages.</strong> Vous pouvez les utiliser pour vous déplacer sur la carte de jeu. Attention, chaque terrain possède un coût de déplacement propre, voir <a href='../regles/regles_carte.php'>ce tableau</a>.
							</li>
							<li>
								<strong>Les PA sont les Points d'Action de vos personnages.</strong> Vous pouvez les utiliser afin d'effectuer différentes actions, chaque action a un coût qui lui est propre.
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
						Je n'ai plus de PM et/ou de PA. Que faire ?
					</button>
				</h2>
				<div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						<strong>A chaque nouveau tour, votre personnage regagne ses PA et PM.</strong> Un tour dure 46h.<br>
						Si vous avez consommé tous vos PM et/ou PA lors de votre tour de jeu, vous avez la possibilité de participer à la vie de votre camp, discuter et mettre en place des stratégies, construire du Rôle Play (etc.) sur l'ensemble des supports dédiés comme le forum ou les autres moyens de communication disponibles (discord, messagerie du jeu, etc.)
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
						Je pars en vacances et je ne pourrais pas jouer. Que faire ?
					</button>
				</h2>
				<div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						Vous avez 2 possibilités :
						<ul>
							<li>
								<strong>Mettre vos personnages en permission</strong> (Profil -> Gérer son compte -> Partir en permission).<br>
								Vos personnages disparaitront du jeu jusqu'à ce que vous reveniez. Attention, un départ en permission ne s'active qu'au bout de 3 jours (prise en compte à minuit).	Pensez donc à faire votre demande en avance (il vous sera possible de continuer à jouer jusqu'à votre départ)!<br>
								A noter que vous ne pouvez revenir de permission que minimum 5 jours après le départ effectif.
							</li>
							<li>
								<strong>Mettre votre perso en "babysitting" par un autre joueur.</strong><br>
								L'autre joueur pourra activer votre tour et jouer votre perso pendant votre absence. Pour cela, vous devez déclarer le babysitting (Profil -> Gérer son compte -> Déclarer un babysitting).
								Le Babysitting ne peut se faire que par un joueur du même camp que vous et les interactions entre les persos des 2 joueurs sont interdites durant toute la période de babysitting.<br>
								Attention, cette option est à vos risques et périls. Si vous perdez vos objets/thunes et/ou que votre perso se fait capturer pendant cette période, ce sera de votre responsabilité.
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
						Comment se passe le retour de permission ?
					</button>
				</h2>
				<div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">	
						Après votre départ effectif de permission, <strong>vous devez attendre au minimum 5 jours pour revenir.</strong>
						A votre retour de permission, votre personnage est envoyé au bâtiment disponible le plus proche de votre position de départ.
						Les bâtiments disponibles pour un retour de permission sont les Forts, les Fortins et les Gares, qui ne sont pas en état de siège et dont la capacité maximale n'est pas atteinte.
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
						Nous sommes plusieurs à jouer régulièrement du même lieu (maison, école, entreprise, etc.), est-ce autorisé ?
					</button>
				</h2>
				<div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						<strong>Oui, mais vous devez le déclarer</strong> (Profil -> Gérer son compte -> Déclarer un multi) et respecter certaines règles :
						<ul>
							<li>Jouer dans le même camp</li>
							<li>Pas d'interaction entre vos persos (pas d'échange de thunes / objets, pas de bousculades, pas de soins)</li>
							<li>Pas d'attaques / interactions contre la même cible sous un délai de 8h (Exemple : si Joueur 1 a attaqué Ennemi A à 14h, Joueur 2 qui est multi de Joueur 1 n'a le droit d'attaquer / bousculer Ennemi A qu'à partir de 22h)</li>
						</ul>
						Tout manquement à ces règles sera sanctionné (en jeu) par des peines plus ou moins grandes (de la simple amende à l'envoi au Pénitencier).<br>
						<strong>Attention, si un multi est détecté sans déclaration ou qu'un multi déclaré se trouve être joué par une seule personne, les persos seront purement et simplement supprimés.</strong>
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
						Comment avoir un aperçu global/stratégique de la situation ?
					</button>
				</h2>
				<div id="collapseSeven" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						<strong>Il existe une carte stratégique accessible depuis l'onglet "CARTE".</strong><br>
						En cliquant dessus, vous ouvrirez la carte stratégique, vous permettant d'avoir une vision rapide de la situation actuelle du jeu.<br>
						La carte possède des zones découvertes (zones déjà explorées par un perso de votre camp) et des zones non découvertes. Il vous est impossible de voir les mouvements ennemis sur les zones non découvertes, à vous donc de faire en sorte de découvrir rapidement le plus de zones !
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
						Je vois un ennemi en forêt à côté de moi mais il est invisible sur la carte stratégique, pourquoi ?
					</button>
				</h2>
				<div id="collapseEight" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						<strong>Les zones de forêt sont dites "à couvert"</strong>, elles permettent aux personnages de rester invisibles de la carte stratégique.<br>
						Cela permet de favoriser les infiltrations ou embuscades et d'ajouter un peu plus de piquant au jeu.
						A vous de prendre en compte les terrains de la carte afin d'identifier les zones à surveiller ou les zones à exploiter.
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
						En entrant dans une forêt ou en grimpant une colline ma perception change, pourquoi ?
					</button>
				</h2>
				<div id="collapseNine" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						<strong>Chaque type de terrain possède ses spécificités</strong> et accorde divers bonus et/ou malus.<br>
						Reportez vous à la <a href='./regles/regles_carte.php'>page de règles dédiées</a> pour tous les connaitre.
					</div>
				</div>
			</div>
			<h2 class='mt-3'>TRAINS ET RAILS</h2>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTrainOne" aria-expanded="true" aria-controls="collapseTrainOne">
						A quoi sert le train et comment le prendre ?
					</button>
				</h2>
				<div id="collapseTrainOne" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						<strong>Le train permet de se rendre d'une gare à une autre.</strong><br>
						Pour prendre le train, vous devez entrer dans la gare et acheter un ticket vers la gare de destination (Coût 5 Thunes par trajet).<br>
						Il ne vous reste plus qu'à attendre le train qui vous embarquera automatiquement dès qu'il sera arrivé en gare et vous débarquera automatiquement dans la gare de destination.<br>
						Si le voyage demande de passer par plusieurs gares, vous serez débarqué dans chaque gare intermédiaire avant d'embarquer automatiquement dans le train suivant jusqu'à votre destination.<br>
						<span class='fw-bold'>Attention : les tickets de train sont nominatifs et ne peuvent pas être cédés à un autre perso, ils ne fonctionneront pas !</span>
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTrainTwo" aria-expanded="false" aria-controls="collapseTrainTwo">
						Mon train semble ne plus avancer, que se passe t-il ?
					</button>
				</h2>
				<div id="collapseTrainTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">
						Un train peut être bloqué pour plusieurs raisons :
						<ul>
							<li>
								<strong>La gare de destination n'existe plus/a été détruite.</strong>
							</li>
							<li>
								<strong>La gare de destination n'est plus en état de fonctionner (PV en dessous de 50%)</strong>
							</li>
							<li>
								<strong>Une barricade a été placée sur la route du train (le train s'arrêtera devant la barricade)</strong>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTrainThree" aria-expanded="false" aria-controls="collapseTrainThree">
						Je souhaite détruire/saboter un rail, comment faire ?
					</button>
				</h2>
				<div id="collapseTrainThree" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
					<div class="accordion-body">	
						<strong>Un rail ne peut être détruit que par les unités du génie.</strong><br>
						Si vous faites partie du génie, il suffit de vous positionner sur le rail que vous souhaitez détruire et l'option apparaitra dans la liste des actions et sur le popup du perso. L'action coûte 10PA et est immédiate.
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/guest.php'); ?>
