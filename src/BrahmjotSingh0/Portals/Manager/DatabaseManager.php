<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Manager;

use BrahmjotSingh0\Portals\Portals;
use BrahmjotSingh0\Portals\Utils\SqlQueries;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Generator;
use Throwable;

class DatabaseManager
{
    private Portals $plugin;
    private DataConnector $database;

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

    private function loadDatabase(): void
    {
        $this->database->executeGeneric(SqlQueries::CREATE_TABLE, [], function () {
            // $this->plugin->getLogger()->info("Database table initialized successfully."); // for debugging
        }, function (\Throwable $error) {
            $this->plugin->getLogger()->critical("Error initializing database table: " . $error->getMessage());
        });
    }

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

    public function addPortal(string $owner, string $name, array $data): Generator
    {
        yield from $this->database->asyncInsert(SqlQueries::SAVE, [
            "name" => $name,
            "owner" => $owner,
            "data" => json_encode($data)
        ]);
    }

    public function updatePortal(string $name, array $data): Generator
    {
        yield from $this->database->asyncInsert(SqlQueries::UPDATE, [
            "name" => $name,
            "data" => json_encode($data)
        ]);
    }

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

    public function deletePortal(string $name): Generator
    {
        $deletedRows = yield from $this->database->asyncInsert(SqlQueries::DELETE, [
            "name" => $name
        ]);
        /** @phpstan-ignore-next-line */
        if ($deletedRows > 0) {
            // $this->plugin->getLogger()->info("Successfully deleted portal: $name");
        } else {
            $this->plugin->getLogger()->warning("Tried to delete portal $name, but it didn't exist.");
        }
        return $deletedRows > 0;
    }

    public function isPortalExists(string $name): Generator
    {
        $result = yield from $this->database->asyncSelect(SqlQueries::EXISTS, [
            "name" => $name
        ]);
        /** @phpstan-ignore-next-line */
        return $result !== null && count($result) > 0 && $result[0]["COUNT(*)"] > 0;
    }
}
