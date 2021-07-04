<?php

namespace matze\gommejar\object;

use pocketmine\level\Position;
use pocketmine\math\Vector3;

class JumpAndRun {

    /** @var string */
    private $level;
    /** @var Vector3 */
    private $vector3;

    /**
     * JumpAndRun constructor.
     * @param Vector3 $vector3
     * @param string $level
     */
    public function __construct(Vector3 $vector3, string $level){
        $this->vector3 = $vector3->floor();
        $this->level = $level;
    }

    /**
     * @return Vector3
     */
    public function getVector3(): Vector3{
        return $this->vector3;
    }

    /**
     * @return string
     */
    public function getLevel(): string{
        return $this->level;
    }

    /**
     * @param Position $position
     * @return bool
     */
    public function equals(Position $position): bool {
        if($position->getLevel() === null) return false;
        if($position->getLevel()->getName() !== $this->getLevel()) return false;
        return $this->getVector3()->equals($position->floor());
    }
}