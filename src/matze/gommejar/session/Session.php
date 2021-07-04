<?php

namespace matze\gommejar\session;

use matze\gommejar\jump\JumpType;
use matze\gommejar\jump\JumpTypeManager;
use pocketmine\block\Block;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function array_rand;
use function is_null;
use function mt_rand;

class Session {

    /** @var Player */
    private $player;

    /** @var Vector3|null */
    private $lastVector3 = null;
    /** @var Vector3|null */
    private $targetVector3 = null;

    /** @var int  */
    private $score = 0;

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
        if($position !== null) {
            $level->setBlockIdAt($position->x, $position->y, $position->z, Block::CONCRETE);
        }

        $this->setLastVector3($this->getTargetVector3());

        /** @var JumpType $jumpType */
        $jumpType = JumpTypeManager::getInstance()->getRandomJumpType();
        $position = $jumpType->init($this);
        if(is_null($position)) {
            $this->targetVector3 = null;
            return;
        }
        $this->score++;
        $this->targetVector3 = $position->floor();

        $level->setBlockIdAt($position->x, $position->y, $position->z, Block::CONCRETE_POWDER);
        $level->setBlockDataAt($position->x, $position->y, $position->z, mt_rand(0, 15));
        for($i = 0; $i <= 10; $i++) {
            $tempPosition = $position->floor()->add(0.5, 0.5, 0.5)->add((mt_rand(-10, 10) / 10), (mt_rand(-10, 10) / 10), (mt_rand(-10, 10) / 10));
            $level->addParticle(new HappyVillagerParticle($tempPosition));
        }
    }

    public function destroy(): void {
        $blocks = [];
        if($this->lastVector3 !== null) $blocks[] = $this->lastVector3;
        if($this->targetVector3 !== null) $blocks[] = $this->targetVector3;

        foreach($blocks as $block) {
            $level = $this->getPlayer()->getLevelNonNull();

            $level->addParticle(new DestroyBlockParticle($block, $level->getBlock($block)));
            $level->setBlockIdAt($block->x, $block->y, $block->z, 0);
            $level->setBlockDataAt($block->x, $block->y, $block->z, 0);
        }
    }
}