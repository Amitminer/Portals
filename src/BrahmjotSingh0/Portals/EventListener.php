<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals;

use BrahmjotSingh0\Portals\Utils\Utils;
use BrahmjotSingh0\Portals\Manager\CacheManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\math\Vector3;
use SOFe\AwaitGenerator\Await;
use Generator;
use pocketmine\player\Player;
use pocketmine\world\Position;

/**
 * Handles events related to portals, such as player movement and block breaking.
 */
class EventListener implements Listener
{
    /** @var Portals The plugin instance. */
    private Portals $plugin;

    /** @var array<string, bool> Tracks players who have been notified about entering a portal. */
    private array $notifiedPlayers = [];
    
    /** @var CacheManager The cache manager for portal data. */
    private CacheManager $cacheManager;

    /**
     * Initializes the EventListener with the plugin instance.
     *
     * @param Portals $plugin The plugin instance.
     * @param CacheManager $cacheManager The cache manager instance.
     */
    public function __construct(Portals $plugin, CacheManager $cacheManager)
    {
        $this->plugin = $plugin;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Handles the PlayerMoveEvent to check if a player enters a portal.
     * Only processes the event if the player has moved to a different block.
     *
     * @param PlayerMoveEvent $event The PlayerMoveEvent instance.
     */
    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        // Check if the player has actually moved to a different block
        $from = $event->getFrom();
        $to = $event->getTo();
        if ((int)$from->getX() === (int)$to->getX() &&(int)$from->getY() === (int)$to->getY() &&(int)$from->getZ() === (int)$to->getZ()
        ) {
            return;
        }
        
        $player = $event->getPlayer();
        $this->processPortalInteraction($player, $player->getPosition());
    }
    
    /**
     * Processes a player's interaction with portals.
     * 
     * @param Player $player The player to check
     * @param Position $position The player's position
     */
    private function processPortalInteraction(Player $player, Position $position): void
    {
        $playerName = $player->getName();
        $worldName = $player->getWorld()->getFolderName();
        
        // Get portals for the player's current world
        $relevantPortals = $this->cacheManager->getPortalsByWorld($worldName);
        
        foreach ($relevantPortals as $portal) {
            $portalData = $portal['data'];

            // Skip if required portal data is missing
            if (!isset($portalData["pos1"], $portalData["pos2"])) {
                continue;
            }

            // Convert portal positions to Vector3
            $pos1 = new Vector3($portalData["pos1"]["x"], $portalData["pos1"]["y"], $portalData["pos1"]["z"]);
            $pos2 = new Vector3($portalData["pos2"]["x"], $portalData["pos2"]["y"], $portalData["pos2"]["z"]);

            // Check if the player is within the portal bounds
            if (Utils::isWithinBounds($position, $pos1, $pos2)) {
                // If player is not already notified
                if (!isset($this->notifiedPlayers[$playerName])) {
                    $this->notifiedPlayers[$playerName] = true;

                    // Send a message if one is set
                    if (isset($portalData["message"]) && $portalData["message"] !== "") {
                        $player->sendMessage($portalData["message"]);
                    }

                    // Execute a command if one is set
                    if (isset($portalData["cmd"]) && $portalData["cmd"] !== "") {
                        Utils::executeCommand($player, $portalData["cmd"]);
                    }
                }
                
                // Player is in at least one portal, no need to check other portals
                return;
            }
        }
        
        // If we get here, player isn't in any portal
        unset($this->notifiedPlayers[$playerName]);
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

        // Prepare position data directly
        $posData = [
            'x' => $position->getX(),
            'y' => $position->getY(),
            'z' => $position->getZ(),
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
                $portalData = [
                    'worldName' => $worldName,
                    'pos1' => $state['pos1'],
                    'pos2' => $state['pos2']
                ];
                
                $newPortal = yield from $this->plugin->getDatabaseManager()->addPortal(
                    $player->getName(),
                    $state['portalName'],
                    $portalData
                );
                
                // Add the new portal to the cache
                if ($newPortal !== null) {
                    $this->cacheManager->addPortal($newPortal);
                }
                
                $player->sendMessage("Portal '{$state['portalName']}' created successfully with the selected positions.");
            });

            $event->cancel();
        }
    }
}