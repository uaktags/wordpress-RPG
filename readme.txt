=== Wordpress RPG ===
Contributors: uaktags
Tags: rpg, wpRPG, Role Playing Game, games, ezRPG
Donate link: http://tagsolutions.tk/donate/
Requires at least: 3.6
Tested up to: 3.8
Stable tag: 1.0.19
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Modular Role-Playing-Game engine built ontop of WordPress

== Description ==
## This plugin is still an on-going development. Issues are sure to arise trying to build an engine that lays foundation for other plugins to build off of. Please use the support forums for help developing.

Inspired by the ezRPG Engine (www.ezrpgproject.net), Wordpress RPG (WP-RPG) looks to add RPG game elements to be built off of Wordpress as it\'s framework.

While this is an early Alpha release, the ultimate goal is to allow developers build plugins that will expand WP-RPG to add more features. Other plugins, using
hooks/actions, can then tap into WP-RPG and add RPG elements to their offerings. 

Now Wordpress visitors have something to keep themselves occupied, build a game along side a popular blog, add game elements to your Multisite and build an MMORPG structure.
Build your own game, on your very popular and easy to use Wordpress Site.


== Installation ==
1. Upload \"wprpg\" folder to the \"/wp-content/plugins/\" directory.
1. Activate the plugin through the \"Plugins\" menu in WordPress.
1. Place \"[list_players]\" in a page to list all players.

== Frequently Asked Questions ==
= What is a Text-based RPG? =
Role Playing Game. Typically a Text-based RPG consists of a player vs player or player vs world game where you must preform actions to collect items, defeat enemies, and/or build armies. There\'s little to no graphics outside of the websites theme.

= Is this plugin an example of an RPG? =
No, this plugin is an \"engine\" or \"framework\" to build your RPG. With the addition of modules and a custom theme, each wordpress blog could have it\'s own unique game built off of this plugin. wpRPG merely provides the tools needed.


== Changelog ==
= 1.0.19 =
- Fixed a Player Class issue with currentUser
- Started translation support. I'm very new to this concept so bare with me for a while.
- Next large set of updates will mark the beginning of 1.1.x series.

= 1.0.18 =
- Fixed the Registration Plugin which had an error with registering new users.

= 1.0.17r1 =
- Reupload the files without ending ?> in the classes to fix unexpected output messages.

= 1.0.17 =
- Deprecated the Player class name from "Player" to "wpRPG_Player".
- Fixed a DebugFunction error that was in the Array causing it to not preform as expected.

= 1.0.16 =
- Updated an issue in the Player Class which caused unexpected results when updating a meta that had the value of 0, the function thought it was a value of "false", and provided unexpected results.
- Added a Plugin_Class which will handle anything that's strictly related to extended plugins such as the Plugin Manager.
- Added a plugin manager to visually see and control all plugins that extend wpRPG. Plugins must register to use the manager.

= 1.0.15 =
- Created a Player Class which handles all Player_Meta functions
- Updated bundled plugins to use Player Class

= 1.0.14 = 
- Fixed the Level Manager issue.
- Fixed a bunch of issues with the Debug functions.
- There was an issue causing the 1.0.3 Attack Module from working.

= 1.0.12 - 1.0.13 =
- Converted rpg_usermeta tables to native WP usermeta. Now instead of custom sql and joins, you use get_user_meta and update_user_meta.
- Added a check for Cron Enable/Disabled tag. Wasn't properly in place before.
- Removed the install for rpg_usermeta for reason listed above.
- Fixed the get_player_meta function for error checking.
- get_player_meta function also calls a new function 'get_meta' combines all rpg_usermeta with wp_user and meta. Creates a super variable for the player.
- Added a Reset Gold in the debug panel.
- Separated the Profile Pages from the Core class. Now the pages tab is hooked by plugins to utilize.
- The Pages panel on the admin page also displays the shortcodes needed for those pages. This is hooked with the plugins too.
- Completely removed races from the core class.

= 1.0.11 = 
- Made extensive progress with the Profiles plugin. Took away the attack profiles and used the Profile's native functionality. Also the members plugin handles the list_players instead of the Attack plugin handling it.
- Alot of changes have been made with the Attack plugin as well. Until WP approves my WPRPG_Attack request, I've zipped and added the attack plugin in the /plugins/ folder.
- You'll also have the opportunity to change the way the Profiles/Members page looks and acts. Currently, it checks if your THEME has a directory called "templates/wprpg/" and looks for the file "view_profile.php" and "list_players.php". This allows you to customize your Lists / Profiles how you see fit without worrying about my updates screwing it up :-).
- Everything in my tests feels very stable so far. Using the new attack module I included, you'll see that you can add things to the List_Players actions and the Profile sections, like the attack button, which is dynamically added rather than statically.
= 1.0.10 =
- Fixed a bug found in the plugins in which they weren't pointing to the correct directories. 

= 1.0.9 =
- Created a jQuery Filter so that we can add all wpRPG jquery code to a single code base for organization. It also ensures that wpRPG required code is executed prior to plugin code.

= 1.0.8 =
- Split the plugin into better sections for organization and debugging purposes. Code seems to be more stable now.

= 1.0.7 = 
- Added a Debug and Level Manager Tab.
- Debug Menu allows you to reset stats to restart the game (great for helping to test).
- Level Manager allows you to create and delete levels.

= 1.0.6 =
- Fixed the Hospital...ish....Should now atleast recognize that you need health, and adds it accordingly. And the template works.
- Added a variable for the HP Increments for every cron. Thanks to Crono from ezRPG.
- Fixed an Attack issue that made you lose EVERYTIME.

= 1.0.5 = 
- Starting to really form the Profile Sections. Now plugins can interact with the standard default profile. Later maybe a Profile editor should be looked at?

= 1.0.4 =
- Started adding the look of the cron manager being table based, and an action column will enable/disable each cron.
- Pages now works to manually edit the rewrite script for Profiles. So say you set Profile to a page with permalink as "check-profile", then now doing blog.com/check-profile/player-name will bring up their profile.
- A todo has been added to the main homepage to explain what's expected to come next.

= 1.0.3 =
- Fixed an issue with the cron system not updating the last and next execution times
- Added the ability to see all the crons that are in the system. Next will be to make a management script to activate/deactivate them.

= 1.0.2 =
- Implemented a cron enqueuing system for other modules in the wpRPG realm to add to. 
- Moved the replenish hp function to the Hospital function as that\'s more relevant.

= 1.0.1 =
- Fixed an issue caused by missing functions that caused hidden errors during install.

= 1.0.0 =
- Fixes to make wordpress compliant.
- Created other modules to extend on wpRPG.
- First version to be Wordpress approved for the Repo. 

= 0.7 - 0.10.9 =
- Combined the Profile, Hospital, and Members function into the core wpRPG plugin file.
- Removed github sync to comply with wordpress repo requirements.
- Added multisite compatiability with db calls.
- Added a race usermeta during signups. Races have different bonuses which will be expanded on with the Attack module
- Created a default profile look and feel.
- Numerous other changes.

= 0.6 =
- Added Filters for the admin pages. 
- Filters are being used to allow other plugins to hook in.
- More Filters and Actions to come.

= 0.2 - 0.5 =
- Fixes to update script
- Fixed errors being found on select systems
- Fixed Activation issues

= 0.1 =
- Most Formatting fixes
- Connected to private repo for updates
- Removed GitHub updater until its fixed
- Added Tab Options panel

= 0.0.8 =
- Fixed activation issue due to out of scope function call.

= 0.0.5-0.0.7 =
- Wrapped WPRPG inside a Class.
- Removed most external includes.
- Created an external Uninstall script due to concerns uninstall wasn\'t always running (bug).
- Removed Attack functions for separate module.

= 0.0.4 =
- Fixed a plugin activation issue.

= 0.0.3 =
- Added a basic cron system. Works off of 30minute intervals, does calculations when scheduled crons haven\'t been ran.
- Added a replenish hp function. Every 30minutes you get 1 HP.

= 0.0.2 =
- Updated the Battle system
- Battle system results work

= 0.0.1 =
- Initial Revision