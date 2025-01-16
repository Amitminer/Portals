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
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

class PortalsCommand extends BaseCommand {
    
    protected Portals $portals;

    public function __construct(Portals $plugin){
        $this->portals = $plugin;
        parent::__construct($plugin, 'portals', 'Manage portals on the server');
    }

    public function prepare(): void {
        $this->setPermission('portals.command');
        
        // Register all subcommands
        $this->registerSubCommand(new CreateSubCommand($this->portals, "create", "Create a portal"));
        $this->registerSubCommand(new DeleteSubCommand($this->portals, "delete", "Delete a portal"));
        $this->registerSubCommand(new ListSubCommand($this->portals, "list", "List all portals"));
        $this->registerSubCommand(new AddCommandSubCommand($this->portals, "addcommand", "Add a command to a portal"));
        $this->registerSubCommand(new MsgSubCommand($this->portals, "msg", "Set a message for a portal"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $this->sendUsage();
    }
}
