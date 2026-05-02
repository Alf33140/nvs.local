<?php
require_once("Model.php");

class Vehicle extends Model
{
	// La classe est copiée de Building pour le moment.
	// Il faudra la modifier avec la table Vehicle pour la refacto et adapter les fonctions
	
	protected $table = "instance_batiment";
	protected $primaryKey = "id_instanceBat";
	// protected $fillable = [];
	protected $guarded = [];
	
	
	/* fonction pivot pour supprimer un ou plusieurs persos d'un véhicule
	 * @var charac_ids (array) ids des persos
	 * @return number of row deleted
	*/
	public function removeCharacFromVehicle(array $charac_ids){
		$binds = [];
		$values = [];
		
		foreach($charac_ids as $id){
			$binds[] = '?';
			$values[] = $id;
		}
		
		$binds = implode(', ',$binds);
		
		$query = "DELETE FROM perso_in_train WHERE id_perso IN (".$binds.")";
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/* fonction pivot pour enregistrer la dernière case empruntée par le train. A refactoriser
	 * @var vehicule_id (int) id du véhicule
	 * @return number of row created
	*/
	public function setLastPathTile(int $vehicule_id, int $x_path, int $y_path) {
		
		$values = [$vehicule_id,$x_path,$y_path];

		$query = 'INSERT INTO train_last_dep (id_train,x_last_dep,y_last_dep) VALUES (?,?,?)';

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}

	/* fonction pivot pour mettre à jour la dernière case empruntée par le train. A refactoriser
	 * @var vehicule_id (int) id du véhicule
	 * @return number of row created
	*/
	public function updateLastPathTile(int $vehicule_id, int $x_path, int $y_path) {
		
		$values = [$x_path,$y_path,$vehicule_id];

		$query = 'UPDATE train_last_dep SET x_last_dep=?, y_last_dep=?, DeplacementDate=NOW() where id_train=?';

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/* fonction pivot pour vérifier la dernière case empruntée par le train. A refactoriser
	 * @var charac_ids (array) ids des persos
	 * @var building_id (int) id du bâtiment
	 * @return response
	*/
	public function getLastPathTile(int $vehicule_id) {
		
		$values = [$vehicule_id];
	
		$query = "SELECT x_last_dep, y_last_dep, DeplacementDate FROM train_last_dep WHERE id_train=? ORDER BY DeplacementDate DESC LIMIT 1";

		$request = $this->request($query,$values);
		$request->setFetchMode(PDO::FETCH_ASSOC);
		$result = $request->fetch();

		return $result;
	}
	
	/* fonction pivot pour supprimer la dernière case empruntée par le train. A refactoriser
	 * @var vehicule_id (int) id du véhicule
	 * @return number of row created
	*/
	public function deleteLastPathTile(int $vehicule_id) {
		
		$values = [$vehicule_id];

		$query = 'DELETE FROM train_last_dep WHERE id_train=?';

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/* fonction pivot pour vérifier les rails autour du train. A refactoriser
	 * @var x (int) position x actuelle du train
	 * @var y (int) position y actuelle du train
	 * @var last_x (int) dernière position x du train
	 * @var last_y (int) dernière position y du train
	 * @return tous les rails dispos
	*/
	public function closestPathTiles(int $x, int $y, int $last_x=null, int $last_y=null) {
		
		$x1 = $x-1;
		$x2 = $x+1;
		$y1 = $y-1;
		$y2 = $y+1;
		
		if(is_null($last_x) OR is_null($last_y)){
			$last_position = '-1;-1';
		}else{
			$last_position = $last_x.';'.$last_y;
		}
		$current_position = $x.';'.$y;
		
		$values = [$x1,$x2,$y1,$y2,$last_position,$current_position];

		$query = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte 
				WHERE fond_carte IN ('rail.gif','rail_1.gif','rail_2.gif','rail_3.gif','rail_4.gif','rail_5.gif','rail_7.gif','railP.gif')
				AND x_carte >= ? AND x_carte <= ?
				AND y_carte >= ? AND y_carte <= ?
				AND coordonnees NOT IN (?,?)";

		$request = $this->request($query,$values);
		$request->setFetchMode(PDO::FETCH_ASSOC);
		$result = $request->fetchAll();
		
		return $result;
	}
	
	/* fonction pivot pour vérifier s'il y a une gare autour du train. A refactoriser
	 * @var x (int) position x actuelle du train
	 * @var y (int) position y actuelle du train
	 * @var gare_id (int) ID de la gare d'arrivée
	 * @return toutes les cases de gare
	*/
	public function closestGareStation(int $x, int $y, int $gare_id) {
		
		$x1 = $x-1;
		$x2 = $x+1;
		$y1 = $y-1;
		$y2 = $y+1;
		
		$values = [$x1,$x2,$y1,$y2,$gare_id];

		$query = "SELECT count(*) as gareStationTiles FROM carte 
				WHERE x_carte >= ? AND x_carte <= ?
				AND y_carte >= ? AND y_carte <= ?
				AND idPerso_carte = ?";

		$request = $this->request($query,$values);
		$request->setFetchMode(PDO::FETCH_ASSOC);
		$result = $request->fetch();
		
		return $result;
	}

	/* fonction pivot pour enregistrer/supprimer le blocage du train. A refactoriser
	 * @var gare_id (int) ID du train bloqué
	 * @return rowCount
	*/
	public function blockedTrain(int $train_id, bool $unblocked=false) {
		
		$values = [$train_id];

		if($unblocked){
			$query = "DELETE FROM train_compteur_blocage WHERE id_train=?";

			$request = $this->request($query,$values);
			$result = $request->rowCount();
			
			return $result;
		}

		$query = "SELECT * FROM train_compteur_blocage WHERE id_train=?";

		$request = $this->request($query,$values);
		$result = $request->rowCount();
	
		if($result>0){
			$query2 = "UPDATE train_compteur_blocage SET compteur = compteur+1 WHERE id_train = ?";
		}else{
			$query2 = "INSERT INTO train_compteur_blocage (id_train, compteur, date_debut_blocage) VALUES (?, 1, NOW())";
		}

		$request = $this->request($query2,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/* fonction pivot pour mettre à jour la carte avec le déplacement du train. A refactoriser
	 * @var vehicule_id (int) id du véhicule
	 * @return number of row created
	*/
	public function updateMapForTrain(int $vehicule_id, string $image_train=null, int $x=null,int $y=null) {
		
		if(is_null($x) OR is_null($y)){
			$values = [0,$vehicule_id];
			$query = 'UPDATE carte SET idPerso_carte=NULL, occupee_carte=?, image_carte=NULL WHERE idPerso_carte=?';
		}else{
			$values = [$vehicule_id,1,$image_train,$x,$y];
			$query = 'UPDATE carte SET idPerso_carte=?, occupee_carte=?, image_carte=? WHERE x_carte=? AND y_carte=?';
		}

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/* fonction pivot pour intégrer un ou plusieurs persos dans ue gare
	 * @var charac_ids (array) ids des persos
	 * @var building_id (int) id du bâtiment
	 * @return number of row created
	*/
	public function insertCharactersInStation(array $charac_ids, int $building_id) {
		
		$binds = [];
		$values = [];
		
		foreach($charac_ids as $id){
			$binds[] = '(?,?)';
			$values[] = $id;
			$values[] = $building_id;
		}
		
		$binds = implode(', ',$binds);
	
		$query = 'INSERT INTO perso_in_batiment (id_perso,id_instanceBat) VALUES '.$binds;

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
}