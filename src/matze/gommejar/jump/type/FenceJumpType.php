<?php

namespace matze\gommejar\jump\type;

use matze\gommejar\jump\JumpType;
use matze\gommejar\session\Session;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use function array_rand;

class FenceJumpType extends JumpType {

    /**
     * @return int
     */
    public function getChance(): int{
        return 50;
    }

    /**
     * @param Session $session
     * @return Vector3|null
     */
    public function init(Session $session): ?Vector3{
        $sides = [-3, -2, 2, 3];
        $player = $session->getPlayer();
        $level = $player->getLevelNonNull();

        $tries = 100;
        $position = new Vector3($player->x + $sides[array_rand($sides)], ($session->getLastVector3() !== null ? $session->getLastVector3()->y : $player->y - 1), $player->z + $sides[array_rand($sides)]);
        while((
                $level->getBlock($position)->getId() !== 0 ||
                $level->getBlock($position->add(0, 1))->getId() !== 0 ||
                $level->getBlock($position->add(0, 2))->getId() !== 0 ||
                $position->y >= 240
            ) && $tries > 0
        ) {
            $position = $position->setComponents($player->x + $sides[array_rand($sides)], ($session->getLastVector3() !== null ? $session->getLastVector3()->y : $player->y - 1), $player->z + $sides[array_rand($sides)]);
            $tries--;
        }
        if($tries <= 0) return null;
        return $position;
    }

    /**
     * @return Block
     */
    public function getTargetBlock(): Block{
        return Block::get(Block::FENCE);
    }

    /**
     * @return Block
     */
    public function getSucceedBlock(): Block{
        return Block::get(Block::FENCE, 1);
    }

    /**
     * @return int
     */
    public function getMinScore(): int{
        return 10;
    }
}