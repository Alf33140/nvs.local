<?php
require_once("Model.php");

class MailFile extends Model
{
	protected $table = "dossier";
	protected $primaryKey = "id_dossier";
	// protected $fillable = [];
	protected $guarded = [];
	
	/* fonction pivot pour ajouter des dossiers à un perso
	 * @var charac_ids (array) ids des persos
	 * @var file_id (int) id du dossier
	 * @return number of row created
	*/
	public function addFiles(array $charac_ids, int $file_id) {
		
		$binds = [];
		$values = [];
		
		foreach($charac_ids as $id){
			$binds[] = '(?,?)';
			$values[] = $id;
			$values[] = $file_id;
		}
		
		$binds = implode(', ',$binds);
	
		$query = 'INSERT INTO perso_as_dossiers (id_perso,id_dossier) VALUES '.$binds;
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
}