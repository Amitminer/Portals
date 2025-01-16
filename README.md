# Portals Plugin

Welcome to the **Portals** plugin for PocketMine-MP! This plugin allows players to create and manage portals that execute commands or display messages when players enter them.

[![](https://poggit.pmmp.io/shield.state/Portals)](https://poggit.pmmp.io/p/Portals)
[![](https://poggit.pmmp.io/shield.dl.total/Portals)](https://poggit.pmmp.io/p/Portals)
[![](https://poggit.pmmp.io/shield.api/Portals)](https://poggit.pmmp.io/p/Portals)

## Features

- Create portals with custom names
- Set positions for portals
- Add commands to be executed when players enter a portal
- Set messages to be displayed when players enter a portal
- Delete existing portals

## Commands

- **/portal create portalname**: Create a new portal with the specified name.
- **/portal addcommand portalname command**: Add a command to be executed when a player enters the specified portal.
- **/portal msg portalname message**: Set a message to be displayed when a player enters the specified portal.
- **/portal delete portalname**: Delete the specified portal.

- **Note** : /portal addcommand portalname player command : The command will be executed as the player who entered the portal.
- **Note** : /portal addcommand portalname server command : The command will be executed as the server console.
## Permissions

- **portals.command**: Allows the player to use the portal commands (default: op).

## Configuration

The configuration file (`config.yml`) is used to store portal data, including positions, commands, and messages. The plugin will automatically save the configuration file when portals are created or modified.

## Usage

1. **Create a Portal**: Use `/portal create <portalname>` to create a new portal with the specified name.
2. **Add Commands**: Use `/portal addcommand <portalname>  <command>` to add a command that will be executed when a player enters the portal.
3. **Set Message**: Use `/portal msg <portalname> <message>` to set a message that will be displayed when a player enters the portal.
4. **Delete a Portal**: Use `/portal delete <portalname>` to delete the specified portal.

## Example

To create a portal named "example", set its positions, add a command, and set a message, follow these steps:

1. `/portal create example`
4. `/portal addcommand example "say Welcome to the portal!, say another command here"`
5. `/portal msg example "You have entered the example portal."`

## Installation

1. Download the plugin.
2. Place the plugin's `.phar` file in the `plugins` folder of your PocketMine-MP server.
3. Start or restart the server to load the plugin.

## Support

If you encounter any issues or have any questions, please open an issue on the [GitHub repository](https://github.com/BrahmjotSingh0/Portals).

---

**Author**: BrahmjotSingh0
**Version**: 2.8.0 (not stable) 
**API**: 5.0.0  
**License**: MIT
