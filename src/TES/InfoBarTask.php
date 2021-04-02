<?php

declare(strict_types=1);

namespace TES;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use Saisana299\easyscoreboardapi\EasyScoreboardAPI;
class InfoBarTask extends Task{

	/** @var Server */
	private $server;

	public function __construct(Server $server){
		$this->server = $server;
		$this->TES  = $this->server->getPluginManager()->getPlugin("TES");
	}
	public function onRun(int $currentTick) : void{
		//$this->server->broadcastMessage($this->TES->someword);
		foreach ($this->server->getOnlinePlayers() as $player){
			$api = EasyScoreboardAPI::getInstance();
			$api->sendScoreboard($player, "sidebar", "TES in MC", false);
			$api->setScore($player, "sidebar", "X:".intval($player->getX()), 0, 1);
			$api->setScore($player, "sidebar", "Y:".intval($player->getY()), 1, 2);
			$api->setScore($player, "sidebar", "Z:".intval($player->getZ()), 2, 3);
			

		}

	}
}
