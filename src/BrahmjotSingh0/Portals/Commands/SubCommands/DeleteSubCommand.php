<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands\SubCommands;

use BrahmjotSingh0\Portals\Portals;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use SOFe\AwaitGenerator\Await;

class DeleteSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('portals.delete');
        $this->registerArgument(0, new RawStringArgument('portalname'));
        $this->setUsage("Usage: /portal delete <portalname>");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Portals $plugin */
        $plugin = $this->getOwningPlugin();
        $manager = $plugin->getPortalManager();

        $portalName = $args['portalname'];

        Await::f2c(function () use ($manager, $sender, $portalName): \Generator {
            $isExists = yield from $manager->isPortalExists($portalName);
            if (!$isExists) {
                $sender->sendMessage("Portal '$portalName' does not exist.");
                return;
            }
            yield from $manager->deletePortal($portalName);
            $sender->sendMessage("Portal '$portalName' has been deleted.");
        });
    }
}
