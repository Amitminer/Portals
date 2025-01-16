<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Utils;

/**
 * Defines permission constants for the Portals plugin.
 * These constants are used to enforce access control for various commands and actions.
 */
interface Permissions
{
    /** @var string Permission to use the main portals command. */
    public const COMMAND = "portals.command";

    /** @var string Permission to create a portal. */
    public const CREATE = "portals.create";

    /** @var string Permission to delete a portal. */
    public const DELETE = "portals.delete";

    /** @var string Permission to list portals. */
    public const LIST = "portals.list";

    /** @var string Permission to add a command to a portal. */
    public const ADD_COMMAND = "portals.addcommand";

    /** @var string Permission to set a message for a portal. */
    public const MESSAGE = "portals.msg";

    /** @var string Permission to update a portal. */
    public const UPDATE = "portals.update";

    /** @var string Permission to fetch portal data. */
    public const FETCH = "portals.fetch";

    /** @var string Permission to check if a portal exists. */
    public const EXISTS = "portals.exists";
}