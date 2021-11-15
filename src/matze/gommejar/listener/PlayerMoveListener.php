<?php

namespace matze\gommejar\listener;

use matze\gommejar\Loader;
use matze\gommejar\session\Session;
use matze\gommejar\session\SessionManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\TextFormat;

class PlayerMoveListener implements Listener {

    /**
     * @param PlayerMoveEvent $event
     */
    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        /** @var Session|null $session */
        $session = SessionManager::getInstance()->getSession($player);
        if($session === null){
            foreach(Loader::getInstance()->getJumpAndRuns() as $jumpAndRun) {
                if(!$jumpAndRun->equals($player)) continue;
                /** @var Session $session */
                $session = SessionManager::getInstance()->addSession($player);
                $player->playSound("respawn_anchor.deplete", 1, 1, [$player]);

                $session->initBlockPosition();
                break;
            }
            return;
        }

        if($session->getTargetVector3() === null) {
            $session->initBlockPosition();
            return;
        }
        if($player->isOnGround() && $player->floor()->subtract(0, 1)->equals($session->getTargetVector3())){
            $player->playSound("random.orb", 1, 1, [$player]);
            $session->initBlockPosition();
            $player->sendActionBarMessage(TextFormat::GOLD."Score: ".TextFormat::WHITE.$session->getScore());
        }

        if($player->y <= ($session->getTargetVector3()->y - 1)) {
            $player->sendMessage("§8§l»§r Score: " . $session->getScore());
            $player->playSound("block.false_permissions", 1, 1, [$player]);
            SessionManager::getInstance()->destroySession($session);
        }
    }
}