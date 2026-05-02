<?php
require_once("Model.php");

class Notification extends Model
{
	protected $table = "message";
	protected $primaryKey = "id_message";
	// protected $fillable = [];
	protected $guarded = [];
	
	
	//fonction de transition pour l'enregistrement des messages. A supprimer après refactorisation
	public function notificationHelper(int $id,int $perso){
		$query = 'INSERT INTO message_perso (id_message,id_perso,id_dossier,lu_message,annonce,supprime_message) VALUES(?,?,?,?,?,?)';
		$values = [$id,$perso,1,0,1,0];
		
		return $this->request($query,$values);
	}
}