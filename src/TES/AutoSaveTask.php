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
		foreach ($this->TES->player_data as $key => $value) {
			//ここに保存機能をつけといてくれ
		}
		foreach ($this->TES->shopdata as $key => $value) {
			//同上だ
		}
		
	}
}
