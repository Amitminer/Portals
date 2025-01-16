<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands\SubCommands;

use BrahmjotSingh0\Portals\Portals;
use BrahmjotSingh0\Portals\Utils\Permissions;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

/**
 * Subcommand for creating a new portal.
 * Allows players to define a portal by setting two positions.
 */
class CreateSubCommand extends BaseSubCommand
{
    /**
     * Prepares the subcommand by setting permissions, arguments, and usage.
     */
    protected function prepare(): void
    {
        $this->setPermission(Permissions::CREATE);
        $this->registerArgument(0, new RawStringArgument('portalname'));
        $this->setUsage("Usage: /portal create <portalname>");
    }

    /**
     * Executes the subcommand to create a new portal.
     *
     * @param CommandSender $sender The command sender.
     * @param string $aliasUsed The alias used to execute the command.
     * @param array $args The arguments passed to the command.
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        // Ensure the command is executed by a player
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return;
        }

        /** @var Portals $plugin */
        $plugin = $this->getOwningPlugin();
        $manager = $plugin->getPortalManager();

        $portalName = $args['portalname'];

        // Check if the portal already exists
        Await::f2c(function () use ($plugin, $manager, $sender, $portalName): \Generator {
            $isExists = yield from $manager->isPortalExists($portalName);
            if ($isExists) {
                $sender->sendMessage("Portal '$portalName' already exists.");
                return;
            }

            // Store the player's state for portal creation
            $plugin->getPortalManager()->setPlayerState($sender->getName(), [
                'portalName' => $portalName,
                'step' => 'pos1'
            ]);
            $sender->sendMessage("Please break a block to set position 1 for portal '$portalName'.");
        });
    }
}