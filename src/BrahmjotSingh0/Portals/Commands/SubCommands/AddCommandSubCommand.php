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
 * Subcommand for adding a command to an existing portal.
 * Allows players to define a command that will be executed when entering the portal.
 */
class AddCommandSubCommand extends BaseSubCommand
{
    /**
     * Prepares the subcommand by setting permissions, arguments, and usage.
     */
    protected function prepare(): void
    {
        $this->setPermission(Permissions::ADD_COMMAND);
        $this->registerArgument(0, new RawStringArgument('portalname'));
        $this->registerArgument(1, new RawStringArgument('command', true));
        $this->setUsage("Usage: /portal addcommand <portalname> <command>");
    }

    /**
     * Executes the subcommand to add a command to a portal.
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

        $portalName = $args['portalname'] ?? null;
        $command = $args['command'] ?? null;

        // Check if the command argument is provided
        if ($command === null) {
            $sender->sendMessage("Please provide a command to add to the portal.");
            return;
        }

        // Add the command to the portal
        Await::f2c(function () use ($manager, $sender, $portalName, $command): \Generator {
            // Check if the portal exists
            $isExists = yield from $manager->isPortalExists($portalName, $sender->getName());
            if (!$isExists) {
                $sender->sendMessage("Portal '$portalName' does not exist.");
                return;
            }

            // Fetch the portal data
            $portalData = yield from $manager->fetchPortal($portalName);
            if ($portalData === null) {
                $sender->sendMessage("Failed to retrieve data for portal '$portalName'.");
                return;
            }

            // Parse existing commands and add the new one
            $commandsString = $portalData['data']['cmd'] ?? '';
            $commands = $commandsString !== '' ? explode(',', $commandsString) : [];
            $commands[] = $command;

            // Update the portal's command list
            $portalData['data']['cmd'] = implode(',', $commands);

            // Save the updated portal data
            yield from $manager->updatePortal($portalName, $portalData['data']);
            $sender->sendMessage("Command added to portal '$portalName': $command");
        });
    }
}