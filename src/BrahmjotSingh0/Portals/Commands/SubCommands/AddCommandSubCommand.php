<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands\SubCommands;

use BrahmjotSingh0\Portals\Portals;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class AddCommandSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('portals.addcommand');
        $this->registerArgument(0, new RawStringArgument('portalname'));
        $this->registerArgument(1, new RawStringArgument('command', true));
        $this->setUsage("Usage: /portal addcommand <portalname> <command>");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return;
        }

        /** @var Portals $plugin */
        $plugin = $this->getOwningPlugin();
        $manager = $plugin->getPortalManager();

        $portalName = $args['portalname'] ?? null;
        $command = $args['command'] ?? null;

        if ($command === null) {
            $sender->sendMessage("Please provide a command to add to the portal.");
            return;
        }

        Await::f2c(function () use ($manager, $sender, $portalName, $command): \Generator {
            $isExists = yield from $manager->isPortalExists($portalName);

            if (!$isExists) {
                $sender->sendMessage("Portal '$portalName' does not exist.");
                return;
            }

            $portalData = yield from $manager->fetchPortal($portalName);

            if ($portalData === null) {
                $sender->sendMessage("Failed to retrieve data for portal '$portalName'.");
                return;
            }

            $commandsString = $portalData['data']['cmd'] ?? '';
            $commands = $commandsString !== '' ? explode(',', $commandsString) : [];

            $commands[] = $command;

            $portalData['data']['cmd'] = implode(',', $commands);

            yield from $manager->updatePortal($portalName, $portalData['data']);

            $sender->sendMessage("Command added to portal '$portalName': $command");
        });
    }
}
