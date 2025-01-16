<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Commands;

use BrahmjotSingh0\Portals\Commands\SubCommands\{
    AddCommandSubCommand,
    CreateSubCommand,
    DeleteSubCommand,
    ListSubCommand,
    MsgSubCommand
};
use BrahmjotSingh0\Portals\Portals;
use BrahmjotSingh0\Portals\Utils\Permissions;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

/**
 * Main command for managing portals on the server.
 * Registers subcommands for portal creation, deletion, listing, and configuration.
 */
class PortalsCommand extends BaseCommand
{
    /** @var Portals The plugin instance. */
    protected Portals $portals;

    /**
     * Initializes the PortalsCommand with the plugin instance.
     *
     * @param Portals $plugin The plugin instance.
     */
    public function __construct(Portals $plugin)
    {
        $this->portals = $plugin;
        parent::__construct($plugin, 'portals', 'Manage portals on the server');
    }

    /**
     * Prepares the command by setting permissions and registering subcommands.
     */
    public function prepare(): void
    {
        $this->setPermission(Permissions::COMMAND);

        // Register all subcommands
        $this->registerSubCommand(new CreateSubCommand($this->portals, "create", "Create a portal"));
        $this->registerSubCommand(new DeleteSubCommand($this->portals, "delete", "Delete a portal"));
        $this->registerSubCommand(new ListSubCommand($this->portals, "list", "List all portals"));
        $this->registerSubCommand(new AddCommandSubCommand($this->portals, "addcommand", "Add a command to a portal"));
        $this->registerSubCommand(new MsgSubCommand($this->portals, "msg", "Set a message for a portal"));
    }

    /**
     * Executes the command when no subcommand is provided.
     * Sends the command usage to the sender.
     *
     * @param CommandSender $sender The command sender.
     * @param string $aliasUsed The alias used to execute the command.
     * @param array $args The arguments passed to the command.
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $this->sendUsage();
    }
}