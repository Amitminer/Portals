<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Utils;

interface SqlQueries
{
    public const CREATE_TABLE = "portals.createTable";
    public const SAVE = "portals.save";
    public const UPDATE = "portals.update";
    public const FETCH_BY_OWNER = "portals.fetchByOwner";
    public const FETCH_BY_NAME = "portals.fetchByName";
    public const DELETE = "portals.delete";
    public const EXISTS = "portals.exists";
}
