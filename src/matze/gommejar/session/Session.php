<?php

namespace matze\gommejar\session;

use matze\gommejar\jump\JumpType;
use matze\gommejar\jump\JumpTypeManager;
use matze\gommejar\Loader;
use mysqli;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ryzerbe\core\util\async\AsyncExecutor;
use ryzerbe\statssystem\provider\StatsProvider;
use ryzerbe\statssystem\StatsSystem;
use function mt_rand;

class Session {

    /** @var Player */
    private Player $player;

    /** @var Vector3|null */
    private ?Vector3 $lastVector3 = null;
    /** @var Vector3|null */
    private ?Vector3 $targetVector3 = null;

    /** @var int  */
    private int $score = 0;

    /** @var JumpType|null  */
    private ?JumpType $lastJumpType = null;

    /**
     * Session constructor.
     * @param Player $player
     */
    public function __construct(Player $player){
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player{
        return $this->player;
    }

    /**
     * @return Vector3|null
     */
    public function getTargetVector3(): ?Vector3{
        return $this->targetVector3;
    }

    /**
     * @return Vector3|null
     */
    public function getLastVector3(): ?Vector3{
        return $this->lastVector3;
    }

    /**
     * @return int
     */
    public function getScore(): int{
        return $this->score;
    }

    /**
     * @param Vector3|null $lastVector3
     */
    public function setLastVector3(?Vector3 $lastVector3): void{
        $this->lastVector3 = $lastVector3;
    }

    public function initBlockPosition(): void {
        $player = $this->getPlayer();
        $level = $player->getLevelNonNull();

        $lastVector3 = $this->getLastVector3();
        if($lastVector3 !== null) {
            $level->addParticle(new DestroyBlockParticle($lastVector3, $level->getBlock($lastVector3)));
            $level->setBlockIdAt($lastVector3->x, $lastVector3->y, $lastVector3->z, 0);
            $level->setBlockDataAt($lastVector3->x, $lastVector3->y, $lastVector3->z, 0);
        }
        $position = $this->getTargetVector3();
        if($position !== null && $this->lastJumpType !== null) {
            $level->setBlockIdAt($position->x, $position->y, $position->z, $this->lastJumpType->getSucceedBlock()->getId());
            if(!$this->lastJumpType->ignoreSucceedBlockMeta()) {
                $level->setBlockDataAt($position->x, $position->y, $position->z, $this->lastJumpType->getSucceedBlock()->getDamage());
            }
        }

        $this->setLastVector3($this->getTargetVector3());

        $this->lastJumpType = $jumpType = JumpTypeManager::getInstance()->getRandomJumpType($this);
        $position = $jumpType->init($this);
        if($position === null) {
            $this->targetVector3 = null;
            return;
        }
        $this->score++;
        $this->targetVector3 = $position = $position->floor();

        $block = $jumpType->getTargetBlock();
        $level->setBlockIdAt($position->x, $position->y, $position->z, $block->getId());
        $level->setBlockDataAt($position->x, $position->y, $position->z, $block->getDamage());
        for($i = 0; $i <= 10; $i++) {
            $tempPosition = $position->floor()->add(0.5, 0.5, 0.5)->add((mt_rand(-10, 10) / 10), (mt_rand(-10, 10) / 10), (mt_rand(-10, 10) / 10));
            $level->addParticle(new HappyVillagerParticle($tempPosition));
        }
    }

    public function destroy(): void {
        $blocks = [];
        if($this->lastVector3 !== null) $blocks[] = $this->lastVector3;
        if($this->targetVector3 !== null) $blocks[] = $this->targetVector3;

        $player = $this->getPlayer();
        foreach($blocks as $block) {
            $level = $player->getLevelNonNull();

            $level->addParticle(new DestroyBlockParticle($block, $level->getBlock($block)));
            $level->setBlockIdAt($block->x, $block->y, $block->z, 0);
            $level->setBlockDataAt($block->x, $block->y, $block->z, 0);
        }

        $score = $this->getScore();
        $playername = $player->getName();
        AsyncExecutor::submitMySQLAsyncTask(StatsSystem::DATABASE, function(mysqli $mysqli) use ($score, $playername): void {
            $statistics = StatsProvider::getStatistics($mysqli, $playername, Loader::STATS_CATEGORY);
            if($statistics === null) {
                StatsProvider::updateStatistic($mysqli, $playername, Loader::STATS_CATEGORY, "score", $score, false);
                StatsProvider::updateStatistic($mysqli, $playername, Loader::STATS_CATEGORY, "m_score", $score, false);
            } else {
                StatsProvider::checkMonthlyStatistic($mysqli, $playername, Loader::STATS_CATEGORY);

                if($statistics["score"] < $score) StatsProvider::updateStatistic($mysqli, $playername, Loader::STATS_CATEGORY, "score", $score, false);
                if($statistics["m_score"] < $score) StatsProvider::updateStatistic($mysqli, $playername, Loader::STATS_CATEGORY, "m_score", $score, false);
            }
        });
    }
}