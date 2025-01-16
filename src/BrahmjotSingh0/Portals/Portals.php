<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals;

use BrahmjotSingh0\Portals\Commands\PortalsCommand;
use BrahmjotSingh0\Portals\Manager\DatabaseManager;
use BrahmjotSingh0\Portals\Manager\PortalManager;
use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;

/**
 * Main class for the Portals plugin.
 * Handles initialization, configuration, and management of portals.
 */
class Portals extends PluginBase
{
    /** @var PortalManager Manages portal-related operations. */
    private PortalManager $portalManager;

    /** @var DatabaseManager Manages database operations for portals. */
    private DatabaseManager $databaseManager;

    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->databaseManager = new DatabaseManager($this);
        $this->databaseManager->getDatabase(true);

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $this->portalManager = new PortalManager($this->databaseManager);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getServer()->getCommandMap()->register('portals', new PortalsCommand($this));
    }

    /**
     * Returns the PortalManager instance.
     *
     * @return PortalManager The portal manager.
     */
    public function getPortalManager(): PortalManager
    {
        return $this->portalManager;
    }

    /**
     * Returns the DatabaseManager instance.
     *
     * @return DatabaseManager The database manager.
     */
    public function getDatabaseManager(): DatabaseManager
    {
        return $this->databaseManager;
    }
}