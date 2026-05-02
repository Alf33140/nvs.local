<?php
require_once("Model.php");

class User extends Model
{
    protected $table = "joueur";
	protected $primaryKey = "id_joueur";
	// protected $fillable = [];
	protected $guarded = [];
	
		
	/**
   * Enregistre les infos utilisateur
   * @return row count
   */
	public function addUserOkLogin(int $user_id, string $ip, string $userAgent, string $cookie, int $est_acquitte=0) {
	
		$query = 'INSERT INTO user_ok_logins (id_joueur,ip_joueur,user_agent,cookie_val,est_acquitte,time) VALUES (?,?,?,?,?,NOW())';
		$values = [$user_id,$ip,$userAgent,$cookie,$est_acquitte];

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	public function addFailedLoginAttempt(int $user_id, string $ip){
		$query = 'INSERT INTO user_failed_logins (user_id,ip_address,attempted_at) VALUES (?,INET_ATON(?),NOW())';
		$values = [$user_id,$ip];

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	public function setMultiAccount(int $user_id, int $target_id,string $details){
		$query = 'INSERT INTO declaration_multi (user_id,target_id,situation,date_declaration) VALUES (?,?,?,NOW())';
		$values = [$user_id,$target_id,$details];

		try{
			$request = $this->request($query,$values);
			$result = $request->rowCount();
			if($result>0){
				return true;
			}else{
				return false;
			}
		}catch(PDOException $e){
			return $e->getCode();
		}
	}
	
	public function getMultiAccount(){
		$primaryKey = $this->primaryKey;
		$user_id = $this->$primaryKey;

		$query = 'SELECT id_declaration, user_id, target_id, user.nom_perso as user_name, target.nom_perso as target_name, user.clan as user_clan, target.clan as target_clan, situation, date_declaration
				FROM declaration_multi
				LEFT JOIN perso as user ON user_id=user.idJoueur_perso
				LEFT JOIN perso as target ON target_id=target.idJoueur_perso
				WHERE user.chef=1 AND target.chef=1 AND declaration_multi.user_id=?';
		$values = [$user_id];
		
		$request = $this->request($query,$values);
		$result = $request->fetchAll();

		return $result;
	}
}