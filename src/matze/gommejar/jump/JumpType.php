<?php

namespace matze\gommejar\jump;

use matze\gommejar\session\Session;
use pocketmine\math\Vector3;

abstract class JumpType {
    abstract public function getChance(): int;
    abstract public function init(Session $session): ?Vector3;
}