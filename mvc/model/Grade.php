<?php
require_once("Model.php");

class Grade extends Model
{
	protected $table = "grades";
	protected $primaryKey = "id_grade";
	// protected $fillable = [];
	protected $guarded = [];
	
	// ancien code à contrôler (impact) et supprimer
	
	// private $id_grade;
	// private $nom_grade;
	// private $pc_grade;
	// private $point_armee_grade;
	
	// public function __set($name, $value) {}
	
	// public function __get($name){
		// return $this->$name;
	// }
	
	public function getGrade($id,$attributs = []){
		$db = $this->dbConnectPDO();
		
		if($attributs){
			$attributs = implode(', ',$attributs);
		}else{
			$attributs = "*";
		}
		
		$query = 'SELECT '.$attributs.' FROM grades WHERE id_grade=:id';
		
		$request = $db->prepare($query);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_CLASS,get_class($this));
		$result = $request->fetch();

		return $result;
	}
	
	// A terme supprimer la table PIVOT perso_as_grade inutile et intégrer une colonne grade_id dans la table Perso.
	// cette fonction sera donc obsolète
	public function getPersoGrade($id){
		$db = $this->dbConnectPDO();
		
		$query = 'SELECT perso_as_grade.id_grade, nom_grade FROM perso_as_grade, grades WHERE perso_as_grade.id_grade = grades.id_grade AND id_perso=:id';
		
		$request = $db->prepare($query);
		$request->bindParam('id', $id, PDO::PARAM_INT);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_CLASS,get_class($this));
		$result = $request->fetch();

		return $result;
	}
	
	// A terme supprimer la table PIVOT perso_as_grade inutile et intégrer une colonne grade_id dans la table Perso.
	// cette fonction sera donc obsolète
	public function addGrade(int $charac_id, int $grade_id) {
	
		$query = 'INSERT INTO perso_as_grade (id_perso,id_grade) VALUES (?,?)';
		$values = [$charac_id,$grade_id];
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
}