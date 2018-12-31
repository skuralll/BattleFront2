<?php

namespace gun\weapons;

use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\entity\Entity;

/* Particles */
// use pocketmine\level\particle\XXXXXXXXXXXX;
use pocketmine\level\sound\DoorBumpSound;
use pocketmine\level\sound\DoorCrashSound;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\ExplodeParticle;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use gun\Callback;
use gun\Blocks;


class beam {

	public function __construct($api){
       		$this->server = $api->plugin->getServer()->getInstance();
        	$this->schedule = $api->plugin->getScheduler();
    	}
    	
    	public function shot($p,$data){
    		if(!isset($p->shot)) return $p->sendMessage('エラー');
    		if($p->reloading) return false;
        	$speed = $data['speed'];
        	$damage = $data['damage'];
        	$reload = $data['reload'];
        	$ammo = $p->ammo;
        	$max_ammo = $data['max_ammo'];
        	if($p->ticks['shot'] <= ($time = round(microtime(true), 5))){
            		$p->ticks['shot'] = $time + round(0.033367 * $speed, 5);
            		if($ammo-- <= 0){
                		$p->sendPopUp("§l§cリロードしなさい");
                		return false;
            		}
            		$lv = $p->level;
            		if(!isset($lv)) return false;
            		$lv->addSound(new DoorCrashSound($p, -100));
            		$p->ammo = $ammo;
            		if(!$p->hasEffect(15)){
                		$p->sendPopUp('                   §l§e残弾数： §f' . $ammo . '/' . '§b'.$max_ammo);//. $max_ammo);
                		$rad = 0.01;
                		$x = $p->x;
                		$y = $p->y + $p->getEyeHeight();
                		$z = $p->z;
                		$motionX = -sin($p->yaw / 180 * M_PI) * cos($p->pitch / 180 * M_PI);
                		$motionZ =  cos($p->yaw / 180 * M_PI) * cos($p->pitch / 180 * M_PI);
                		$motionY = -sin($p->pitch / 180 * M_PI);
                		$c = 30;
               			while ($c-- >= 0){
                    			$x += $motionX;
                    			$y += $motionY;
                    			$z += $motionZ;
					$pos = new Vector3($x, $y, $z);
                    			foreach ($lv->getPlayers() as $player){
                    				if($player !== $p){
                        				if($pos->distance($player) < 1){
                            					$headshot = false;
                            					if($player->getY() + 1.42 <= $y){
                                					$headshot = true;
                                					$damage *= 3;
                                					$p->addTitle('§4>   <','',1,1,1);
                            					}else{
                            						$p->addTitle('>   <','',1,1,1);
                            					}
                            					$ev = new EntityDamageByEntityEvent($p,$player,EntityDamageEvent::CAUSE_PROJECTILE, (int)$damage);                            		$ev->setKnockBack(0);
                            					$ev->setAttackCooldown(0);
                            					$player->attack($ev);
                                				$lv->addParticle(new DestroyBlockParticle($pos, Block::get(236,14)), [$p]);
                                				break 2;
                                			}
                            			}
                       			}
                       			if(Blocks::isSolid($id = $lv->getBlockIdAt($x, $y, $z))){
                        			$b = Block::get($id, $lv->getBlockDataAt($x, $y, $z));
                        			$lv->addParticle(new DestroyBlockParticle(new Vector3($x - $motionX, $y - $motionY, $z - $motionZ), $b), [$p]);
                        			break;
                    			}
                    		}
            		}else{
                		$p->sendPopUp('見えない弾が当たるわけ無いだろ！いい加減にしろ！');//. $max_ammo);
            		}
            	}
            	if($p->shot)
            	$this->schedule->scheduleDelayedTask(new CallBack([$this, 'shot'], [$p, $data]), 1);
       }
}

