<?php

namespace matze\gommejar\listener;

use matze\gommejar\session\Session;
use matze\gommejar\session\SessionManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class PlayerQuitListener implements Listener {

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        /** @var Session|null $session */
        $session = SessionManager::getInstance()->getSession($player);
        if($session === null) return;

        SessionManager::getInstance()->destroySession($session);
    }
}