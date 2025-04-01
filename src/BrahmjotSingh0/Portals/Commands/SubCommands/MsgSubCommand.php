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
 * Subcommand for setting or updating the message of an existing portal.
 * Allows players to define a message that will be displayed when entering the portal.
 */
class MsgSubCommand extends BaseSubCommand
{
    /**
     * Prepares the subcommand by setting permissions, arguments, and usage.
     */
    protected function prepare(): void
    {
        $this->setPermission(Permissions::MESSAGE);
        $this->registerArgument(0, new RawStringArgument('portalname'));
        $this->registerArgument(1, new RawStringArgument('message', true));
        $this->setUsage("Usage: /portal msg <portalname> <message>");
    }

    /**
     * Executes the subcommand to set or update the message of a portal.
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

        $portalName = $args['portalname'] ?? null;
        $message = $args['message'] ?? null;

        // Check if the message argument is provided
        if ($message === null) {
            $sender->sendMessage("Please provide a message to set for the portal.");
            return;
        }

        // Update the portal's message
        Await::f2c(function () use ($manager, $sender, $portalName, $message): \Generator {
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

            // Update the portal's message
            if (!isset($portalData['data']['message'])) {
                $portalData['data']['message'] = "";
            }
            $portalData['data']['message'] = $message;

            // Save the updated portal data
            yield from $manager->updatePortal($portalName, $portalData['data']);
            $sender->sendMessage("Message for portal '$portalName' has been updated to: $message");
        });
    }
}