<?php

namespace matze\gommejar;

use matze\gommejar\jump\JumpTypeManager;
use matze\gommejar\listener\BlockUpdateListener;
use matze\gommejar\listener\PlayerMoveListener;
use matze\gommejar\object\JumpAndRun;
use matze\gommejar\session\SessionManager;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;

class Loader extends PluginBase {

    /** @var Loader|null */
    private static $instance = null;

    /** @var array  */
    private $jumpAndRuns = [];

    public function onEnable(): void{
        self::$instance = $this;

        $this->saveResource("/jump_and_runs.json");

        $this->initListener();
        $this->initJumpAndRuns();

        SessionManager::getInstance();
        JumpTypeManager::getInstance();
    }

    /**
     * @return Loader|null
     */
    public static function getInstance(): ?Loader{
        return self::$instance;
    }

    /**
     * @return JumpAndRun[]
     */
    public function getJumpAndRuns(): array {
        return $this->jumpAndRuns;
    }

    private function initListener(): void {
        $listeners = [
            new PlayerMoveListener(),
            new BlockUpdateListener()
        ];
        foreach($listeners as $listener) {
            Server::getInstance()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    private function initJumpAndRuns(): void {
        $config = new Config($this->getDataFolder() . "/jump_and_runs.json");
        foreach($config->getAll() as $key => $value) {
            $this->jumpAndRuns[] = new JumpAndRun(new Vector3($value["X"], $value["Y"], $value["Z"]), $value["Level"]);
        }
    }
}