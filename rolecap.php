<?php
/*
Plugin Name: Role Capabilities
Description: View Roles and Capabilities
Version: 0.1.7
Requires at least: 5.6
PHP Version: 7.4
Author: Paul Johnson
Text Domain: rolecap
*/

// Avoid redeclaration of class
if(!class_exists('RoleCap')){
class RoleCap {
  private const ROLECAP_VERSION_KEY = "rolecap_version";
  private const ROLECAP_VERSION_VALUE = "1";
  private const ROLECAP_MIN_WP_VERSION = "5.8";
  private const ROLECAP_CAPABILITIES_BACKUP_ORIGINAL = "rolecap_backup_og_capabilities";

  // Find all of the current capabilities by manually going through the option for user_roles
  // Ignore duplicates, create an array of unique capabilities 
  private function parse_capabilities() {
    $roles = get_option('wp_user_roles');
    $capabilities = [];
    foreach($roles as $role) {
      foreach($role['capabilities'] as $key => $value) {
        if(in_array($key, $capabilities) === false) {
          $capabilities[] = $key;
        }
      }
    }
    
    if(count($capabilities) == 0) {
      $this->error_logging("Unable to parse capabilities or no capabilities found.");
      return false;
    }
    return $capabilities;
  }

  // Updates the user capabilities based on form submission
  // Loop each row (capability) by each column (user) to get compound names of checkboxes 
  // value of checkbox used to add or remove capability to/from a user
  private function form_actions_capabilities_update() {
    // Only allow valid requests (existence of nonce field)
    if (!check_admin_referer('rolecap_update_capabilities')) {
      $this->error_logging("inout_reset_to_install didn't have a valid referer.");
      return false;
    }
    
    global $wp_roles;
    $roles = $wp_roles->roles;
    $capabilities = $this->parse_capabilities(); 

    // Loop each column
    foreach ($roles as $role_key => $role) {
      $role_obj = get_role($role_key);
      // Loop row
      foreach ($capabilities as $capability) {
        $checkbox_name = $role_key . '_' . $capability;  
        // If the checkbox is checked, add the capability; otherwise, remove it.
        // Prevent adminstrator losing manage_options! 
        if ($checkbox_name != "administrator_manage_options") {
          // Uses wordpress functions to add/remove capabilities from the role object, rather than manipulate the array/serialised string
          if(isset($_POST[$checkbox_name])) {
            $role_obj->add_cap(sanitize_text_field($capability));
          } else {
            $role_obj->remove_cap(sanitize_text_field($capability));
          }
        } else {
          // SINGLE failure
          $this->error_logging("Cannot remove manage_options from administrator!");
        }
      }
    }

    // Success
    $this->dismissable_success("Capabilities updated successfully.");
  }

  // 
  private function inout_import_serialised_string() {
    // Check nonce or prevent reset if none.
    if (!check_admin_referer('rolecap_import_rolecap')) {
      $this->error_logging("inout_import_serialised_string didn't have a valid referer.", true);
      return;
    }
    // Required string from post
    if(!isset($_POST['import_string'])) {
      $this->error_logging("inout_import_serialised_string didn't have a valid import_string field.", true);
      return;
    }

    $import_str = trim($_POST['import_string']);
    // is_serialised is a wordpress function
    // string must match this format to be "genuine"
    if(!is_serialized($import_str)) {
      $this->error_logging("inout_import_serialised_string import_string was not a serialised string.", true);
      return;
    }
    // serialised string must contain the manage_options AND have it set to true for at least one user group
    // most wordpress admin checks look for this option rather than user type or any other setting
    // so this check prevents killing the admin panel
    if(strpos($import_str, 's:14:\"manage_options\";b:1;') === false) {
      $this->error_logging("inout_import_serialised_string didn't contain a manage_options setting set to true.\n", true);
      return;
    }

    $safe_str = unserialize(stripslashes($import_str)); // Use @ to suppress errors during debugging
    if ($safe_str === false && $import_str !== 'b:0;') {
      $this->error_logging("Unserialize failed. Serialized string: " . $import_str);
      return;
    }

    // All checks passed, now update the option
    global $wp_roles;
    update_option('wp_user_roles', $safe_str);

    // Reload roles
    $wp_roles->roles = $safe_str;
    $wp_roles->role_objects = [];
    $wp_roles->role_names = [];
    foreach ($safe_str as $role_group => $role_values) {
        $wp_roles->role_objects[$role_group] = new WP_Role($role_group, $role_values['capabilities']);
        $wp_roles->role_names[$role_group] = $role_values['name'];
    }

    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    $this->dismissable_success("Roles and Capabilities updated to manual input successfully.", "success");
  }

  // Checks request to reset capabilities has come from legitimate header
  // Gets the original settings, overwrites the current ones
  private function inout_reset_to_install() {
    // Check nonce or prevent reset if none.
    if (!check_admin_referer('rolecap_reset_capabilities')) {
      $this->error_logging("inout_reset_to_install didn't have a valid referer.");
      return;
    }

    // Obtain the array of capabilities as defined when the plugin was stored
    // Validate the array before updating the current capabilities with the old ones
    $original = get_option(self::ROLECAP_CAPABILITIES_BACKUP_ORIGINAL);
    // Required string from post
    if(!isset($original)) {
      $this->error_logging("inout_reset_to_install didn't have a valid import_string field.", true);
      return;
    }

    // All checks passed, now update the option
    global $wp_roles;
    update_option('wp_user_roles', $original);

    // Reload roles
    $wp_roles->roles = $original;
    $wp_roles->role_objects = [];
    $wp_roles->role_names = [];
    foreach ($original as $role_group => $role_values) {
        $wp_roles->role_objects[$role_group] = new WP_Role($role_group, $role_values['capabilities']);
        $wp_roles->role_names[$role_group] = $role_values['name'];
    }

    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    // Success
    $this->dismissable_success("Capabilities restored to state at plugin installation successfully.", "success");
  }

  // Handle post actions of inout form
  // Checks 1) form name is posted, 2) the user has manage_options priveledge, 3) which form action is posted
  // Successful posts produce function calls for the post type.
  public function process_submenu_inout_form() {
    // Function should only run if requested by form submission, not code
    if (!isset($_POST['capability_form_action'])) {
      $this->error_logging("process_submenu_inout_form didn't get called from a POST request.");
      return false;
    }
    // Should only allow users with manage_options ability, typically Administrators 
    if (!current_user_can('manage_options')) {
      $this->error_logging("process_submenu_inout_form not coming from a user with permission.");
      return false;
    }

    // Update form
//    if($_POST['capability_form_action'] === 'update_capabilities') {
//      $this->form_actions_capabilities_update();
//    }

    // Import form in middle of page
    if($_POST['capability_form_action'] === 'import_capabilities') {
      $this->inout_import_serialised_string();
    }

    // Reset form at bottom of page
    if($_POST['capability_form_action'] === 'reset_capabilities') {
      $this->inout_reset_to_install();
      //die("Error here2");
    }
    return true;
  }

  /*
  -
  - Code above: specific functions 
  - Code below: general plugin activation, installation, update, admin display hooks, error logging
  -
  */

  // Display a dismissable notice with translated message
  // TODO: modify for success, warning, error
  private function dismissable_success($message, $type){
    $message = esc_html__($message, "rolecap");
    $class = "notice notice-{$type} is-dismissible";
    add_action('admin_notices', function() use ($message, $class) {
      echo "<div class='{$class}'><p>{$message}</p></div>";
    });
  }

  // Prefix all error messages with [RoleCap plugin] for quick searching
  private function error_logging($message, $force_notify = false) {
    $user = wp_get_current_user();
    $role = !empty($user->roles) ? $user->roles[0] : 'No role assigned';
    error_log("[RoleCap plugin] {$message} | User:{$user->ID}, Role: {$role}");

    if($force_notify !== false) {
      $this->dismissable_success($message, "error");
    }
  }
  
  // HTML output of import/export submenu
  public function display_submenu_importexport() {
    $roles = get_option('wp_user_roles');
    $roles = serialize($roles);
    $original = get_option(self::ROLECAP_CAPABILITIES_BACKUP_ORIGINAL);
    
    include_once plugin_dir_path(__FILE__)."/view/submenu_inout.php";
    return true;
  }
  
  // HTML output of capabilities submenu
  public function display_submenu_capabilities() {
    $roles = get_option('wp_user_roles');
    $capabilities = $this->parse_capabilities();
    
    include_once plugin_dir_path(__FILE__)."/view/submenu_capabilities.php"; 
    return true;
  }

  // HTML output of roles submenu
  public function display_submenu_roles() {
    $roles = get_option('wp_user_roles');

    include_once plugin_dir_path(__FILE__)."/view/submenu_roles.php"; 
    return true;
  }
  
  // Checks the user is able to manage options then adds a submenu item and hook to the Users admin menu
  public function add_admin_plugin_menu_subs() {
    if (current_user_can('manage_options')) {
      add_submenu_page("_rolecap", "RoleCap", "Roles", "manage_options", "Roles", array($this, 'display_submenu_roles'));
      add_submenu_page("_rolecap", "RoleCap", "Capabilities", "manage_options", "Capabilities", array($this, 'display_submenu_capabilities'));
      add_submenu_page("_rolecap", "RoleCap", "Import/Export", "manage_options", "Import/Export", array($this, 'display_submenu_importexport'));
      return true;
    } else {
      $this->error_logging("User cannot manage_options, cannot add_admin_plugin_menu_subs.");
      return false;
    }
  }

  // Checks the user is able to manage options then adds a menu item for the plugin
  public function add_admin_plugin_menu() {
    if(current_user_can('manage_options')) {
      add_menu_page('RoleCap', 'RoleCap', 'manage_options', '_rolecap', '', 'dashicons-groups', 71);
      add_action('admin_menu', function() { remove_submenu_page('_rolecap', '_rolecap'); }, 99);
    }
  } 

  // All necessary setup for a fresh install
  // Creates a backup of the CURRENT roles and capabilities, not the default WP settings
  private function install_new() {
    $roles = get_option('wp_user_roles');
    update_option(self::ROLECAP_CAPABILITIES_BACKUP_ORIGINAL, $roles, $autoload="no");
  }

  // Plugin version check with placeholder for version update function calls and first time install call 
  // maintains an option which stores the plugin version number against a custom string (in case of future conflicting key names)
  public function version_check() {
    $version = get_option(self::ROLECAP_VERSION_KEY);
    // First time installation assumes no version_key value
    if($version === false) {
        $this->install_new();
        update_option(self::ROLECAP_VERSION_KEY, self::ROLECAP_VERSION_VALUE, $autoload = "no");
    } else {
      //if(version_compare($version, '', '<')) {} // add a new one for each version release
    }
    return true;
  }

  // Prevent activation on unsupported versions of WordPress
  public function rolecap_activation_check() {
    // min version check 
    if (version_compare($GLOBALS['wp_version'], self::ROLECAP_MIN_WP_VERSION, '<')) {
      $this->error_logging("WordPress version too low ({$GLOBALS['wp_version']}). Requires ".self::ROLECAP_MIN_WP_VERSION);
      wp_die(_e("This plugin requires WordPress ".self::ROLECAP_MIN_WP_VERSION." or higher."));   
    }
    return true;
  }

  // Toot toot, beep beep
  public function __construct() {
    register_activation_hook( __FILE__, array($this, 'rolecap_activation_check')); // Check WP version on plugin activation

    add_action('plugins_loaded', [$this, 'version_check']);              // Always check if upgrade functions needed
    add_action('admin_menu',     [$this, 'add_admin_plugin_menu']);      // Add the menu 
    add_action('admin_menu',     [$this, 'add_admin_plugin_menu_subs']); // Add the submenu 'page'
    add_action('admin_init',     [$this, 'process_submenu_inout_form']); // Listen for form submission 
  }
}
} else {
  error_log("[RoleCap plugin] RoleCap class already defined. Should have used a namespace.");
}

new RoleCap();


// on construction loads an activation check to gracefully die if WP version not supported
// then does plugin version check to setup necessary options and installation bits / call upgrade methods
// - installation adds an option which creates a backup of the user roles before the user tampers with them
// then adds a submenu page to the users.php menu IF the user has manage-options capability
