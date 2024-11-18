# ROLECAP Change Log

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