<?php

namespace matze\gommejar;

use baubolp\core\provider\AsyncExecutor;
use matze\gommejar\jump\JumpTypeManager;
use matze\gommejar\listener\BlockUpdateListener;
use matze\gommejar\listener\PlayerMoveListener;
use matze\gommejar\listener\PlayerQuitListener;
use matze\gommejar\object\JumpAndRun;
use matze\gommejar\session\SessionManager;
use mysqli;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;

class Loader extends PluginBase {

    public const STATS_CATEGORY = "jumpAndRun";

    /** @var Loader|null */
    private static ?Loader $instance = null;

    /** @var array  */
    private array $jumpAndRuns = [];

    public function onEnable(): void{
        self::$instance = $this;

        $this->saveResource("/jump_and_runs.json");

        $this->initListener();
        $this->initJumpAndRuns();

        SessionManager::getInstance();
        JumpTypeManager::getInstance();

        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli): void {
            StatsProvider::createCategory($mysqli, Loader::STATS_CATEGORY, [
                "score" => "INT"
            ], [
                "score" => 0
            ]);
        });
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
            new BlockUpdateListener(),
            new PlayerQuitListener()
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