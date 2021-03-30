<?php

declare(strict_types=1);

namespace TES;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\level\Position;
use bbo51dog\pmdiscord\Sender;
use bbo51dog\pmdiscord\element\Content;
use bbo51dog\pmdiscord\element\Embed;
use bbo51dog\pmdiscord\element\Embeds;

/*
useクソ多いけど多分いくつか使ってないよねこれ
*/
//???????????????????????????????????????????????????????????????????????????
/*
????????????????
????????????????
*/
/*
オッスオラfolosuru！後世のためにメモを残しとくぞ！
$this->player_data
	これは名前の通りプレイヤーの情報だな！そのプレイヤーの名前で連想配列になってるぞ！
	例えばfolosuruってプレイヤーの情報を見たいときは$this->player_data["folosuru"]だな！
	そしてその中にさらに連想配列で各種情報が入るぞ！！
	例えばfolosuruの持ってるACPって通貨の値出すなら$this->player_data["folosuru"]["ACP"]だな！
	そしてシステム的に通貨はいくらでも追加可能だ！だから何だって話だけどな！！
plugin_data/TES/*.json
	これは上の情報を保存するとこだな！*にはプレイヤーの名前を入れるぞ！
	名前の通りjson形式だ！

	*/
class MainClass extends PluginBase implements Listener{
	public $api;

	public function onLoad() : void{
		$this->getLogger()->info(TextFormat::WHITE . "I've been loaded!");
	}

	public function onEnable() : void{
		$this->someword = "unchi";
		$this->country =  array('zennaka' => , );
		$this->getServer()->getPluginManager()->registerEvents(new ExampleListener($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this->getServer()), 1200);//BroadcastTaskのアレを120tickごとに動かす
		//$this->getScheduler()->scheduleRepeatingTask(new InfoBarTask($this->getServer()), 4);
		$this->getLogger()->info(TextFormat::DARK_GREEN . "I've been enabled!");
//		$content = new Content();
//		$content->setText("サーバーが起動しました");
//		$webhook = Sender::create($this->WebhookURL)
//			->add($content)
//			->setCustomName("from PMMP server");
//		Sender::send($webhook);
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->WebhookURL = "https://discord.com/api/webhooks/816533633208549376/CyIo52uELVMlPrYjwD2xLzLOf5HXblG9UjNNFSmUbvXgiyCZFIwkzhZOTs-0JkRdNq_5";
	}

	public function onDisable() : void{
		$this->getLogger()->info(TextFormat::DARK_RED . "I've been disabled!");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "example":
				$sender->sendMessage("Hello " . $sender->getName() . "!");
				return true;
			case "hub":
				$level = $this->getServer()->getLevelByName("world");
				$pos = new Position(0,100,0,$level);
				$player = $sender;
				$player->teleport($pos);
				return true;
			case "disc":
				$content = new Content();
				$content->setText("This message was sent from PMMP server.Hello,discord.py.");
				$webhook = Sender::create($this->WebhookURL)
					->add($content)
					->setCustomName("from PMMP server");
				Sender::send($webhook);
				return true;
			case "register":
				$sender->sendMessage($this->player_data[$sender->getName()]);
				return true;
			case "savepda":
				$arr = json_encode($this->player_data[$sender->getName()]);
				file_put_contents("plugin_data/TES/player/".$sender->getName().".json",$arr);
				return true;
			case "country":
				if (empty($args)){
					$sender->sendMessage("使い方は/country helpで確認してください。");
				}else {
					switch ($args[0]) {
						case 'help':
							$sender->sendMessage("[country help]");
							$sender->sendMessage("/country help:このヘルプを表示します。");
							$sender->sendMessage("/country new [名前]:新たに国を作成します。すでに国に所属している場合はできません。");
							return true;
						case "new":
							$sender->sendMessage($args[0].$args[1]);
							/*if ($this->player_data[$sender->getName()]["country"] = "default"){
								if (array_key_exists($args[1],$this->country_data)){
									$sender->sendMessage("[国家作成]その名前の国家はすでに存在します");
								}else {
									$sender->sendMessage("新たに国家".$args[1]."を作成しました！");
								}
							}*/
						default:
							// code...
							break;
					}				}
				return true;
			default:
				throw new \AssertionError("This line will never be executed");

		}
	}
	public function onChat(PlayerChatEvent $event){
		$content = new Content();
		$player = $event->getPlayer();
		$message = $event->getMessage();
		$content->setText("<" .$player->getname().">"." ".$message);
		$webhook = Sender::create($this->WebhookURL)
			->add($content)
			->setCustomName("マイクラ内チャット");
		Sender::send($webhook);
	}
	public function onLogin(PlayerLoginEvent $event){
		#$content = new Content();
		#$content->setText("Player login! Name:".$event->getPlayer()->getName()." ID:".$event->getPlayer()->getXuid());//EventからPlayerを取得、そこからXuid取得
		#$webhook = Sender::create($this->WebhookURL)
		#	->add($content)
		#	->setCustomName("test");
		#Sender::send($webhook);
		if (file_exists("plugin_data/TES/player/".$event->getPlayer()->getName().".json")){
			$player_data_tmp = file_get_contents("plugin_data/TES/player/".$event->getPlayer()->getName().".json");
			#$player_data_tmp = mb_convert_encoding($player_data_tmp,"UTF8",auto);
			$this->player_data[$event->getPlayer()->getName()] = $player_data_tmp;
			$event->getPlayer()->sendMessage($this->player_data[$event->getPlayer()->getName()]);
			$event->getPlayer()->sendMessage("おかえりなさい、".$event->getPlayer()->getName()."さん！");
		}else {
			$this->player_data[$event->getPlayer()->getName()] = array('name' => $event->getPlayer()->getName() , "first_login" => 1 ,"ACP" => 0,"country" =>"default" );
			$this->getLogger()->info($this->player_data[$event->getPlayer()->getName()]["name"]);
			$this->getLogger()->info("プレイヤーデータが存在しないため、新規作成しました。")	;
		}
	}
	public function onJoin(PlayerJoinEvent $event){
		if ($this->player_data[$event->getPlayer()->getName()]["first_login"] = 1) {
			$event->setJoinMessage(TextFormat::YELLOW.$event->getPlayer()->getName()."さんが新たに参加しました！");
			$event->getPlayer()->sendMessage("はじめまして、".$event->getPlayer()->getName()."さん！TES in MCへようこそ！");
			$this->player_data[$event->getPlayer()->getName()]["first_login"] = 0;
		}else {
			$event->setJoinMessage(TextFormat::YELLOW.$event->getPlayer()->getName()."さんが参加しました");
		}
	}
	public function onQuit(PlayerQuitEvent $event){
		$this->getLogger()->info(TextFormat::WHITE . "Player Quit. Name:".$event->getPlayer()->getName());
		$arr = json_encode($this->player_data[$event->getPlayer()->getName()]);
		file_put_contents("plugin_data/TES/player/".$event->getPlayer()->getName().".json",$arr);
	}
}
