<?php
require_once("Model.php");

class Building extends Model
{
	protected $table = "instance_batiment";
	protected $primaryKey = "id_instanceBat";
	// protected $fillable = [];
	protected $guarded = [];
	
	/* fonction pour compter le nombre d'ennemis autour du bâtiment
	 * @var x (int)
	 * @var y (int)
	 * @var id_camp (int)
	 * @var distance (int)
	 * @return int
	*/
	public function enemiesArround(int $x,int $y,int $id_camp, int $distance) {
	
		$query = "SELECT COUNT(id_perso) as enemies FROM perso, carte 
				WHERE perso.id_perso = carte.idPerso_carte 
				AND x_carte <= ? + ".$distance."
				AND x_carte >= ? - ".$distance."
				AND y_carte <= ? + ".$distance."
				AND y_carte >= ? - ".$distance."
				AND perso.clan != ?";

		$values = [$x,$x,$y,$y,$id_camp];
		$request = $this->request($query,$values);
		$result = $request->fetch();

		return $result[0];
	}
	
	/* fonction pivot pour intégrer un ou plusieurs persos dans un bâtiment
	 * @var charac_ids (array) ids des persos
	 * @var building_id (int) id du bâtiment
	 * @return number of row created
	*/
	public function insertCharacters(array $charac_ids, int $building_id) {
		
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
	
	/* fonction pivot pour supprimer un ou plusieurs persos d'un bâtiment
	 * @var charac_ids (array) ids des persos
	 * @var freeJail (bool) boolean indiquant si le perso peut sortir de prison 
	 * @return number of row deleted
	*/
	public function removeCharacters(array $charac_ids, bool $freeJail=false) {
		
		$jails = $this->select('instance_batiment.id_instanceBat')
						->leftJoin('batiment','instance_batiment.id_batiment','=','batiment.id_batiment')
						->where('batiment.nom_batiment','Pénitencier')
						->get();
		
		$jailsBinds = [];
		$binds = [];
		$values = [];
		$NotInJail = '';
		
		foreach($charac_ids as $id){
			$binds[] = '?';
			$values[] = $id;
		}
		
		$binds = implode(', ',$binds);
		
		if(!empty($jails)){
			foreach($jails as $jail){
				$values[] = $jail->id_instanceBat;
				$jailsBinds[] = '?';
			}
			$jailsBinds = implode(', ',$jailsBinds);
			
			if(!$freeJail){
				$NotInJail = ' AND id_instanceBat NOT IN ('.$jailsBinds.')';
			}
		}
		
		$query = 'DELETE FROM perso_in_batiment WHERE id_perso IN ('.$binds.')'.$NotInJail.'';
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/* fonction pour vérifier si un bâtiment est disponible pour respawn
	 * @camp (int)
	 * @$buildingType (array)
	 * @return Building instance or false
	*/
	public function respawnCheck(int $camp,array $buildingType = [9,8]) {
		// Ordre de respawn : fort > fortin > aléatoire dans la zone
		$buildings = $this->select('instance_batiment.id_instanceBat, instance_batiment.id_batiment, instance_batiment.x_instance, instance_batiment.y_instance, instance_batiment.contenance_instance, instance_batiment.pv_instance, instance_batiment.pvMax_instance, count(perso_in_batiment.id_perso) as nb_perso_in')
							->leftJoin('perso_in_batiment','instance_batiment.id_instanceBat','=','perso_in_batiment.id_instanceBat')
							->leftJoin('batiment','instance_batiment.id_batiment','=','batiment.id_batiment')
							->where('instance_batiment.camp_instance',$camp)
							->whereIn('instance_batiment.id_batiment',$buildingType)
							->groupBy('instance_batiment.id_instanceBat')
							->orderBy('batiment.respawn_order')
							->get();
				
		$Building_respawn = '';

		// vérification dispo fort puis fortin
		foreach($buildings as $building){
			$enemiesArround = $this->enemiesArround($building->x_instance,$building->y_instance,$camp,15);
			$lifePercent = round($building->pv_instance/$building->pvMax_instance*100,2);

			if($building->contenance_instance > $building->nb_perso_in +1 && $lifePercent>=90 && $enemiesArround<10){
				return $building;
			}
		}
		
		return false;
	}
	
	// Obsolète. A supprimer à termes
   public function getByType(int $type,int $camp=null){
		$db = $this->dbConnectPDO();
		
		if($camp){
			$query = "SELECT id_instanceBat, id_batiment, nom_instance, x_instance, y_instance, contenance_instance FROM instance_batiment WHERE camp_instance=$camp AND id_batiment=$type ORDER BY nom_instance";
		}else{
			$query = "SELECT id_instanceBat, id_batiment, nom_instance, x_instance, y_instance, contenance_instance FROM instance_batiment WHERE id_batiment=$type ORDER BY nom_instance";
		}
		
		$request = $db->prepare($query);
		$request->execute();
		$request->setFetchMode(PDO::FETCH_ASSOC);
		
		$result = $request->fetchAll();

		return $result;
   }
   
   // Obsolète. A supprimer à termes
   public function getById(int $id){
		$db = $this->dbConnectPDO();
		
		$query = "SELECT id_instanceBat, id_batiment, nom_instance, x_instance, y_instance, contenance_instance FROM instance_batiment WHERE id_instanceBat=$id";
		
		$request = $db->prepare($query);
		$request->execute();
		
		$result = $request->fetch();

		return $result;
   }
}