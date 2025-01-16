<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Manager;

use Generator;

class PortalManager
{
    private DatabaseManager $databaseManager;
    private array $playerStates = [];


    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

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

    public function setPlayerState(string $playerName, ?array $state): void
    {
        if ($state === null) {
            unset($this->playerStates[$playerName]);
        } else {
            $this->playerStates[$playerName] = $state;
        }
    }

    public function getPlayerState(string $playerName): ?array
    {
        return $this->playerStates[$playerName] ?? null;
    }

    public function addPortal(string $owner, string $name, ?array $data = null): Generator
    {
        $data ??= $this->getDefaultPortalData();
        yield from $this->databaseManager->addPortal($owner, $name, $data);
    }

    public function updatePortal(string $name, array $data): Generator
    {
        $data = array_merge($this->getDefaultPortalData(), $data);
        yield from $this->databaseManager->updatePortal($name, $data);
    }

    public function fetchPortalsByOwner(string $owner): Generator
    {
        return yield from $this->databaseManager->fetchPortalsByOwner($owner);
    }

    public function fetchPortal(string $name): Generator
    {
        return yield from $this->databaseManager->fetchPortal($name);
    }

    public function deletePortal(string $name): Generator
    {
        return yield from $this->databaseManager->deletePortal($name);
    }

    public function isPortalExists(string $name): Generator
    {
        return yield from $this->databaseManager->isPortalExists($name);
    }
}
