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

/**
 * Handles events related to portals, such as player movement and block breaking.
 */
class EventListener implements Listener
{
    /** @var Portals The plugin instance. */
    private Portals $plugin;

    /** @var array<string, bool> Tracks players who have been notified about entering a portal. */
    private array $notifiedPlayers = [];

    /**
     * Initializes the EventListener with the plugin instance.
     *
     * @param Portals $plugin The plugin instance.
     */
    public function __construct(Portals $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Handles the PlayerMoveEvent to check if a player enters a portal.
     *
     * @param PlayerMoveEvent $event The PlayerMoveEvent instance.
     */
    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $position = $player->getPosition();

        Await::f2c(function () use ($player, $position): Generator {
            // Fetch portals owned by the player
            $data = yield from $this->plugin->getDatabaseManager()->fetchPortalsByOwner($player->getName());

            foreach ($data as $portal) {
                $portalData = $portal['data'];

                // Skip if required portal data is missing
                if (!isset($portalData["pos1"], $portalData["pos2"], $portalData["worldName"])) {
                    continue;
                }

                // Skip if the player is not in the correct world
                if ($player->getWorld()->getFolderName() !== $portalData["worldName"]) {
                    continue;
                }

                // Convert portal positions to Vector3
                $pos1 = new Vector3($portalData["pos1"]["x"], $portalData["pos1"]["y"], $portalData["pos1"]["z"]);
                $pos2 = new Vector3($portalData["pos2"]["x"], $portalData["pos2"]["y"], $portalData["pos2"]["z"]);

                // Check if the player is within the portal bounds
                if (Utils::isWithinBounds($position, $pos1, $pos2)) {
                    // Notify the player if they haven't been notified already
                    if (!isset($this->notifiedPlayers[$player->getName()])) {
                        $this->notifiedPlayers[$player->getName()] = true;

                        // Send a message if one is set
                        if (isset($portalData["message"])) {
                            $player->sendMessage($portalData["message"]);
                        }

                        // Execute a command if one is set
                        if (isset($portalData["cmd"]) && $portalData["cmd"] !== "") {
                            Utils::executeCommand($player, $portalData["cmd"]);
                        }
                    }
                } else {
                    // Remove the player from the notified list if they leave the portal bounds
                    unset($this->notifiedPlayers[$player->getName()]);
                }
            }
        });
    }

    /**
     * Handles the BlockBreakEvent to set portal positions.
     *
     * @param BlockBreakEvent $event The BlockBreakEvent instance.
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $playerName = $player->getName();

        // Get the player's current state for portal creation
        $state = $this->plugin->getPortalManager()->getPlayerState($playerName);
        if ($state === null) {
            return;
        }

        // Get the position of the broken block
        $block = $event->getBlock();
        $position = $block->getPosition();

        // Prepare position data
        $posData = [
            'x' => $position->getX(),
            'y' => $position->getY(),
            'z' => $position->getZ()
        ];

        // Handle setting position 1
        if ($state['step'] === 'pos1') {
            $state['pos1'] = $posData;
            $state['step'] = 'pos2';
            $this->plugin->getPortalManager()->setPlayerState($playerName, $state);
            $player->sendMessage("Position 1 set. Please break another block to set position 2.");
            $event->cancel();
        }
        // Handle setting position 2 and creating the portal
        elseif ($state['step'] === 'pos2') {
            $state['pos2'] = $posData;
            $this->plugin->getPortalManager()->setPlayerState($playerName, null);

            // Save the portal to the database
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