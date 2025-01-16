<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Manager;

use BrahmjotSingh0\Portals\Portals;
use BrahmjotSingh0\Portals\Utils\SqlQueries;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Generator;
use Throwable;

/**
 * Manages database operations for the Portals plugin.
 */
class DatabaseManager
{
    /** @var Portals The plugin instance. */
    private Portals $plugin;

    /** @var DataConnector The database connection. */
    private DataConnector $database;

    /**
     * Initializes the database connection.
     *
     * @param Portals $plugin The plugin instance.
     * @throws Throwable If the database connection fails.
     */
    public function __construct(Portals $plugin)
    {
        $this->plugin = $plugin;
        try {
            $this->database = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
                "sqlite" => "sqlite.sql"
            ]);
        } catch (Throwable $e) {
            $this->plugin->getLogger()->critical("Failed to create database connection: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Loads or closes the database connection.
     *
     * @param bool $load Whether to load or close the database.
     */
    public function getDatabase(bool $load = true): void
    {
        if ($load) {
            try {
                $this->loadDatabase();
            } catch (Throwable $e) {
                $this->plugin->getLogger()->error("Failed to load database: " . $e->getMessage());
            }
        } else {
            $this->closeDatabase();
        }
    }

    /**
     * Initializes the database table.
     */
    private function loadDatabase(): void
    {
        $this->database->executeGeneric(SqlQueries::CREATE_TABLE, [], function () {
            // $this->plugin->getLogger()->info("Database table initialized successfully."); // for debugging
        }, function (\Throwable $error) {
            $this->plugin->getLogger()->critical("Error initializing database table: " . $error->getMessage());
        });
    }

    /**
     * Closes the database connection.
     */
    private function closeDatabase(): void
    {
        if (isset($this->database)) {
            try {
                $this->database->close();
            } catch (Throwable $e) {
                $this->plugin->getLogger()->error("Error closing database connection: " . $e->getMessage());
            }
        }
    }

    /**
     * Adds a new portal to the database.
     *
     * @param string $owner The owner of the portal.
     * @param string $name The name of the portal.
     * @param array $data The portal data to save.
     * @return Generator
     */
    public function addPortal(string $owner, string $name, array $data): Generator
    {
        yield from $this->database->asyncInsert(SqlQueries::SAVE, [
            "name" => $name,
            "owner" => $owner,
            "data" => json_encode($data)
        ]);
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
        yield from $this->database->asyncInsert(SqlQueries::UPDATE, [
            "name" => $name,
            "data" => json_encode($data)
        ]);
    }

    /**
     * Fetches all portals owned by a specific owner.
     *
     * @param string $owner The owner of the portals.
     * @return Generator<array> Returns a generator yielding an array of portals.
     */
    public function fetchPortalsByOwner(string $owner): Generator
    {
        $result = yield from $this->database->asyncSelect(SqlQueries::FETCH_BY_OWNER, [
            "owner" => $owner
        ]);

        foreach ($result as &$portal) {
            if (!isset($portal['data'])) {
                continue;
            }

            $decodedData = json_decode($portal['data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $portal['data'] = $decodedData;
            } else {
                $portal['data'] = [];
            }
        }

        return $result;
    }

    /**
     * Fetches a portal by its name.
     *
     * @param string $name The name of the portal.
     * @return Generator<array|null> Returns a generator yielding the portal data or null if not found.
     */
    public function fetchPortal(string $name): Generator
    {
        $result = yield from $this->database->asyncSelect(SqlQueries::FETCH_BY_NAME, [
            "name" => $name
        ]);

        if (count($result) === 0) {
            return null;
        }

        $portalData = $result[0];
        $portalData['data'] = json_decode($portalData['data'], true);
        return $portalData;
    }

    /**
     * Deletes a portal by its name.
     *
     * @param string $name The name of the portal to delete.
     * @return Generator<string|int|bool> Returns a generator yielding true if the portal was deleted, false otherwise.
     */
    public function deletePortal(string $name): Generator
    {
        $deletedRows = yield from $this->database->asyncInsert(SqlQueries::DELETE, [
            "name" => $name
        ]);
        $rowsDeleted = $deletedRows[1];

        if ($rowsDeleted > 0) {
            $this->plugin->getLogger()->info("Successfully deleted portal: $name");
        } else {
            $this->plugin->getLogger()->warning("Tried to delete portal $name, but it didn't exist.");
        }
        return $rowsDeleted > 0;
    }

    /**
     * Checks if a portal with the given name exists.
     *
     * @param string $name The name of the portal to check.
     * @return Generator<bool> Returns a generator yielding true if the portal exists, false otherwise.
     */
    public function isPortalExists(string $name): Generator
    {
        $result = yield from $this->database->asyncSelect(SqlQueries::EXISTS, [
            "name" => $name
        ]);

        if (empty($result)) {
            return false;
        }
        $portalCount = $result[0]["portal_count"] ?? 0;
        return $portalCount > 0;
    }
}