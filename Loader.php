<?php

namespace VitalHCF;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use VitalHCF\provider\{
    SQLite3Provider, YamlProvider, MysqlProvider,
};
use VItalHCF\player\{
    Player,
};
use VitalHCF\API\{
    Scoreboards,
};
use VitalHCF\Task\{
	BardTask, ArcherTask,
};
use VitalHCF\Task\event\{
	FactionTask,
};
use VitalHCF\Task\updater\{
    NinjaShearUpdaterTask,
};
use VitalHCF\listeners\{
	Listeners,
};
use VitalHCF\commands\{
    Commands,
};
use VitalHCF\item\{
    Items,
};
use VitalHCF\block\{
    Blocks,
};
use VitalHCF\entities\{
    Entitys,
};
use VitalHCF\enchantments\{
    Enchantments,
};
class Loader extends PluginBase {
    
    /** @var Loader */
    protected static $instance;
    
    /** @var Array[] */
    public static $appleenchanted = [];
    
    /** @var Array[] */
	public $permission = [];
    
    /**
     * @return void
     */
    public function onLoad() : void {
        self::$instance = $this;
    }
    
    /**
     * @return void
     */
    public function onEnable() : void {
        MysqlProvider::connect();
        SQLite3Provider::connect();

        Listeners::init();
        Commands::init();
        Items::init();
        Blocks::init();
        Entitys::init();
        Enchantments::init();
        
        YamlProvider::init();
        
        Factions::init();

        $this->getScheduler()->scheduleRepeatingTask(new BardTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new ArcherTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new FactionTask(), 5 * 60 * 40);
    }
    
    /**
     * @return void
     */
    public function onDisable() : void {
        SQLite3Provider::disconnect();
        MysqlProvider::disconnect();

        YamlProvider::save();
    }

    /**
     * @return Loader
     */
    public static function getInstance() : Loader {
        return self::$instance;
    }

    /**
     * @return SQLite3Provider
     */
    public static function getProvider() : SQLite3Provider {
        return new SQLite3Provider();
    }

    /**
     * @return Scoreboards
     */
	public static function getScoreboard() : Scoreboards {
		return new Scoreboards();
    }

    /**
     * @param String $configuration
     */
    public static function getDefaultConfig($configuration){
        return self::getInstance()->getConfig()->get($configuration);
    }
    
    /**
     * @param String $configuration
     */
    public static function getConfiguration($configuration){
    	return new Config(self::getInstance()->getDataFolder()."{$configuration}.yml", Config::YAML);
    }

    /**
     * @param Player $player
     */
    public function getPermission(Player $player){
        if(!isset($this->permission[$player->getName()])){
            $this->permission[$player->getName()] = $player->addAttachment($this);
        }
        return $this->permission[$player->getName()];
    }
}

?>