<?php
require_once("Model.php");

class Skill extends Model
{
	protected $table = "competence";
	protected $primaryKey = "id_competence";
	// protected $fillable = [];
	protected $guarded = [];
	
	/* fonction pivot pour ajouter une compétence à un perso
	 * @var charac_id (int) id du perso
	 * @var skill_id (int) id de l'arme
	 * @var points (int) 1 ou 0
	 * @return number of row created
	*/
	public function addSkill(int $charac_id, int $skill_id, int $points=1) {
	
		$query = 'INSERT INTO perso_as_competence (id_perso,id_competence,nb_points) VALUES (?,?,?)';
		$values = [$charac_id,$skill_id,$points];
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
}