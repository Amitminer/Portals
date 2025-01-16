<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals;

use BrahmjotSingh0\Portals\Utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\math\Vector3;
use SOFe\AwaitGenerator\Await;
use Generator;

class EventListener implements Listener
{
    private Portals $plugin;

    /** @var array<string, bool> */
    private array $notifiedPlayers = [];

    public function __construct(Portals $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Handles the PlayerMoveEvent to check if a player enters a portal.
     */
    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $position = $player->getPosition();

        Await::f2c(function () use ($player, $position): Generator {
            $data = yield from $this->plugin->getDatabaseManager()->fetchPortalsByOwner($player->getName());

            foreach ($data as $portal) {
                $portalData = $portal['data'];

                if (!isset($portalData["pos1"], $portalData["pos2"], $portalData["worldName"])) {
                    continue;
                }

                if ($player->getWorld()->getFolderName() !== $portalData["worldName"]) {
                    continue;
                }

                $pos1 = new Vector3($portalData["pos1"]["x"], $portalData["pos1"]["y"], $portalData["pos1"]["z"]);
                $pos2 = new Vector3($portalData["pos2"]["x"], $portalData["pos2"]["y"], $portalData["pos2"]["z"]);

                if (Utils::isWithinBounds($position, $pos1, $pos2)) {
                    if (!isset($this->notifiedPlayers[$player->getName()])) {
                        $this->notifiedPlayers[$player->getName()] = true;

                        if (isset($portalData["message"])) {
                            $player->sendMessage($portalData["message"]);
                        }

                        if (isset($portalData["cmd"]) && $portalData["cmd"] !== "") {
                            Utils::executeCommand($player, $portalData["cmd"]);
                        }
                    }
                } else {
                    unset($this->notifiedPlayers[$player->getName()]);
                }
            }
        });
    }

    /**
     * Handles the BlockBreakEvent to set portal positions.
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $playerName = $player->getName();

        $state = $this->plugin->getPortalManager()->getPlayerState($playerName);
        if ($state === null) {
            return;
        }

        $block = $event->getBlock();
        $position = $block->getPosition();

        $posData = [
            'x' => $position->getX(),
            'y' => $position->getY(),
            'z' => $position->getZ()
        ];

        if ($state['step'] === 'pos1') {
            $state['pos1'] = $posData;
            $state['step'] = 'pos2';
            $this->plugin->getPortalManager()->setPlayerState($playerName, $state);
            $player->sendMessage("Position 1 set. Please break another block to set position 2.");
            $event->cancel();
        } elseif ($state['step'] === 'pos2') {
            $state['pos2'] = $posData;
            $this->plugin->getPortalManager()->setPlayerState($playerName, null);

            Await::f2c(function () use ($state, $player): Generator {
                $worldName = $player->getWorld()->getFolderName();
                yield from $this->plugin->getDatabaseManager()->addPortal(
                    $player->getName(),
                    $state['portalName'],
                    [
                        'worldName' => $worldName,
                        'pos1' => $state['pos1'],
                        'pos2' => $state['pos2']
                    ]
                );
                $player->sendMessage("Portal '{$state['portalName']}' created successfully with the selected positions.");
            });

            $event->cancel();
        }
    }
}