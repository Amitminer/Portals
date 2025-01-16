<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands\SubCommands;

use BrahmjotSingh0\Portals\Portals;
use BrahmjotSingh0\Portals\Utils\Permissions;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use SOFe\AwaitGenerator\Await;

/**
 * Subcommand for listing all portals owned by the command sender.
 * Displays a list of portal names created by the player.
 */
class ListSubCommand extends BaseSubCommand
{
    /**
     * Prepares the subcommand by setting permissions and usage.
     */
    protected function prepare(): void
    {
        $this->setPermission(Permissions::LIST);
        $this->setUsage('/portal list');
    }

    /**
     * Executes the subcommand to list all portals owned by the sender.
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

        // Fetch and display the list of portals owned by the sender
        Await::f2c(function () use ($manager, $sender): \Generator {
            $portals = yield from $manager->fetchPortalsByOwner($sender->getName());

            // Check if the sender has any portals
            if ($portals === null || count($portals) === 0) {
                $sender->sendMessage("You have not created any portals yet.");
                return;
            }

            // Display the list of portals
            $sender->sendMessage("Your Portals:");
            foreach ($portals as $portal) {
                $sender->sendMessage("- " . $portal['name']);
            }
        });
    }
}