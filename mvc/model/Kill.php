<?php
require_once("Model.php");

class Kill extends Model
{
	protected $table = "dernier_tombe";
	protected $primaryKey = "id_perso";
	// protected $fillable = [];
	protected $guarded = [];
}