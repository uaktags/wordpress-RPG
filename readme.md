# Wordpress RPG

-Contributors: Tim G
-Tags:
-Requires at least: 3.5
-Tested up to: 3.5
-Stable tag: 0.0.7
~Current Version:0.0.7~
=======


~Current Version:0.0.5~


## Description
Inspired by the ezRPG Engine (www.ezrpgproject.net), Wordpress RPG (WP-RPG) looks to add RPG game elements to be built off of Wordpress as it's framework.

While this is an early Alpha release, the ultimate goal is to allow developers build plugins that will expand WP-RPG to add more features. Other plugins, using
hooks/actions, can then tap into WP-RPG and add RPG elements to their offerings. 

Now Wordpress visitors have something to keep themselves occupied, build a game along side a popular blog, add game elements to your Multisite and build an MMORPG structure.
Build your own game, on your very popular and easy to use Wordpress Site.

## Changelog ==

### 0.0.5-0.0.7 =
- Wrapped WPRPG inside a Class.
- Removed most external includes.
- Created an external Uninstall script due to concerns uninstall wasn't always running (bug).
- Removed Attack functions for separate module.

### 0.0.4 =
- Fixed a plugin activation issue.

### 0.0.3 =
- Added a basic cron system. Works off of 30minute intervals, does calculations when scheduled crons haven't been ran.
- Added a replenish hp function. Every 30minutes you get 1 HP.

### 0.0.2 =
- Updated the Battle system
- Battle system results work

### 0.0.1 =
- Initial Revision
