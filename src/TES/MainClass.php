<?php

declare(strict_types=1);


namespace TES;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
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
$this->country_territory
	国の領土だ！例によって連想配列になっていて、"[x座標/50の小数点以下切り捨て]_[y座標/50の小数点以下切り捨て]"=>”所有国家”だ！
	$this->country_territory[intval($sender->getX()/50)."_".intval($sender->getZ()/50))]
	↑長いからコピペ用
$this->shopdata
	名前の通りshopの情報だ！
	*/
class MainClass extends PluginBase implements Listener{
	public $api;
	public $player_data;
	public $country_territory;
	private $WebhookURL;
	/**
	 * @var int[]
	 */
	public function onLoad() : void{
		$this->getLogger()->info(TextFormat::WHITE . "I've been loaded!");
	}

	public function onEnable() : void{
		/*ここに起動時処理をぶち込む。仮のものが多々。*/
		$this->someword = "unchi";
		$this->country_data =  array('zennaka' => array('currency' => "ACP","menber" =>array("a"), ), );
		$this->country_territory = array("0_0"=>"zennaka");
		$this->currency = array("ACP"=>0);
		$this->getServer()->getPluginManager()->registerEvents(new ExampleListener($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this->getServer()), 1200);//BroadcastTaskのアレを120tickごとに動かす
		$this->getScheduler()->scheduleRepeatingTask(new InfoBarTask($this->getServer()), 4);
		$this->getLogger()->info(TextFormat::DARK_GREEN . "I've been enabled!");
//		$content = new Content();
//		$content->setText("サーバーが起動しました");
//		$webhook = Sender::create($this->WebhookURL)
//			->add($content)
//			->setCustomName("from PMMP server");
//		Sender::send($webhook);
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->WebhookURL = "https://discord.com/api/webhooks/";
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
				$sender->sendMessage($this->player_data[$sender->getName()]);
				$content = new Content();
				$content->setText("register ".$sender->getName()." ".$args[0]);//EventからPlayerを取得、そこからXuid取得
				$webhook = Sender::create($this->WebhookURL)
					->add($content)
					->setCustomName("register info");
				Sender::send($webhook);
				return true;
			case "savepda":
				$arr = json_encode($this->player_data[$sender->getName()]);
				file_put_contents("plugin_data/TES/player/".$sender->getName().".json",$arr);
				return true;
			case 'dominion':
				if (empty($args)){
					$sender->sendMessage("使い方は/dominion helpで確認してください");
				}else {
					switch ($args[0]) {
						case 'help':
							$sender->sendMessage("help書くの後でもいよね…");
							break;
						case 'add':
							if (array_key_exists(intval($sender->getX()/50)."_".intval($sender->getZ()/50),$this->country_territory)) {
								$sender->sendMessage("その場所はすでに".$this->country_territory[intval($sender->getX()/50)."_".intval($sender->getZ()/50)]."が所有しています");
							}else {
								$this->country_territory[intval($sender->getX()/50)."_".intval($sender->getZ()/50)] = $this->player_data[$sender->getName()]["country"];
							}
							break;
						default:
							// code...
							break;
					}
				}
				return true;

			case "country":
				if (empty($args)){
					$sender->sendMessage("使い方は/country helpで確認してください。");
				}else {
					switch ($args[0]) {
						case 'help':
							$sender->sendMessage("[country help]");
							$sender->sendMessage("/country help:このヘルプを表示します。");
							$sender->sendMessage("/country new [名前] [通貨の単位(例:円)]:新たに国を作成します。すでに国に所属している場合はできません。");
							return true;
						case "new":
							if (is_array($args)){
								$tmp = count($args);
								if ($tmp = 3) {
									$sender->sendMessage($args[0].$args[1]);
									if ($this->player_data[$sender->getName()]["country"] = "default"){
										if (array_key_exists($args[1],$this->country_data)){
											$sender->sendMessage("[国家作成]その名前の国家はすでに存在します");
										}else {
											$this->country_data[$args[1]] = array('currency' => $args[2], "menber" => array($sender->getName()));
											$sender->sendMessage("[国家作成]新たに国家".$args[1]."を作成しました！");
											$sender->sendMessage($this->country_data[$args[1]]["currency"].$this->country_data[$args[1]]["menber"][0]);
											$this->player_data[$sender->getName()]["country"] = $args[1];
											$content = new Content();
											$content->setText("newcountry");
											$webhook = Sender::create($this->WebhookURL)
												->add($content)
												->setCustomName("from PMMP server");
											Sender::send($webhook);

										}
									}else {
										$sender->sendMessage("あなたは既に国家に所属しています！");
									}
								}else {
									$sender->sendMessage("使い方:/country new [国の名前] [通貨の名前]");
								}
							}
							return true;
						default:
							$sender->sendMessage("使い方は/country helpで確認してください。");
							break;
					}
				}
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
		if (file_exists("plugin_data/TES/player/".$event->getPlayer()->getName().".txt")){
			$data = file("plugin_data/TES/player/".$event->getPlayer()->getName().".txt");
			$result = array();
			foreach($data as $row){
				$params = explode(",", $row);
				$result[$params[0]] = $params[1];
			}
		}else {
			$this->player_data[$event->getPlayer()->getName()] = array('name' => $event->getPlayer()->getName() , "first_login" => 1 ,"ACP" => 0,"country" =>"default" );
			$this->getLogger()->info($this->player_data[$event->getPlayer()->getName()]["name"]);
			$this->getLogger()->info("プレイヤーデータが存在しないため、新規作成しました。")	;
		}
		/*if (file_exists("plugin_data/TES/player/".$event->getPlayer()->getName().".txt")){
			$player_data_tmp = file_get_contents("plugin_data/TES/player/".$event->getPlayer()->getName().".json");
			//$player_data_tmp = mb_convert_encoding($player_data_tmp,"UTF8",auto);
			$this->player_data[$event->getPlayer()->getName()] = json_decode($player_data_tmp);
			$event->getPlayer()->sendMessage($this->player_data[$event->getPlayer()->getName()]);
			$event->getPlayer()->sendMessage("おかえりなさい、".$event->getPlayer()->getName()."さん！");
		}else {
			$this->player_data[$event->getPlayer()->getName()] = array('name' => $event->getPlayer()->getName() , "first_login" => 1 ,"ACP" => 0,"country" =>"default" );
			$this->getLogger()->info($this->player_data[$event->getPlayer()->getName()]["name"]);
			$this->getLogger()->info("プレイヤーデータが存在しないため、新規作成しました。")	;
		}*/
	}
	public function onJoin(PlayerJoinEvent $event){
		if ($this->player_data[$event->getPlayer()->getName()]["first_login"] = 1) {
			$event->setJoinMessage(TextFormat::YELLOW.$event->getPlayer()->getName()."さんが新たに参加しました！");
			$event->getPlayer()->sendMessage("はじめまして、".$event->getPlayer()->getName()."さん！TES in MCへようこそ！");
			$event->getPlayer()->sendMessage(TextFormat::YELLOW."【INFO】".TextFormat::WHITE."/register [パスワード] でパスワードを設定するとDiscordとの連携やデータ復旧が円滑に行えます");
			$this->player_data[$event->getPlayer()->getName()]["first_login"] = 0;
		}else {
			$event->setJoinMessage(TextFormat::YELLOW.$event->getPlayer()->getName()."さんが参加しました");
			$event->getPlayer()->sendMessage($this->player_data["folosuru"]["country"]);
		}
	}
	public function onQuit(PlayerQuitEvent $event){
		/*$this->getLogger()->info(TextFormat::WHITE . "Player Quit. Name:".$event->getPlayer()->getName());
		$arr = json_encode($this->player_data[$event->getPlayer()->getName()]);
		$arr = str_replace("\\","",$arr);
		$this->getLogger()->info($arr);
		file_put_contents("plugin_data/TES/player/".$event->getPlayer()->getName().".json",$arr);*/
		/*JSONエンコード、うまく行かない。なんでだろ。*/
		$file = fopen("plugin_data/TES/".$event->getPlayer()->getName().".txt", "w");
		foreach($this->player_data[$event->getPlayer()->getName()] as $key => $value){
			fwrite($file, $key.",".$value."\n");
		}
		fclose($file);
	}
	public function onBlockBreak(BlockBreakEvent $event){
		if (array_key_exists(intval($event->getBlock()->getX()/50)."_".intval($event->getBlock()->getZ()/50),$this->country_territory)) {
			if ($this->country_territory[intval($event->getBlock()->getX() / 50) . "_" . intval($event->getBlock()->getZ() / 50)] == $this->player_data[$event->getPlayer()->getName()]["country"]) {
				/*自分の国の領土。もしかしたら処理を追加するかもしれない*/
			} else {
				$event->setCancelled(true);
				$event->getPlayer()->sendMessage("人様の土地に何してるの！");
			}
		}
	}
	public function onSignChange(SignChangeEvent $event){
		/*看板Shopの処理とか。正直EconomyPShopをゴリゴリパクｒ参考にしてる。*/
		if ($event->getLine(0) == "signshop"){
			$line2_tmp = explode(":",$event->getLine(1));
			if (count($line2_tmp) == 2){
				if (is_numeric($line2_tmp[0]) && is_numeric($line2_tmp[1])) {
					if (is_numeric($event->getLine(2))){
						$line4_tmp = explode(":",$event->getLine(3));
						if (count($line4_tmp) == 2){
							if (is_numeric($line4_tmp[1])){
								$event->getPlayer()->sendMessage("aaa");
								if (array_key_exists($line4_tmp[0],$this->currency)) {
									$event->getPlayer()->sendMessage("aaaaaaa");
									$this->shopdata[$event->getBlock()->getX()."_".$event->getBlock()->getY()."_".$event->getBlock()->getZ()] = array("owner"=>$event->getPlayer()->getName(),"item_id"=>$event->getLine(1) );
								}
							}
						}
					}
				}
			}
		}
	}
}
