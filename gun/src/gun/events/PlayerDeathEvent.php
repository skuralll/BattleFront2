<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\gameManager;
use gun\data\playerData;

class PlayerDeathEvent extends Events {

	public function __construct($plugin){
		$this->playerdata = playerdata::getPlayerData();
	}

	public function call($event){
		$event->setKeepInventory(true);
		$player = $event->getPlayer();
    $this->playerdata->setAccount($player->getName(), 'death', $this->playerdata->getAccount()['death']++);
		if(isset($p->shot)) $p->shot = false;

		if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$killer = $player->getLastDamageCause()->getDamager();
      $this->playerdata->setAccount($killer->getName(), 'kill', $this->playerdata->getAccount()['kill']++);
			$team = $this->plugin->gameManager->getTeam($killer);
			if($team !== false){
				$this->plugin->gameManager->addKillCount($team);
			}
		}
	}
}
