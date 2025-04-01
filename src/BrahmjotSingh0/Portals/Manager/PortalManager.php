<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Manager;

use Generator;

/**
 * Manages portal-related operations, including player states and database interactions.
 */
class PortalManager
{
    /** @var DatabaseManager Handles database operations for portals. */
    private DatabaseManager $databaseManager;

    /** @var array<string, array|null> Stores player states for portal creation or editing. */
    private array $playerStates = [];

    /**
     * Initializes the PortalManager with a DatabaseManager instance.
     *
     * @param DatabaseManager $databaseManager The database manager for portal operations.
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Returns the default portal data structure.
     *
     * @return array<string, mixed> The default portal data.
     */
    public function getDefaultPortalData(): array
    {
        return [
            "worldName" => "",
            "pos1" => null,
            "pos2" => null,
            "message" => "",
            "cmd" => ""
        ];
    }

    /**
     * Sets the state of a player for portal creation or editing.
     *
     * @param string $playerName The name of the player.
     * @param array|null $state The state to set, or null to clear the state.
     */
    public function setPlayerState(string $playerName, ?array $state): void
    {
        if ($state === null) {
            unset($this->playerStates[$playerName]);
        } else {
            $this->playerStates[$playerName] = $state;
        }
    }

    /**
     * Returns the current state of a player for portal creation or editing.
     *
     * @param string $playerName The name of the player.
     * @return array|null The player's state, or null if no state is set.
     */
    public function getPlayerState(string $playerName): ?array
    {
        return $this->playerStates[$playerName] ?? null;
    }

    /**
     * Adds a new portal to the database.
     *
     * @param string $owner The owner of the portal.
     * @param string $name The name of the portal.
     * @param array|null $data The portal data. If null, default data will be used.
     * @return Generator
     */
    public function addPortal(string $owner, string $name, ?array $data = null): Generator
    {
        $data ??= $this->getDefaultPortalData();
        yield from $this->databaseManager->addPortal($owner, $name, $data);
    }

    /**
     * Updates an existing portal in the database.
     *
     * @param string $name The name of the portal to update.
     * @param array $data The updated portal data.
     * @return Generator
     */
    public function updatePortal(string $name, array $data): Generator
    {
        $data = array_merge($this->getDefaultPortalData(), $data);
        yield from $this->databaseManager->updatePortal($name, $data);
    }

    /**
     * Fetches all portals owned by a specific owner.
     *
     * @param string $owner The owner of the portals.
     * @return Generator<array> Returns a generator yielding an array of portals.
     */
    public function fetchPortalsByOwner(string $owner): Generator
    {
        return yield from $this->databaseManager->fetchPortalsByOwner($owner);
    }

    /**
     * Fetches a portal by its name.
     *
     * @param string $name The name of the portal.
     * @return Generator<array|null> Returns a generator yielding the portal data or null if not found.
     */
    public function fetchPortal(string $name): Generator
    {
        return yield from $this->databaseManager->fetchPortal($name);
    }

    /**
     * Deletes a portal by its name.
     *
     * @param string $name The name of the portal to delete.
     * @return Generator<mixed> Returns a generator yielding true if the portal was deleted, false otherwise.
     */
    public function deletePortal(string $name): Generator
    {
        return yield from $this->databaseManager->deletePortal($name);
    }

    /**
     * Checks if a portal with the given name exists for a specific owner.
     *
     * @param string $name The name of the portal to check.
     * @param string $owner The owner to check for.
     * @return Generator<mixed> Returns a generator yielding true if the portal exists, false otherwise.
     */
    public function isPortalExists(string $name, string $owner): Generator
    {
        return yield from $this->databaseManager->isPortalExists($name, $owner);
    }
}