<?php
require_once("Model.php");

class RespawnZone extends Model
{
	protected $table = "zone_respawn_camp";
	protected $primaryKey = "id_zone";
	// protected $fillable = [];
	protected $guarded = [];
}