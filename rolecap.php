<?php
/*
Plugin Name: Role Capabilities
Description: View Roles and Capabilities
Version: 0.1.0
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

  // HTML output of capabilities page
  // TODO: make this its own file?
  public function display_submenu() {
    $roles = get_option('wp_user_roles');
    $capabilities = $this->parse_capabilities();
    
    echo "<div class='wrap'>\n";
    echo "          <h1>"._e("Capabilities","rolecap")."</h1>\n";
    echo "          <p>"._e("Manage user capabilities.", "rolecap")."</p>";
    echo "          <form method='post' action=''>\n";
    echo wp_nonce_field('rolecap_update_capabilities');
    echo "          <input type='hidden' name='capability_form_action' value='update_capabilities'>\n";
    echo "          <table class='wp-list-table widefat fixed striped'>\n";
    echo "            <thead>\n";
    echo "              <tr>\n";
    echo "              <th>Capability</th>\n";
    // Output role headers
    foreach ($roles as $role_key => $role) {
      echo "              <th>".esc_html($role['name'])."</th>\n";
    }
    echo "              </tr>\n";
    echo "            </thead>\n";
    echo "            <tbody>\n";

    foreach ($capabilities as $capability) {
        echo "          <tr>\n";
        echo "            <td>" . esc_html($capability) . "</td>\n";
        
        // Check each role for this capability
        foreach ($roles as $role_key => $role) {
            $checkbox_name = esc_attr($role_key . '_' . $capability);
            $checked = isset($role['capabilities'][$capability]) && $role['capabilities'][$capability] ? 'checked' : '';
            
            echo '<td>';
            echo '<input type="checkbox" name="' . esc_html($checkbox_name). '" value="1" ' . esc_html($checked) . '>';
            echo '</td>';
        }
        
        echo "          </tr>\n";
    }
    
    echo "        </tbody>\n";
    echo "      </table>\n";
    echo "      <p><input type='submit' class='button button-primary' value='Save Changes'></p>\n";
    echo "    </form>\n";
    // Reset button 
    $original = get_option(self::ROLECAP_CAPABILITIES_BACKUP_ORIGINAL);
    echo "    <h2>"._e("Reset", "rolecap")."</h2>\n";
    echo "    <form method='post' action=''>\n";
    echo wp_nonce_field('rolecap_reset_capabilities');
    echo "      <input type='hidden' name='capability_form_action' value='reset_capabilities'>\n";
    echo "      <p>"._e("Restore the capabilities available when the plugin was installed:","rolecap")."<br>".esc_html($original)." <input type='submit' class='button button-secondary' value='"._e("Reset Defaults", "rolecap")."'></p>\n";
    echo "    </form>\n";
    echo "  </div>\n";

    return true;
  }

  // Handle post actions of capabilities forms
  public function process_capability_form_submission() {
    // Function should only run if requested by form submission, not code
    if (!isset($_POST['capability_form_action'])) {
      $this->error_logging("process_capability_form_submission didn't get called from a POST request.");
      return false;
    }

    // Should only allow users with manage_options ability, typically Administrators 
    if (!current_user_can('manage_options')) {
      $this->error_logging("process_capability_form_submission not coming from a user with permission.");
      return false;
    }

    // Update form
    if($_POST['capability_form_action'] === 'update_capabilities') {
      $this->form_actions_capabilities_update();
    }
    // Reset form
    if($_POST['capability_form_action'] === 'reset_capabilities') {
      $this->form_actions_capabilities_reset();
    }
    return true;
  }

  // Updates the user capabilities based on form submission
  // Loop each row (capability) by each column (user) to get compound names of checkboxes 
  // value of checkbox used to add or remove capability to/from a user
  private function form_actions_capabilities_update() {
    // Only allow valid requests (existence of nonce field)
    if (!check_admin_referer('rolecap_update_capabilities')) {
      $this->error_logging("form_actions_capabilities_reset didn't have a valid referer.");
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

  // Checks request to reset capabilities has come from legitimate header
  // Gets the original settings, overwrites the current ones
  private function form_actions_capabilities_reset() {
    // Check nonce or prevent reset if none.
    if (!check_admin_referer('rolecap_reset_capabilities')) {
      $this->error_logging("form_actions_capabilities_reset didn't have a valid referer.");
      return;
    }

    // Obtain the array of capabilities as defined when the plugin was stored
    // Validate the array before updating the current capabilities with the old ones
    $original = get_option(self::ROLECAP_CAPABILITIES_BACKUP_ORIGINAL);
    if($original) {
      update_option('wp_user_roles', $original, $autoload="yes");
    }

    // Success
    $this->dismissable_success("Capabilities restored successfully.");
  }

  // Display a dismissable notice with translated message
  // TODO: modify for success, warning, error
  private function dismissable_success($message){
    $message = __($message, "rolecap");
    add_action('admin_notices', function() {
      echo "<div class='notice notice-success is-dismissible'><p>$message</p></div>";
    });
  }

  // Checks the user is able to manage options then adds a submenu item and hook to the Users admin menu
  public function add_submenu_item() {
    if (current_user_can('manage_options')) {
        add_submenu_page("users.php", "RoleCap", "Capabilities", "manage_options", "Capabilities", array($this, 'display_submenu'));
      return true;
    } else {
      $this->error_logging("User cannot manage_options, cannot add_submenu_item.");
      return false;
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
      $this->error_logging("WordPress version too low (".$GLOBALS['wp_version']."). Requires ".self::ROLECAP_MIN_WP_VERSION);
      wp_die(_e("This plugin requires WordPress ".self::ROLECAP_MIN_WP_VERSION." or higher."));   
    }
    return true;
  }

  // Prefix all error messages with [RoleCap plugin] for quick searching
  private function error_logging($message) {
    $user = wp_get_current_user();
    error_log("[RoleCap plugin] ".$message." | User:".$user->ID.", Role: ".$user->roles[0]);
  }

  // Toot toot, beep beep
  public function __construct() {
/*    add_action('init', function() {
      // Get the administrator role
      $role = get_role('administrator');
      
      // Check if the role exists and add the manage_options capability
      if ($role) {
          $role->add_cap('manage_options');
      }
    });
*/
    register_activation_hook( __FILE__, array($this, 'rolecap_activation_check')); // Check WP version on plugin activation

    add_action('plugins_loaded', [$this, 'version_check']); // Always check if upgrade functions needed
    add_action('admin_menu', [$this, 'add_submenu_item']);  // Add the submenu 'page'
    add_action('admin_init', [$this, 'process_capability_form_submission']); // Listen for form submission 
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
