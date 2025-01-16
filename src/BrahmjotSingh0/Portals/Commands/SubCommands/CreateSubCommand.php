<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands\SubCommands;

use BrahmjotSingh0\Portals\Portals;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class CreateSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('portals.create');
        $this->registerArgument(0, new RawStringArgument('portalname'));
        $this->setUsage("Usage: /portal create <portalname>");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return;
        }

        /** @var Portals $plugin */
        $plugin = $this->getOwningPlugin();
        $manager = $plugin->getPortalManager();

        $portalName = $args['portalname'];

        Await::f2c(function () use ($plugin, $manager, $sender, $portalName): \Generator {
            $isExists = yield from $manager->isPortalExists($portalName);
            if ($isExists) {
                $sender->sendMessage("Portal '$portalName' already exists.");
                return;
            }
            
            // Store the player's state
            $plugin->getPortalManager()->setPlayerState($sender->getName(), ['portalName' => $portalName, 'step' => 'pos1']);
            $sender->sendMessage("Please break a block to set position 1 for portal '$portalName'.");
        });
    }
}