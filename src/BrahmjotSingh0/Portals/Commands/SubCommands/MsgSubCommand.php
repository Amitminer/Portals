<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands\SubCommands;

use BrahmjotSingh0\Portals\Portals;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use SOFe\AwaitGenerator\Await;

class MsgSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('portals.msg');
        $this->registerArgument(0, new RawStringArgument('portalname'));
        $this->registerArgument(1, new RawStringArgument('message', true));
        $this->setUsage("Usage: /portal msg <portalname> <message>");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var Portals $plugin */
        $plugin = $this->getOwningPlugin();
        $manager = $plugin->getPortalManager();

        $portalName = $args['portalname'] ?? null;
        $message = $args['message'] ?? null;

        if ($message === null) {
            $sender->sendMessage("Please provide a message to set for the portal.");
            return;
        }

        Await::f2c(function () use ($manager, $sender, $portalName, $message): \Generator {
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

            if (!isset($portalData['data']['message'])) {
                $portalData['data']['message'] = "";
            }

            $portalData['data']['message'] = $message;

            yield from $manager->updatePortal($portalName, $portalData['data']);

            $sender->sendMessage("Message for portal '$portalName' has been updated to: $message");
        });
    }
}
