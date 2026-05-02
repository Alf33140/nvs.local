<?php
require_once("Model.php");

class GameLog extends Model
{
	protected $table = "game_log";
	// protected $primaryKey = "id";
	// protected $fillable = [];
	protected $guarded = [];
	
	/* liste des codes de log
		accès : 1
			connexion : 10
			accès à la page principale : 11
			accès à une autre page : 12
		triche : 2
			contrôle de perso non autorisé : 1
		pendaison : 3
		respawn : 4
		animation : 5
			alerte refresh 10 sec : 52
			alerte refresh 30 sec : 53
	*/
}