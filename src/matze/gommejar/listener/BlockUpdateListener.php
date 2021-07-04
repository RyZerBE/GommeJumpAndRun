<?php

namespace matze\gommejar\listener;

use matze\gommejar\session\Session;
use matze\gommejar\session\SessionManager;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;

class BlockUpdateListener implements Listener {

    /**
     * @param BlockUpdateEvent $event
     */
    public function onBlockUpdate(BlockUpdateEvent $event): void {
        $block = $event->getBlock();

        /** @var Session $session */
        foreach(SessionManager::getInstance()->getSessions() as $session) {
            if($session->getTargetVector3()->equals($block->floor())) {
                $event->setCancelled();
                break;
            }
        }
    }
}