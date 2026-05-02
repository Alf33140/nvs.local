<?php
require_once("Model.php");

class Company extends Model
{
	protected $table = "compagnies";
	protected $primaryKey = "id_compagnie";
	// protected $fillable = [];
	protected $guarded = [];
	
	
	// ------- fonctions pour tables pivots ------- //
	
	/**
     * Helper pour le nombre de membres dans une compagnie
     * @param $id int company id
     * @return int
     */
	public function countMembers($id){
		$query = "SELECT COUNT(*) as countMembers FROM perso_in_compagnie WHERE id_compagnie=? AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
		$request = $this->request($query,[$id]);
		
		$countMembers = $request->fetch(PDO::FETCH_ASSOC);
		return $countMembers['countMembers'];
	}
	
	/**
     * Helper pour le type d'unité autorisé dans la compagnie
     * @param $company_id int company id
     * @param $unity_id int unity id
     * @return bool
     */
	public function checkUnit($company_id, $unity_id){
		
		$query = "SELECT COUNT(*) as checkType FROM compagnie_as_contraintes WHERE id_compagnie=? AND contrainte_type_perso=?";
		$request = $this->request($query,[$company_id,$unity_id]);
		
		$count = $request->fetch(PDO::FETCH_ASSOC);

		if($count['checkType']>0){
			return true;
		}else{
			return false;
		}
	}
	
	/**
     * Pivot table "compagnie_as_contraintes" to allow units to integrate a company
     * @param $type array
     * @return bool
     */
	public function allowUnits(array $types){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_compagnie';
		
		$pivotTable = 'compagnie_as_contraintes';
		$requests = 0;
		
		if(isset($this->allowed_units)){
			$count1 = count(array_diff($types,$this->allowed_units));
			$count2 = count(array_diff($this->allowed_units,$types));
			$control = $count1+$count2;
			
			foreach($types as $type){
				if(!in_array($type,$this->allowed_units)){
					$query = 'INSERT INTO '.$pivotTable.' ('.$firstTableKeyInPivot.',contrainte_type_perso) VALUES (?,?)';
					$values = [$this->$firstTableKey,$type];

					$request = $this->request($query,$values);
					$requests ++;
				}
			}
			foreach($this->allowed_units as $unit){
				if(!in_array($unit,$types)){
					$query = 'DELETE FROM '.$pivotTable.' WHERE '.$firstTableKeyInPivot.'=? AND contrainte_type_perso=?';
					$values = [$this->$firstTableKey,$unit];

					$request = $this->request($query,$values);
					$requests ++;
				}
			}
			
			return $control==$requests;
		}else{
			foreach($types as $type){
				$query = 'INSERT INTO '.$pivotTable.' ('.$firstTableKeyInPivot.',contrainte_type_perso) VALUES (?,?)';
				$values = [$this->$firstTableKey,$type];

				$request = $this->request($query,$values);
				$requests ++;
			}
			
			return count($types)==$requests;
		}
	}
	
	/**
   * fonction pivot pour savoir si un perso appartient à une compagnie et s'il en est le chef
   * @var charac_id (int) id du perso
   * @return array
   */
	public function inCompany(int $id) {
		
		$query = "SELECT id, perso_in_compagnie.id_perso, perso_in_compagnie.id_compagnie, perso_in_compagnie.poste_compagnie, poste.slug FROM perso_in_compagnie LEFT JOIN poste ON poste.id_poste=perso_in_compagnie.poste_compagnie WHERE perso_in_compagnie.id_perso=?";
		$values = [$id];

		$request = $this->request($query,$values);
		$result = $request->fetch(PDO::FETCH_ASSOC);

		return $result;
	}
	
	/**
     * Pivot table "perso_in_compagnie" to assign characters to integrate a company
     * @param $type array
     * @return bool
     */
	public function assignPerso($id_perso,$poste=0,$attenteValidation_compagnie=1){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_compagnie';
		
		$pivotTable = 'perso_in_compagnie';
		
		$query = 'INSERT INTO '.$pivotTable.' ('.$firstTableKeyInPivot.',id_perso,poste_compagnie,attenteValidation_compagnie) VALUES (?,?,?,?)';
		$values = [$this->$firstTableKey,$id_perso,$poste,$attenteValidation_compagnie];

		$request = $this->request($query,$values);
			
		return $request;
	}
	
	/**
     * Pivot table "perso_in_compagnie" to delete a character in a company
     * @param $id_perso int
     * @param $demand bool
     * @return bool
     */
	public function dismissPerso(int $id_perso,$demand=false){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_compagnie';
		
		$pivotTable = 'perso_in_compagnie';
		
		if($demand){
			$query = 'UPDATE '.$pivotTable.' SET attenteValidation_compagnie=2 WHERE '.$firstTableKeyInPivot.' = ? AND id_perso = ?';
		}else{
			$query = 'DELETE FROM '.$pivotTable.' WHERE '.$firstTableKeyInPivot.' = ? AND id_perso = ?';
		}
		
		$values = [$this->$firstTableKey,$id_perso];

		$request = $this->request($query,$values);
			
		return $request;
	}
	
	/**
     * Pivot table "perso_in_compagnie" to delete all persos in a company
     * @param $type array
     * @return bool
     */
	public function dismissAll(){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_compagnie';
		
		$pivotTable = 'perso_in_compagnie';
		
		$query = 'DELETE FROM '.$pivotTable.' WHERE '.$firstTableKeyInPivot.' = ?';
		$values = [$this->$firstTableKey];

		$request = $this->request($query,$values);
			
		return $request;
	}
	
	// ------- fonctions spéciales pour l'EM ------- //
	
	/**
     * Récupération de toutes les demandes de création de compagnie
     * 
     * @return list of obj
     */
	public function creationDemands(int $camp){
		
		$query = "SELECT em_creer_compagnie.id, em_creer_compagnie.id_perso, em_creer_compagnie.nom_compagnie, em_creer_compagnie.description_compagnie, perso.nom_perso, em_creer_compagnie.votes_result, em_creer_compagnie.soft_delete
				FROM em_creer_compagnie LEFT JOIN perso ON perso.id_perso=em_creer_compagnie.id_perso
				WHERE em_creer_compagnie.soft_delete IS NULL AND em_creer_compagnie.camp = ?";
		$request = $this->request($query,[$camp]);
		
		return $request->fetchAll(PDO::FETCH_CLASS);
	}
	
	/**
     * Récupération d'une demande de création de compagnie
     * @param $id
     * @return obj
     */
	public function demand(int $id){
		
		$query = "SELECT em_creer_compagnie.id, em_creer_compagnie.id_perso, em_creer_compagnie.nom_compagnie, em_creer_compagnie.description_compagnie, em_creer_compagnie.camp, perso.nom_perso, em_creer_compagnie.votes_result, em_creer_compagnie.soft_delete
				FROM em_creer_compagnie LEFT JOIN perso ON perso.id_perso=em_creer_compagnie.id_perso
				WHERE em_creer_compagnie.id = ?";
		$request = $this->request($query,[$id]);
		
		return $request->fetch(PDO::FETCH_OBJ);
	}
	
	/**
     * Traitement des votes pour la création d'une compagnie
     * @param $id_compagnie
	 * @param $id_perso
	 * @param $vote
     * @return bool
     */
	public function validationVote(int $id_compagnie,int $id_perso,int $vote){
		
		$query = "INSERT INTO em_vote_creer_compagnie (id_em_creer_compagnie, id_em_perso, vote) VALUES (?,?,?)";
		$request = $this->request($query,[$id_compagnie,$id_perso,$vote]);
		
		return $request;
	}
	
	/**
     * Réinitialisation des votes pour la création d'une compagnie
     * @param $id_compagnie
     * @return bool
     */
	public function resetVotes(int $id_compagnie){
		
		$query = "DELETE FROM em_vote_creer_compagnie WHERE id_em_creer_compagnie=?";
		$request = $this->request($query,[$id_compagnie]);
		
		return $request;
	}
	
	/**
     * Récupérer les votes de création d'une compagnie
     * @param $id_compagnie
     * @return list of obj
     */
	public function creationVotes(int $id_compagnie){
		
		$query = "SELECT * FROM em_vote_creer_compagnie WHERE id_em_creer_compagnie=?";
		$request = $this->request($query,[$id_compagnie]);
		
		return $request->fetchAll(PDO::FETCH_CLASS);
	}
	
	/**
     * Enregistrer le résultat définitif du vote de l'Etat Major pour la création d'une compagnie
     * @param $id_compagnie
     * @return bool
     */
	public function votesResult(int $id_compagnie,int $result){
		$query = "UPDATE em_creer_compagnie SET votes_result = ? WHERE em_creer_compagnie.id=?";
		$request = $this->request($query,[$result,$id_compagnie]);
		
		return $request;
	}
	
	/**
     * Effacer la compagnie de la liste affichée pour l'Etat Major. La demande n'est pas supprimée de la BDD
     * @param $id_compagnie
     * @return bool
     */
	public function deleteForCommand(int $id_compagnie){
		$query = "UPDATE em_creer_compagnie SET soft_delete = NOW() WHERE em_creer_compagnie.id=?";
		$request = $this->request($query,[$id_compagnie]);
		
		return $request;
	}
}