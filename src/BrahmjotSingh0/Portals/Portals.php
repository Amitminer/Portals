<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals;

use BrahmjotSingh0\Portals\Commands\PortalsCommand;
use BrahmjotSingh0\Portals\Manager\DatabaseManager;
use BrahmjotSingh0\Portals\Manager\PortalManager;
use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;

class Portals extends PluginBase
{
    private PortalManager $portalManager;

    private DatabaseManager $databaseManager;

    public function onEnable(): void
    {
        $this->databaseManager = new DatabaseManager($this);
        $this->databaseManager->getDatabase(true);

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $this->saveDefaultConfig();

        $this->portalManager = new PortalManager($this->databaseManager);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getServer()->getCommandMap()->register('portals', new PortalsCommand($this));
    }

    public function getPortalManager(): PortalManager
    {
        return $this->portalManager;
    }

    public function getDatabaseManager(): DatabaseManager
    {
        return $this->databaseManager;
    }
}
