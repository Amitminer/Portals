<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Utils;

use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

/**
 * Provides utility methods for portal-related operations.
 */
class Utils
{
    /**
     * Checks if a position is within the bounds of a portal.
     *
     * @param Vector3 $position The position to check.
     * @param Vector3 $pos1 The first corner of the portal bounds.
     * @param Vector3 $pos2 The second corner of the portal bounds.
     * @return bool True if the position is within the bounds, false otherwise.
     */
    public static function isWithinBounds(Vector3 $position, Vector3 $pos1, Vector3 $pos2): bool
    {
        $minX = min($pos1->getX(), $pos2->getX());
        $maxX = max($pos1->getX(), $pos2->getX());
        $minY = min($pos1->getY(), $pos2->getY());
        $maxY = max($pos1->getY(), $pos2->getY());
        $minZ = min($pos1->getZ(), $pos2->getZ());
        $maxZ = max($pos1->getZ(), $pos2->getZ());

        $margin = 0.5;
        $withinX = ($position->getX() >= ($minX - $margin) && $position->getX() <= ($maxX + $margin));
        $withinY = ($position->getY() >= ($minY - $margin) && $position->getY() <= ($maxY + $margin));
        $withinZ = ($position->getZ() >= ($minZ - $margin) && $position->getZ() <= ($maxZ + $margin));

        return ($withinX && $withinY && $withinZ);
    }

    /**
     * Executes a command as the player.
     *
     * @param Player $player The player executing the command.
     * @param string $commandString The command(s) to execute, separated by commas.
     */
    public static function executeCommand(Player $player, string $commandString): void
    {
        $server = Server::getInstance();
        $commands = explode(',', $commandString);

        foreach ($commands as $command) {
            $command = trim($command);
            $server->dispatchCommand($player, $command);
        }
    }
}