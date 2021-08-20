<?php

namespace matze\gommejar\jump;

use matze\gommejar\session\Session;
use pocketmine\block\Block;
use pocketmine\math\Vector3;

abstract class JumpType {
    abstract public function getChance(): int;
    abstract public function init(Session $session): ?Vector3;
    abstract public function getTargetBlock(): Block;
    abstract public function getSucceedBlock(): Block;

    /**
     * @return bool
     */
    public function ignoreSucceedBlockMeta(): bool {
        return false;
    }

    /**
     * @return int
     */
    public function getMinScore(): int {
        return 0;
    }
}