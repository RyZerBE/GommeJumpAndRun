<?php

namespace matze\gommejar\session;

use pocketmine\Player;
use pocketmine\utils\SingletonTrait;

class SessionManager {
    use SingletonTrait;

    /** @var array  */
    private $sessions = [];

    /**
     * @return Session[]
     */
    public function getSessions(): array{
        return $this->sessions;
    }

    /**
     * @param Player|string $player
     * @return Session|null
     *
     */
    public function getSession($player): ?Session {
        if($player instanceof Player) $player = $player->getName();
        return $this->sessions[$player] ?? null;
    }

    /**
     * @param Player $player
     * @return Session
     */
    public function addSession(Player $player): Session {
        $this->sessions[$player->getName()] = ($session = new Session($player));
        return $session;
    }

    /**
     * @param Session $session
     */
    public function destroySession(Session $session): void {
        unset($this->sessions[$session->getPlayer()->getName()]);
        $session->destroy();
    }
}