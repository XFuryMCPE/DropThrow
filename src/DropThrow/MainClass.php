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

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;

use pocketmine\utils\Config;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ByteTag;

use pocketmine\entity\Entity;

use pocketmine\level\sound\GhastShootSound;

class MainClass extends PluginBase implements Listener{

	public $entityLog = [];
	
	public function onEnable(){
		@mkdir($this->getDataFolder());
		if(!file_exists($this->getDataFolder() . "config.yml")){
			$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML,[
				"throw-speed" => 1.5,
				"fire-trail" => true,
				"swoosh-throw-sound" => true,
				"no-pickup-mode" => true
			]);
			$this->config->save();
		}
		else{
			$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		}
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new FireTrailTask($this), 2);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDrop(PlayerDropItemEvent $e){
		$p = $e->getPlayer();
		$i = $e->getItem();
		$e->setCancelled();
		$p->getInventory()->removeItem($i);
		$nbt = new CompoundTag ("", [ 
			"Pos" => new ListTag("Pos", [ 
				new DoubleTag("", $p->getX()),
				new DoubleTag("", $p->getY() + $p->getEyeHeight()),
				new DoubleTag("", $p->getZ()) 
			]),
			"Motion" => new ListTag("Motion", [ 
				new DoubleTag("", -sin($p->yaw / 180 * M_PI) * cos($p->pitch / 180 * M_PI)),
				new DoubleTag("", -sin($p->pitch / 180 * M_PI)),
				new DoubleTag("", cos($p->yaw / 180 * M_PI) * cos($p->pitch / 180 * M_PI)) 
			]),
			"Rotation" => new ListTag("Rotation",[ 
				new FloatTag("", $p->yaw),
				new FloatTag("", $p->pitch) 
			]),
			"Health" => new ShortTag("Health", 5),
			"Item" => new CompoundTag("Item", [
				"id" => new ShortTag("id", $i->getId()),
				"Damage" => new ShortTag("Damage", $i->getDamage()),
				"Count" => new ByteTag("Count", $i->getCount())
			]),
			"PickupDelay" => new ShortTag("PickupDelay", 1) 
		]);
		$f = $this->config->get("throw-speed");
		$thrown = Entity::createEntity("Item", $p->chunk, $nbt, $p);
		$thrown->setMotion($thrown->getMotion()->multiply($f));
		$thrown->spawnToAll();
		$this->entityLog[$thrown->getId()] = [
			"x" => $thrown->getX(),
			"y" => $thrown->getY(),
			"z" => $thrown->getZ()
		];
		if($this->config->get("swoosh-throw-sound") == true){
			$p->getLevel()->addSound(new GhastShootSound($p));
		}
	}

	public function onPickup(InventoryPickupItemEvent $e){

	}
}
