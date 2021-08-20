<?php

namespace matze\gommejar\jump;

use matze\gommejar\jump\type\DefaultJumpType;
use matze\gommejar\jump\type\FenceJumpType;
use matze\gommejar\jump\type\GlassPaneJumpType;
use matze\gommejar\Loader;
use matze\gommejar\session\Session;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use function array_rand;

class JumpTypeManager {
    use SingletonTrait;

    /** @var array  */
    private array $jumpTypes = [];

    /**
     * JumpTypeManager constructor.
     */
    public function __construct(){
        $types = [
            new DefaultJumpType(),
            new FenceJumpType(),
            new GlassPaneJumpType()
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
     * @param Session $session
     * @return JumpType
     */
    public function getRandomJumpType(Session $session): JumpType {
        $jumpType = $this->jumpTypes[array_rand($this->jumpTypes)];
        while($jumpType->getMinScore() > $session->getScore()) {
            $jumpType = $this->jumpTypes[array_rand($this->jumpTypes)];
        }
        return $jumpType;
    }
}