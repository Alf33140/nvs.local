<?php
$title = "Présentation";

/* ---Content--- */
ob_start();
?>
<div class="row justify-content-center">
	<div class="col-12 bg-light bg-opacity-75 p-4">
		<h3 class='fs-5 mt-3 mb-4 text-center fw-semibold'>
			Nord VS Sud<br>
			un jeu de stratégie multijoueur au tour par tour, <span class='fw-normal'>sur navigateur.</span>
		</h3>
		<p>
			Nord Vs Sud se présente comme un plateau de jeu sur lequel vous faite évoluer vos pions.<br>
			Le but principal du jeu est de faire gagner votre camp (<span class='text-north fw-semibold'>Nord</span> ou <span class='text-south fw-semibold'>Sud</span>)
			en jouant de façon coordonnée avec les autres joueurs afin de monter des opérations pour capturer/détruire les bâtiments ennemis, infiltrer/explorer la carte, participer à la construction des infrastructures ou encore participer aux missions lancées par l'animation.
		</p>
		<p>
			Vous dirigez un chef de bataillon dans une amérique déchirée entre le Nord unioniste et le Sud confédéré.<br>
			Dans votre aventure, vous êtes épaulé par votre premier grouillot, fort utile pour vous lancer à corps perdu dans la bataille.
			<center><img class='img-fluid' src='../images/presentation/chef.png' /></center>
			<center><img class='img-fluid' src='../images/presentation/grouillot.png' /></center>
		</p>
		<p>
			Chaque personnage que vous controllez possède un certain nombre de <span class='fw-bold'>Points d'action (PA)</span> que vous pouvez dépenser pour différentes actions (attaque, construction, réparation, soin, achat, consommation d'objet, équipement d'arme, etc...) durant votre tour de jeu.<br>
			De la même façon, vos personnages possèdent un certain nombre de <span class='fw-bold'>Points de mouvement (PM)</span> vous permettant de vous déplacer sur la carte de jeu.<br>
			<span class='fw-bold'>Attention</span>, <a href='./regles/regles_carte.php'>chaque type de terrain consomme un nombre de PM différent</a>.
			<center><img class='img-fluid' src='../images/presentation/caracs_pa_pm.png' /></center>
		</p>
		<p>
			Vos PA et PM sont restaurés à chaque nouveau tour de jeu.<br>
			<span class='fw-bold'>Un tour de jeu dure 46h</span>. Pour activer votre nouveau tour, vous devrez vous reconnecter au jeu.<br>
			Vous pouvez consulter la date et heure d'activation de votre prochain tour en haut à gauche de la page principale de jeu :
			<center><img class='img-fluid' src='../images/presentation/tour.png' /></center>
		</p>
		<p>
			Vos unités évolueront sur une carte de jeu dont la vision est limitée par la perception de votre personnage.<br>
			Par exemple, ici un personnage avec 5 de perception en plaine :
			<center><img class='img-fluid' src='../images/presentation/carte_vision.png' /></center>
		</p>
		<p>
			Des bonus/malus de perception peuvent être donnés en fonction du terrain sur lequel vous vous trouvez.<br>
			Par exemple, vous avez un malus de 2 points de perception en forêt (mais un bonus de défense aux attaques à ditance de 20) :
			<center><img class='img-fluid' src='../images/presentation/carte_vision_malus_foret.png' /><img class='img-fluid' src='../images/presentation/caracs_perception_malus.png' /></center>
		</p>
		<p>
			En dehors des combats, l'exploration a un rôle important.<br>
			La minimap qui représente la carte de bataille se présente sous cette forme :
			<center><img class='img-fluid' src='../images/presentation/minimap.png' /></center><br />
		</p>
		<p>
			Certaines parties de la carte sont sous une forme de brouillard de guerre, ce sont des zones que votre camp n'a pas encore exploré.<br>
			Les personnages et infrastructures sont représentés en <span class='fw-semibold text-north'>bleu</span> pour le Nord et en <span span='fw-semibold text-south'>rouge</font>pour le Sud.<br>
			Les tracés gris clair entre certains bâtiments, représentent les rails entre les gares où circulent les trains.
			<center><img class='img-fluid' src='../images/presentation/train.png' /></center><br />
			Les trains permettent de voyager rapidement d'une gare à une autre afin de rejoindre certaines parties de la carte plus facilement et rapidement.
		</p>
	</div>
</div>
<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/guest.php'); ?>
