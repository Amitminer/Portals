<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands\SubCommands;

use BrahmjotSingh0\Portals\Portals;
use BrahmjotSingh0\Portals\Utils\Permissions;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use SOFe\AwaitGenerator\Await;

/**
 * Subcommand for deleting an existing portal.
 * Allows players to delete a portal by its name.
 */
class DeleteSubCommand extends BaseSubCommand
{
    /**
     * Prepares the subcommand by setting permissions, arguments, and usage.
     */
    protected function prepare(): void
    {
        $this->setPermission(Permissions::DELETE);
        $this->registerArgument(0, new RawStringArgument('portalname'));
        $this->setUsage("Usage: /portal delete <portalname>");
    }

    /**
     * Executes the subcommand to delete a portal.
     *
     * @param CommandSender $sender The command sender.
     * @param string $aliasUsed The alias used to execute the command.
     * @param array $args The arguments passed to the command.
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Portals $plugin */
        $plugin = $this->getOwningPlugin();
        $manager = $plugin->getPortalManager();

        $portalName = $args['portalname'];

        // Check if the portal exists and delete it
        Await::f2c(function () use ($manager, $sender, $portalName): \Generator {
            $isExists = yield from $manager->isPortalExists($portalName);
            if (!$isExists) {
                $sender->sendMessage("Portal '$portalName' does not exist.");
                return;
            }

            // Delete the portal
            yield from $manager->deletePortal($portalName);
            $sender->sendMessage("Portal '$portalName' has been deleted.");
        });
    }
}