<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands\SubCommands;

use BrahmjotSingh0\Portals\Portals;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use SOFe\AwaitGenerator\Await;

class ListSubCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission('portals.list');
        $this->setUsage('/portal list');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Portals $plugin */
        $plugin = $this->getOwningPlugin();
        $manager = $plugin->getPortalManager();

        Await::f2c(function () use ($manager, $sender): \Generator {
            $portals = yield from $manager->fetchPortalsByOwner($sender->getName());
            if ($portals === null || count($portals) === 0) {
                $sender->sendMessage("You have not created any portals yet.");
                return;
            }
            $sender->sendMessage("Your Portals:");
            foreach ($portals as $portal) {
                $sender->sendMessage("- " . $portal['name']);
            }
        });
    }
}
