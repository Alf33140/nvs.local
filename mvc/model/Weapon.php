<?php
require_once("Model.php");

class Weapon extends Model
{
	protected $table = "arme";
	protected $primaryKey = "id_arme";
	// protected $fillable = [];
	protected $guarded = [];
	
	/* fonction pivot pour ajouter une arme à un perso
	 * @var charac_id (int) id du perso
	 * @var weapon_id (int) id de l'arme
	 * @var est_portee (int) 1 ou 0
	 * @return number of row created
	*/
	public function addWeapon(int $charac_id, int $weapon_id, int $est_portee=0) {
	
		$query = 'INSERT INTO perso_as_arme (id_perso,id_arme,est_portee) VALUES (?,?,?)';
		$values = [$charac_id,$weapon_id,$est_portee];
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
}