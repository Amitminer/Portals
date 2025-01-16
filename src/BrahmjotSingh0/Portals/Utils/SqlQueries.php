<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Utils;

/**
 * Defines SQL query identifiers for portal-related database operations.
 * These constants are used to reference specific queries in the database manager.
 */
interface SqlQueries
{
    /** @var string Query identifier for creating the portals table. */
    public const CREATE_TABLE = "portals.createTable";

    /** @var string Query identifier for saving a new portal. */
    public const SAVE = "portals.save";

    /** @var string Query identifier for updating an existing portal. */
    public const UPDATE = "portals.update";

    /** @var string Query identifier for fetching portals by owner. */
    public const FETCH_BY_OWNER = "portals.fetchByOwner";

    /** @var string Query identifier for fetching a portal by name. */
    public const FETCH_BY_NAME = "portals.fetchByName";

    /** @var string Query identifier for deleting a portal. */
    public const DELETE = "portals.delete";

    /** @var string Query identifier for checking if a portal exists. */
    public const EXISTS = "portals.exists";
}