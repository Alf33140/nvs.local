<?php
require_once("Model.php");

class Bank extends Model
{
	protected $table = "banque_as_compagnie";
	// protected $primaryKey = "";
	// protected $fillable = [];
	protected $guarded = [];
	
	/* Id des opérations bancaires :
		Dépôt = 0;
		Retrait = 1;
		emprunt = 2;
		Remboursement emprunt = 3;
		Virement = 4;
		départ de compagnie = 5;
	*/

	/**
     * Fonction anti-zerk pour le dépôt de thunes après avoir fait un retrait
	 * @param $perso int
	 * @param $operation int
     * @return false or dateInterval
     */
	public function antiZerkDeposit(int $perso,int $operation=1){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_bank';
		
		$pivotTable = 'banque_log';
		$select = 'id_log, date_log as lastOp';
		$and = 'AND id_perso = ? AND operation = ?';
		
		$query = 'SELECT '.$select.' FROM '.$pivotTable.' WHERE '.$firstTableKeyInPivot.' = ? '.$and.' ORDER BY date_log DESC LIMIT 1';
		$values = [$this->$firstTableKey,$perso,$operation];		
		
		$request = $this->request($query,$values);
		$request = $request->fetch(PDO::FETCH_OBJ);
		
		if(isset($request->lastOp)){
			
			$lastOp = date_create_immutable($request->lastOp, new DateTimeZone('Europe/Paris'));
			$unlockDate = $lastOp->modify('+8 hours');
			$now = date_create("now", new DateTimeZone('Europe/Paris'));
			
			$interval = $unlockDate->diff($now);
			
			if($now<$unlockDate){
				return $interval;
			}else{
				return false;
			}
		}
		
		return false;
	}
	
	/**
     * Pivot table "histobanque_compagnie" to delete the bank history
     * @return bool
     */
	public function deleteHistory(){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_bank';
		
		$pivotTable = 'histobanque_compagnie';
		
		$query = 'DELETE FROM '.$pivotTable.' WHERE '.$firstTableKeyInPivot.' = ?';
		$values = [$this->$firstTableKey];

		$request = $this->request($query,$values);
			
		return $request;
	}
	
	/**
     * Pivot table "banque_log" to delete the bank log
     * @return bool
     */
	public function deleteBankLog(){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_bank';
		
		$pivotTable = 'banque_log';
		
		$query = 'DELETE FROM '.$pivotTable.' WHERE '.$firstTableKeyInPivot.' = ?';
		$values = [$this->$firstTableKey];

		$request = $this->request($query,$values);
			
		return $request;
	}
	
	/**
     * Pivot table "banque_log" to get the bank log order by Date
     * @param $limit int
	 * @param $desc bool False
	 * @param $perso int NULL
	 * @param $operation array NULL
     * @return class
     */
	public function getBankLog(int $limit, bool $desc=False, int $perso=NULL,array $operations=NULL){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_bank';
		
		$pivotTable = 'banque_log';
	
		$values = [$this->$firstTableKey];
		$add_perso = '';
		$add_ope = '';
		$add_desc = '';
		$ope_binds = [];
		
		if($desc){
			$add_desc = ' DESC';
		}
		
		if($limit<=0){
			$limit = "";
		}else{
			$limit = ' LIMIT '.$limit;
		}
		
		if($perso){
			$add_perso = ' AND banque_log.id_perso = ?';
			$values[] = $perso;
		}
		
		if($operations){
			foreach($operations as $ope){
				$values[] = $ope;
				$ope_binds[] .= '?';
			}
			
			$ope_binds = implode(', ',$ope_binds);
			$add_ope = ' AND operation IN ('.$ope_binds.')';
		}
		
		$select = 'banque_log.id_log, banque_log.id_bank, banque_log.id_compagnie, banque_log.id_perso, banque_log.operation, banque_log.montant_transfert, banque_log.montant_final, banque_log.is_auteur, banque_log.id_receiver, banque_log.details, banque_log.date_log, perso.nom_perso';
		
		$query = 'SELECT '.$select.' FROM '.$pivotTable.' LEFT JOIN perso ON perso.id_perso='.$pivotTable.'.id_perso WHERE '.$firstTableKeyInPivot.' = ?'.$add_perso.$add_ope.' ORDER BY date_log'.$add_desc.$limit;

		$request = $this->request($query,$values);
			
		return $request->fetchAll(PDO::FETCH_CLASS);
	}
	
	/**
     * Pivot table "banque_log" to add a bank log
	 * @param $company int
	 * @param $perso int
	 * @param $operation int
	 * @param $amount int
	 * @param $perso int
     * @return bool
     */
	public function addBankLog(int $company, int $perso, int $operation, int $amount, int $total, string $details='', bool $author=true, $recipient=NULL){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_bank';
		
		$pivotTable = 'banque_log';
		
		
		if($author==true){
			$author=1;
		}else{
			$author=0;
		}
		if(empty($details)){
			$details=NULL;
		}
	
		$columns = [$firstTableKeyInPivot, 'id_compagnie', 'id_perso', 'operation', 'montant_transfert', 'montant_final', 'is_auteur', 'id_receiver', 'details','date_log'];
		$values = [$this->$firstTableKey, $company, $perso, $operation, $amount, $total, $author, $recipient, $details];
		
		foreach($columns as $column){
			if($column=='date_log'){
				$binds[] = 'NOW()';
			}else{
				$binds[] = '?';
			}
		}
		
		$columns = implode(', ',$columns);
		$binds = implode(', ',$binds);
		
		$query = 'INSERT INTO '.$pivotTable.' ('.$columns.') VALUES ('.$binds.')';
		
		return $this->request($query,$values);
	}
}