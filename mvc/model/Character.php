<?php
require_once("Model.php");

// classe créée en remplacement de la classe "perso". La classe "perso" est maintenue pour la retro compatibilité. A supprimer à terme
class Character extends Model
{
	protected $table = "perso";
	protected $primaryKey = "id_perso";
	// protected $fillable = [];
	protected $guarded = [];
	
		
	/**
   * recherche les infos d'un perso
   * @return class of $this
   */
	public function searchCharacters($data,$self=0) {
		
		$query = "select id_perso, idJoueur_perso, nom_perso FROM perso WHERE id_perso LIKE CONCAT(?,'%') AND chef=1 AND est_pnj=0 AND idJoueur_perso<>? LIMIT 10";
		$values = [$data,$self];

		$request = $this->request($query,$values);
		$result = $request->fetchAll(PDO::FETCH_CLASS, 'Character');

		return $result;
	}
	
	/**
   * fonction pivot pour savoir si un perso est dans un bâtiment
   * @var charac_id (int) id du perso
   * @return array
   */
	public function inBuilding(int $id) {
		
		$query = "SELECT id, id_perso, id_instanceBat FROM perso_in_batiment WHERE id_perso=?";
		$values = [$id];

		$request = $this->request($query,$values);
		$result = $request->fetch(PDO::FETCH_ASSOC);

		return $result;
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
   * fonction pivot pour récupérer les respawns d'un perso
   * @var charac_id (int) id du perso
   * @return an array
   */
	public function respawns(int $id) {
		
		$query = "SELECT id, id_instance_bat, id_bat FROM perso_as_respawn WHERE id_perso=?";
		$values = [$id];

		$request = $this->request($query,$values);
		$result = $request->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	}
		
	/**
   * fonction pivot pour sauvegarder un respawn d'un perso
   * @var building_id (int) id de l'instance du bâtiment
   * @var type_id (int) id du type de bâtiment
   * @var charac_id (int) id du perso
   * @return an array
   */
	public function saveRespawn(int $building_id, int $type_id, int $charac_id) {
		
		$query = 'INSERT INTO perso_as_respawn (id_instance_bat,id_bat,id_perso) VALUES (?,?,?)';
		$values = [$building_id,$type_id,$charac_id];
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/**
   * fonction pivot pour actualiser un respawn d'un perso
   * @var oldRespawn (int) id de l'ancien respawn
   * @var newRespawn (int) id du nouveau respawn
   * @var charac_id (int) id du perso
   * @return an array
   */
	public function updateRespawn(int $oldRespawn, int $newRespawn, int $charac_id) {
		

		$query = "UPDATE perso_as_respawn SET id_instance_bat=? WHERE id_instance_bat=? AND id_perso=?";
		$values = [$newRespawn,$oldRespawn,$charac_id];
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/**
   * fonction pivot pour supprimer un respawn d'un perso
   * @var building_id (int) id de l'instance du bâtiment
   * @var charac_id (int) id du perso
   * @return an array
   */
	public function removeRespawn(int $building_id, int $charac_id) {
		
		$query = "DELETE FROM perso_as_respawn WHERE id_instance_bat=? AND id_perso=?";
		$values = [$building_id,$charac_id];

		$request = $this->request($query,$values);
		$result = $request->rowCount();
		
		return $result;
	}
	
	/* fonction pivot pour supprimer un ou plusieurs persos de la carte
	 * @var charac_ids (array) ids des persos
	 * @return number of row deleted
	*/
	public function removeCharacFromMap(array $charac_ids){
		$binds = [];
		$values = [];
		
		foreach($charac_ids as $id){
			$binds[] = '?';
			$values[] = $id;
		}
		
		$binds = implode(', ',$binds);
		$query = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte IN (".$binds.")";
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/* fonction pivot pour désactiver un perso (suppression bât, véhicules et carte + statut "est renvoyé")
	 * @var charac_ids (array) ids des persos
	 * @return number of row deleted
	*/
	public function desactivateCharacters(array $charac_ids,bool $freeJail=false){
		
		// On supprime le perso des bâtiments/véhicules/carte
		// On déplace le perso hors carte
		// On active l'option "est_renvoyé" dans la table perso
		
		$query = 'SELECT instance_batiment.id_instanceBat FROM instance_batiment LEFT JOIN batiment ON instance_batiment.id_batiment=batiment.id_batiment WHERE batiment.nom_batiment="Pénitencier"';
		
		$request = $this->request($query);
		$jails = $request->fetchAll();
		
		$jailsBinds = [];
		$binds = [];
		$values = [];
		$NotInJail = '';
		
		foreach($charac_ids as $id){
			$binds[] = '?';
			$values[] = $id;
		}
		
		$binds = implode(', ',$binds);
		
		$query = 'DELETE FROM perso_in_train WHERE id_perso IN ('.$binds.')';
		$request = $this->request($query,$values);
		$deleteFromVehicule = $request->rowCount();
		
		$query = 'UPDATE carte SET occupee_carte="0", idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte IN ('.$binds.')';
		$request = $this->request($query,$values);
		$deleteFromMap = $request->rowCount();
		
		if(!empty($jails)){
			foreach($jails as $jail){
				$values[] = $jail['id_instanceBat'];
				$jailsBinds[] = '?';
			}
			$jailsBinds = implode(', ',$jailsBinds);
			
			if(!$freeJail){
				$NotInJail = ' AND id_instanceBat NOT IN ('.$jailsBinds.')';
			}
		}
		
		$query = 'DELETE FROM perso_in_batiment WHERE id_perso IN ('.$binds.')'.$NotInJail.'';
		$request = $this->request($query,$values);
		$deleteFromBuilding = $request->rowCount();		
		
		$now = new DateTime();
		$countResults = 0;
		
		foreach($charac_ids as $id){
			$this->id_perso = $id;
			$this->x_perso = -1000;
			$this->x_perso = -1000;
			$this->y_perso = -1000;
			$this->est_renvoye = 1;
			$this->date_renvoi = $now->format('Y-m-d H:i:s');
			$result = $this->update();
			
			if($result){
				$countResults++;
			}
		}
		
		if($countResults==count($charac_ids)){
			return true;
		}

		return false;
	}
	
	/**
	 * Normalise le nom d'un perso pour comparaison
	 * @param string $name Nom à normaliser
	 */
	function normalizeName($name)
	{
		// Enlever accents
		$name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
		// Minuscules
		$name = strtolower($name);
		// Supprimer espaces multiples
		$name = preg_replace('/\s+/', ' ', $name);
		return trim($name);
	}

	/**
	 * Vérifie si un nom est trop proche ou identique à un autre joueur
	 * @param string $newName Nom à vérifier
	 * @param int $distanceMax Distance Levenshtein maximale (strict = 3)
	 * @param float $similarMin Similarité minimale (%) (strict = 80)
	 * @return array ['match'=>bool, 'exact'=>bool, 'conflict'=>string|null, 'distance'=>int|null, 'similarity'=>float|null]
	 */
	function isNameTooClose(string $newName, int $distanceMax = 3, float $similarMin = 80)
	{
		$normName = $this->normalizeName($newName);
		$len = strlen($normName);

		// On vérifie si le nom est le même qu'un nom existant
		$query = 'SELECT nom_perso FROM perso WHERE normalized_name = ? LIMIT 1';
		$values = [$normName];
		$firstResult = $this->request($query,$values);

		if ($row = $firstResult->fetch(PDO::FETCH_ASSOC)) {
			return [
				'match' => true,
				'exact' => true,
				'conflict' => $row['nom_perso'],
				'distance' => 0,
				'similarity' => 100
			];
		}

		// Préfiltrage par longueur de nom pour améliorer les performances
		$minLen = max(1, $len - $distanceMax - 1);
		$maxLen = $len + $distanceMax + 1;

		$query = 'SELECT nom_perso, normalized_name FROM perso WHERE CHAR_LENGTH(normalized_name) BETWEEN ? AND ?';
		$values = [$minLen,$maxLen];
		$candidates = $this->request($query,$values);

		$candidates = $candidates->fetchAll(PDO::FETCH_ASSOC);

		// Comparaison proche avec les méthodes levenshtein et similar_text
		foreach ($candidates as $row) {
			$candidateNorm = $row['normalized_name'];

			$distance = levenshtein($normName, $candidateNorm);
			if ($distance <= $distanceMax) {
				return [
					'match' => true,
					'exact' => false,
					'conflict' => $row['name'],
					'distance' => $distance,
					'similarity' => null
				];
			}

			if ($distance > $distanceMax + 2) continue;

			similar_text($normName, $candidateNorm, $percent);
			if ($percent >= $similarMin) {
				return [
					'match' => true,
					'exact' => false,
					'conflict' => $row['name'],
					'distance' => $distance,
					'similarity' => $percent
				];
			}
		}

		return ['match' => false];
	}

}