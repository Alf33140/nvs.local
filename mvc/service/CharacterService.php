<?php
require_once("../mvc/model/Character.php");
require_once("../mvc/model/Building.php");
require_once("../mvc/model/Company.php");
require_once("../mvc/model/Bank.php");
require_once("../mvc/model/Account.php");
require_once("ActionResult.php");

class CharacterService
{
    /**
     * upgrade des capacités (PV, PM, PA…)
     */
	public function upgradeCapacities(Character $character, array $input, array $unsetAttributes = []): ActionResult
	{
        if (!empty($input['pv_cost']) && $character->pi_perso >= $input['pv_cost']) {
            $character->pvMax_perso++;
            $character->pi_perso -= $input['pv_cost'];
			$msg = "PV augmentés de +1 (".$character->pvMax_perso.")";
        }
		if(!empty($input['pm_cost']) AND $character->pi_perso>=$input['pm_cost']){
			$character->pmMax_perso++;
			$character->pi_perso=$character->pi_perso-$input['pm_cost'];
			$msg = "PM augmentés de +1 (".$character->pmMax_perso.")";
		}
		if(!empty($input['pa_cost']) AND $character->pi_perso>=$input['pa_cost']){
			$character->paMax_perso++;
			$character->pi_perso=$character->pi_perso-$input['pa_cost'];
			$msg = "PA augmentés de +1 (".$character->paMax_perso.")";
		}
		if(!empty($input['percep_cost']) AND $character->pi_perso>=$input['percep_cost']){
			$character->perception_perso++;
			$character->pi_perso=$character->pi_perso-$input['percep_cost'];
			$msg = "Perception augmentée de +1 (".$character->perception_perso.")";
		}
		if(!empty($input['recup_cost']) AND $character->pi_perso>=$input['recup_cost']){
			$character->recup_perso++;
			$character->pi_perso=$character->pi_perso-$input['recup_cost'];
			$msg = "récupération augmentée de +1 (".$character->recup_perso.")";
		}

		if(!empty($unsetAttributes)){
			foreach($unsetAttributes as $attr){
				unset($character->$attr);
			}
		}
		
        if ($character->update()) {
            return ActionResult::success($msg);
        } else {
            return ActionResult::fail("Une erreur est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.");
        }
    }
	
	/**
     * upgrade des rapatriements
     */
	public function upgradeRespawns(Character $character, array $input, array $allowedRespawns, array $unsetAttributes = []): ActionResult
	{
		$allowedByType = [];
		foreach ($allowedRespawns as $r) {
			$allowedByType[$r->id_batiment][] = $r->id_instanceBat;
		}
		
		$currentRespawns = [];
		foreach($character->respawns($character->id_perso) as $cr){
			$currentRespawns[$cr['id_bat']] = $cr['id_instance_bat'];
		}
		
		$success = true;
		
		foreach($allowedByType as $typeId => $instance){
			$newRespawn = (int)$input[$typeId.'_select'] ?? 0;
			$currentRespawn = (int) $currentRespawns[$typeId] ?? null;
			
			// Si aucun changement de respawn de ce type, on continue
			if($newRespawn === $currentRespawn){
				continue;
			}
			
			if($newRespawn === 0 && $currentRespawn){
				$success = $success && $character->removeRespawn($currentRespawn,$character->id_perso);
				continue;
			}
			
			if($currentRespawn!==$newRespawn){
				if($currentRespawn){
					$success = $success && $character->updateRespawn($currentRespawn,$newRespawn,$character->id_perso);
				}else{
					$success = $success && $character->saveRespawn($newRespawn,$typeId,$character->id_perso);
				}
			}
		}

        return $success
			? ActionResult::success("Les rapatriements ont été mis à jour")
			: ActionResult::fail("Erreur lors de la mise à jour des rapatriements. Si le problème persiste, contactez l'administrateur.",$input);
	}
	
	/**
     * activation/désactivation des persos
     */
	public function activateToggle(Character $character, array $input, array $unsetAttributes = []): ActionResult
    {
		$leader = new Character();
		$leader = $leader->select('perso.id_perso, perso.pa_perso, grades.id_grade, grades.point_armee_grade')
						->leftJoin('perso_as_grade','perso_as_grade.id_perso','=','perso.id_perso')
						->leftJoin('grades','perso_as_grade.id_grade','=','grades.id_grade')
						->where('perso.idJoueur_perso',$character->idJoueur_perso)
						->where('perso.chef',1)
						->get();
		$leader = $leader[0];
		
		// le chef doit avoir au moins 2 PA et doit être dans un bâtiment
		if($leader->pa_perso<2){
			return ActionResult::fail("Vous n'avez pas assez de points d'action. Réessayez au prochain tour");
		}
		
		if(empty($leader->inBuilding($leader->id_perso))){
			return ActionResult::fail("Votre chef doit être dans un bâtiment pour réactiver/désactiver un perso");
		}
		
		// activation du perso
		if($input['activationBtn']=='activate'){
			
			$usedPG = (int)((new Character())
						->select('SUM(cout_pg) as used_pg')
						->leftJoin('type_unite','type_unite.id_unite','=','perso.type_perso')
						->where('perso.idJoueur_perso',$character->idJoueur_perso)
						->where('perso.est_renvoye',0)
						->get()[0]->used_pg ?? 0);
			
			// le chef doit avoir le nombre de points de recrutement nécessaires
			if($leader->point_armee_grade<=$usedPG){
				return ActionResult::fail("Vous n'avez pas assez de point de recrutement disponibles. Désactivez un autre perso ou passez un grade pour réactiver un grouillot");
			}
			
			$respawnBuilding = ((new Building())
								->select('id_instanceBat, id_batiment, x_instance, y_instance')
								->where('camp_instance',$character->clan)
								->where('id_batiment',9)
								->groupBy('id_instanceBat')
								->get()[0] ?? NULL);
			
			if(!$respawnBuilding){
				return ActionResult::fail("Aucun bâtiment de respawn disponible");
			}
			
			$leader->pa_perso -= 2;
			unset($leader->id_grade,$leader->point_armee_grade);
			
			if(!$leader->update()){
				return ActionResult::fail("Une erreur est survenue. Veuillez recommencer. Si le problème persiste, contactez l'administrateur");
				// log à intégrer à terme
			}
			
			$character->pa_perso = (int) $character->paMax_perso/2;
			$character->pm_perso = (int) $character->pmMax_perso/2;
			$character->pv_perso = (int) $character->pvMax_perso/2;
			$character->x_perso = $respawnBuilding->x_instance;
			$character->y_perso = $respawnBuilding->y_instance;
			$character->est_renvoye = 0;
			
			if(!empty($unsetAttributes)){
				foreach($unsetAttributes as $attr){
					unset($character->$attr);
				}
			}
			
			if(!$character->update()){
				return ActionResult::fail("Une erreur est survenue. Veuillez recommencer. Si le problème persiste, contactez l'administrateur");
				// log à intégrer à terme
			}
			
			if(!$respawnBuilding->insertCharacters([$character->id_perso],$respawnBuilding->id_instanceBat)){
				return ActionResult::fail("Une erreur est survenue. Veuillez recommencer. Si le problème persiste, contactez l'administrateur");
				// log à intégrer à terme
			}
			
			return ActionResult::success("votre perso a été réactivé");
		}
		
		// désactivation du perso
		if($input['activationBtn']=='desactivate'){
			$characterModel = new Character();
			
			if(empty($characterModel->inBuilding($character->id_perso))){
				return ActionResult::fail("Votre grouillot doit être dans un bâtiment pour être désactivé");
			}
			
			$CompanyModel = new Company();
			$characterInCompany = $CompanyModel->inCompany($character->id_perso);
			
			// si le perso est dans une compagnie
			if($characterInCompany){
				
				if($characterInCompany['slug']=='leader'){
					return ActionResult::fail("Vous ne pouvez pas désactiver un chef de compagnie");
				}
				
				$company = $CompanyModel
							->select('id_compagnie, nom_compagnie')
							->where('id_compagnie',$characterInCompany['id_compagnie'])
							->get()[0];
				
				$account = (new Account())
						->select('*')
						->where('id_perso',$character->id_perso)
						->get()[0] ?? NULL;
			
				if($account && $account->montant_emprunt>0){
					return ActionResult::fail("Vous ne pouvez pas désactiver un personnage qui a des dettes dans une compagnie");
				}
				
				if($account){
					$bank = new Bank();
					$bank = $bank->select('id, id_compagnie, montant')->find($account->bank_id)??NULL;
					
					if(!$bank){
						return ActionResult::fail("Une erreur est survenue. Veuillez recommencer. Si le problème persiste, contactez l'administrateur");
						// log à intégrer à terme
					}
					
					// on supprime le compte en banque du perso
					if(!$account->delete()){
					return ActionResult::fail("Une erreur est survenue. Veuillez recommencer. Si le problème persiste, contactez l'administrateur");
					// log à intégrer à terme
					}
					$bankLog = $bank->addBankLog($bank->id_compagnie, $character->id_perso,5,$account->montant,$bank->montant,'le perso a quitté la compagnie', false);
				}
				
				if(!$company->dismissPerso($character->id_perso)){
					return ActionResult::fail("Une erreur est survenue. Veuillez recommencer. Si le problème persiste, contactez l'administrateur");
					// log à intégrer à terme
				}
			}

			if(!$characterModel->desactivateCharacters([$character->id_perso])){
				return ActionResult::fail("Une erreur est survenue. Veuillez recommencer. Si le problème persiste, contactez l'administrateur");
				// log à intégrer à terme
			}
			
			return ActionResult::success("votre perso a été désactivé");
		}
		
		return ActionResult::fail("Une erreur est survenue. Veuillez recommencer. Si le problème persiste, contactez l'administrateur");
	}
}