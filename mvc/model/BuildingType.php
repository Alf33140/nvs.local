<?php
require_once("Model.php");

class BuildingType extends Model
{
	protected $table = "batiment";
	protected $primaryKey = "id_batiment";
	// protected $fillable = [];
	protected $guarded = [];
}