<?php

namespace matze\gommejar\jump;

use matze\gommejar\jump\type\DefaultJumpType;
use matze\gommejar\Loader;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use function array_rand;

class JumpTypeManager {
    use SingletonTrait;

    /** @var array  */
    private $jumpTypes = [];

    /**
     * JumpTypeManager constructor.
     */
    public function __construct(){
        $types = [
            new DefaultJumpType()
        ];
        foreach($types as $type) {
            $this->registerJumpType($type);
        }

        if($this->jumpTypes === []) {
            Server::getInstance()->getPluginManager()->disablePlugin(Loader::getInstance());
        }
    }

    /**
     * @param JumpType $jumpType
     */
    public function registerJumpType(JumpType $jumpType): void {
        for($i = 0; $i <= $jumpType->getChance(); $i++) {
            $this->jumpTypes[] = $jumpType;
        }
    }

    /**
     * @return JumpType
     */
    public function getRandomJumpType(): JumpType {
        return $this->jumpTypes[array_rand($this->jumpTypes)];
    }
}