<?php
require_once("Model.php");

class Unit extends Model
{
	protected $table = "type_unite";
	protected $primaryKey = "id_unite";
	// protected $fillable = [];
	protected $guarded = [];
	
	/* fonction pour calculer le coût d'amélioration des caractéristiques d'un perso
	 * @var carac (string) caractéristique à améliorer
	 * @var actual_points (int) points actuels de la caractéristique
	 * @var base_unit (int) points de base de l'unité
	 * @return cost
	*/
	public function upgradeCosts(string $carac, int $actual_points, int $base_unit) {
	
		switch($carac){
			case "pv" :
				$base_cost = 1;
				$multiplicateur = 1;
				$diviseur = 10;
				break;
			case "pm" :
				$base_cost = 175;
				$multiplicateur = 53;
				break;
			case "pa" :
				$base_cost = 200;
				$multiplicateur = 60;
				break;
			case "perception" :
				$base_cost = 150;
				$multiplicateur = 45;
				break;
			case "recup" :
				$base_cost = 10;
				$multiplicateur = 3;
				$diviseur = 10;
				break;
		}
		
		$delta = $actual_points-$base_unit;
		if(!empty($diviseur)){
			$delta = floor(($actual_points-$base_unit)/$diviseur);
		}
		$cost = $base_cost+$delta*$multiplicateur;
		
		return $cost;
	}
}