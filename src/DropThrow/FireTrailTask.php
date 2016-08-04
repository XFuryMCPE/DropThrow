<?php

/*    ___                 
 *   / __\   _ _ __ _   _ 
 *  / _\| | | | '__| | | |
 * / /  | |_| | |  | |_| |
 * \/    \__,_|_|   \__, |
 *                  |___/
 *
 * No copyright 2016 blahblah
 * Plugin made by fury and is FREE SOFTWARE
 * Do not sell or i will sue you lol
 * but fr tho I will sue ur face
 * DO NOT SELL
 */

namespace DropThrow;

use pocketmine\scheduler\PluginTask;
use pocketmine\entity\Item;

use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\ExplodeParticle;

class FireTrailTask extends PluginTask{

	public function __construct(MainClass $plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}

	public function onRun($currentTick){
		foreach($this->plugin->getServer()->getLevels() as $lvl){
			foreach($lvl->getEntities() as $ent){
				if($ent instanceof Item){
					if(isset($this->plugin->entityLog[$ent->getId()])){
						if($this->plugin->config->get("fire-trail") == true){
							$ent->getLevel()->addParticle(new FlameParticle($ent));
						}
						$ea = $this->plugin->entityLog[$ent->getId()];
						if($lvl->getBlockIdAt($ent->getX(),$ent->getY() - 0.5,$ent->getZ()) != 0){
							unset($this->plugin->entityLog[$ent->getId()]);
							if($this->plugin->config->get("no-pickup-mode") == true){
								$ent->getLevel()->addParticle(new ExplodeParticle($ent));
								$ent->close();
							}
						}
						else{
							$ea = [
								"x" => $ent->getX(),
								"y" => $ent->getY(),
								"z" => $ent->getZ()
							];
						}
					}
				}
			}
		}
	}
}