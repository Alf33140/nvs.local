<?php
require_once("Model.php");

class News extends Model
{
    protected $table = "news";
	protected $primaryKey = "id_news";
	// protected $fillable = [];
	protected $guarded = [];
	
}