# ROLECAP Change Log

## [0.1.8] - 2025-01-03
### Changed
- /view/submenu_inout previously displayed $roles and $original. Removed $roles as intended, and now functional.
- rolecap.php inout_import_serialised_string() update code changed completely: originally brute forcing update_option, then tried add_role/remove_role to different effect, and finally landed on update_option but reloading roles safely, and adding in more serialized checks and stripslashes so that it actually works.
- INOUT complete?

## [0.1.7] - 2025-01-01
### Changed
- inout_import_serialised_string bug checking for presence for an accurate manage_options serialised setting that was previously failing.
- Unfortunately manually adding serialised codes is causing wp failure, thought it was slashes, added strip_slashes but that also kills it. Need to figure it out. MIght be that i'm overwriting too much of the options rather than just user cap/role?


## [0.1.6] - 2024-12-10
### Added
- submenu_roles and submenu_capabilities files in view folder

### Changed
- moved HTML output to separate files for display_submenu_roles and display_submenu_capabilities
- moved display functions above add_admin_plugin_menu_subs for readability

## [0.1.5] - 2024-12-09
### Added 
- Blank index page to view subfolder to prevent directory view
- Hidden field for the import form of submenu_inout.php
- inout_import_serialised_string which ensures post data is correct before committing to update the settings
-- this is not finished: checks are in place, but not the actual action if they pass

### Changed
-- GENERAL
- dismissable_success now accepts type as a parameter, and the function is updated to create a class based on passed type
- fixed the dismissable_success bug of not displaying the $message value
- commented out form action update_capabilities in process_submenu_inout_form temporarily before move
- added optional parameter to error_logging, if not default false, will send the same message to dismissable_success 
-- FUNC RENAMING (for better context)
- form_actions_capabilities_reset is now inout_reset_to_install
- add_menu_item is now add_admin_plugin_menu and has the manage_options check the comment said was there
- add_submenu_item is now add_admin_plugin_menu_subs
-- FUNC PLACEMENT (for redability bottom to top from constructor):
- process_capability_form_submission is now process_submenu_inout_form
- swapped placement of add_admin_plugin_menu_subs and add_admin_plugin_menu
- moved error_logging to be with dismissable_success
- moved process_submenu_inout_form below inout_reset_to_install

### Removed
- Removed the commented out section from the constructor, think it was there as an attempt to fix broken permissions:
/*    add_action('init', function() {
      // Get the administrator role
      $role = get_role('administrator');
      
      // Check if the role exists and add the manage_options capability
      if ($role) {
          $role->add_cap('manage_options');
      }
    });
*/

## [0.1.4] - 2024-12-06
### Added
- View subdir for php enchanced html, making the plugin code easier to read 
- Export/Import/Revert html for the import/export page, now has readonly textareas for export and revert, and a writeable textarea for import. 

### Changed
- Moved the import / export html to its own file, and changed from echos to html with inline php tags

## [0.1.3] - 2024-12-05
### Added
- Plugin menu item and hook, so no longer included in the users.php menu
- Placeholder functions for Roles page and Import/Export page and submenu items

### Changed
- Capabilities submenu no longer points to users.php, now in RoleCap menu


## [0.1.2] - 2024-11-24
### Added
- Fixes.php with three functions wrapped in a class:
-- Allows addition of manage_options to administrator role
-- Allows restoration of all roles and cpabilities via wordpress' populate_roles
-- Allows fallback/direct restoration of all roles manually 

### Changed
- Removed the closing ?> in rolecap.php for security

## [0.1.1] - 2024-11-13
### Added
- Nonce fields to both forms and check_admin_referer of those nonces to the form_actions functions
- Custom error_logging function to prefix all error_log messages with [RoleCap plugin] prefix
- Error logging!
- Refactored admin notices from two form_action_* functions to a reusable dismissable_success function
- Comments throughout, though how useful they are is up to you

### Changed
- All __() translation replaced with _e() as not needing to modify translated strings, can directly print
- Added $autoload="no" to update_option in install_new for 'memory managment'
- All public functions except the __constructor return true or false unless they should return something specific
- form_actions_capabilities_update made private, was public
- Minimum requirements based on features used in code, but not tested: WP version 5.6, PHP version 7.4 

### Removed
- Call to version_check in rolecap_activation_check as this was duplicated in the constructor 


## [0.1.0] - 2024-11-12
### Added
- Initial project setup
- View, edit, and reset user role capabilities via submenu of User menu.
- Empty index page 