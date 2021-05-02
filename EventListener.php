<?php

namespace VitalHCF;

use VitalHCF\{Loader, Factions};
use VitalHCF\player\{Player, PlayerBase};

use VitalHCF\Task\asynctask\{LoadPlayerData, SavePlayerData};

use VitalHCF\Task\Scoreboard;

use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TE;
use pocketmine\level\biome\Biome;

use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerChatEvent, PlayerMoveEvent, PlayerInteractEvent};
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;

use pocketmine\network\mcpe\protocol\LevelEventPacket;

class EventListener implements Listener {

    /**
     * EventListener Constructor.
     */
    public function __construct(){
		
    }
    
    /**
     * @param PlayerCreationEvent $event
     * @return void
     */
    public function onPlayerCreationEvent(PlayerCreationEvent $event) : void {
        $event->setPlayerClass(Player::class, true);
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $event->setJoinMessage(TE::GRAY."[".TE::GREEN."+".TE::GRAY."] ".TE::GREEN.$player->getName().TE::GRAY." entered the server!");
        
        PlayerBase::create($player->getName());
		Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new Scoreboard($player), 20);
        Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new LoadPlayerData($player->getName(), $player->getUniqueId()->toString(), Loader::getDefaultConfig("MySQL")["hostname"], Loader::getDefaultConfig("MySQL")["username"], Loader::getDefaultConfig("MySQL")["password"], Loader::getDefaultConfig("MySQL")["database"], Loader::getDefaultConfig("MySQL")["port"]));
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onPlayerQuitEvent(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
		$event->setQuitMessage(TE::GRAY."[".TE::RED."-".TE::GRAY."] ".TE::RED.$player->getName().TE::GRAY." left the server!");

        Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new SavePlayerData($player->getName(), $player->getUniqueId()->toString(), $player->getClientId(), $player->getCountry(), $player->getAddress(), Factions::inFaction($player->getName()) ? Factions::getFaction($player->getName()) : "This player not have faction", Loader::getDefaultConfig("MySQL")["hostname"], Loader::getDefaultConfig("MySQL")["username"], Loader::getDefaultConfig("MySQL")["password"], Loader::getDefaultConfig("MySQL")["database"], Loader::getDefaultConfig("MySQL")["port"]));
        if($player instanceof Player){
            $player->removePermissionsPlayer();
		}
	}
	
	/**
     * @param EntityLevelChangeEvent $event
     * @return void
     */
	public function onEntityLevelChangeEvent(EntityLevelChangeEvent $event) : void {
		$player = $event->getEntity();
		$player->showCoordinates();
	}
    
    /**
     * @param PlayerChatEvent $event
     * @return void
     */
    public function onPlayerChatEvent(PlayerChatEvent $event) : void {
    	$player = $event->getPlayer();
    	$format = null;
    	if($player->getRank() === null||$player->getRank() === "Guest"){
    		$format = TE::GRAY."[".TE::GREEN."Guest".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Owner"){
    		$format = TE::GRAY."[".TE::DARK_RED."Owner".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Co-Owner"){
    		$format = TE::GRAY."[".TE::RED."Co-Owner".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Admin"){
    		$format = TE::GRAY."[".TE::DARK_AQUA."Admin".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Sr-Mod"){
    		$format = TE::GRAY."[".TE::DARK_PURPLE."Sr-Mod".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Mod"){
    		$format = TE::GRAY."[".TE::LIGHT_PURPLE."Mod".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Jr-Admin"){
    		$format = TE::GRAY."[".TE::GREEN."Jr-Admin".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Sr-Admin"){
    		$format = TE::GRAY."[".TE::AQUA."Sr-Admin".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Trainee"){
    		$format = TE::GRAY."[".TE::YELLOW."Trainee".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Developer"){
    		$format = TE::GRAY."[".TE::DARK_AQUA."Developer".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "VitalHero"){
    		$format = TE::GRAY."[".TE::AQUA."Vital".TE::GOLD."Hero".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "VitalHero+"){
    		$format = TE::GRAY."[".TE::OBFUSCATED.TE::LIGHT_PURPLE."!!".TE::RESET.TE::AQUA."Vital".TE::GOLD."Hero".TE::AQUA."+".TE::GRAY.TE::OBFUSCATED.TE::LIGHT_PURPLE."!!".TE::RESET.TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Demon"){
    		$format = TE::GRAY."[".TE::OBFUSCATED.TE::LIGHT_PURPLE."!!".TE::RESET.TE::DARK_RED."Demon".TE::GRAY.TE::OBFUSCATED.TE::LIGHT_PURPLE."!!".TE::RESET.TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
		}
		if($player->getRank() === "Angelic"){
    		$format = TE::GRAY."[".TE::OBFUSCATED.TE::WHITE."!!".TE::RESET.TE::AQUA."Angelic".TE::GRAY.TE::OBFUSCATED.TE::WHITE."!!".TE::RESET.TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "NitroBooster"){
    		$format = TE::GRAY."[".TE::LIGHT_PURPLE."NitroBooster".TE::RESET.TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Monster"){
    		$format = TE::GRAY."[".TE::DARK_GREEN."Monster".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Partner"){
    		$format = TE::GRAY."[".TE::OBFUSCATED.TE::AQUA."!!".TE::RESET.TE::YELLOW."Partner".TE::OBFUSCATED.TE::AQUA."!!".TE::RESET.TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "MiniYT"){
    		$format = TE::GRAY."[".TE::WHITE."Mini".TE::RED."YT".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "YouTuber"){
    		$format = TE::GRAY."[".TE::WHITE."You".TE::RED."Tuber".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Famous"){
    		$format = TE::GRAY."[".TE::LIGHT_PURPLE."Famous".TE::GRAY."] ".TE::LIGHT_PURPLE.$player->getName().TE::WHITE;
    	}
    	if($player->getRank() === "Twitch"){
    		$format = TE::GRAY."[".TE::LIGHT_PURPLE."Twitch".TE::GRAY."] ".TE::GRAY.$player->getName().TE::WHITE;
    	}
    	if(Factions::inFaction($player->getName())){
			$factionName = Factions::getFaction($player->getName());
			$event->setFormat(TE::GOLD."[".TE::RED.$factionName.TE::GOLD."]".TE::RESET.$format.": ".$event->getMessage());
		}else{
			$event->setFormat($format.": ".$event->getMessage());
		}
	}
}

?>