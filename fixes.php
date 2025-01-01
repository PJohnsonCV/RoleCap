<?php
// Copy this file to your main wordpress folder, where wp-admin, wp-content, and wp-includes live
// The file needs to be in root to make use of wp-load.php
// Scroll to the bottom of this file to uncomment the appopriate function you need to use (read the comments)

if(!class_exists('FixRoleCap')){
  class FixRoleCap {
    public function __construct() {
      // Load WordPress environment
      require_once( dirname(__FILE__) . '/wp-load.php' );
      if (!defined('ABSPATH')) {
        die("<br>If the following directory is not your root wordpress folder, this will not work: <br>". dirname(__FILE__));
      } else {
        echo "<br>Loaded WP installation, moving to fixing roles and capabilities...<br>";
      }
    }

    public function fix_admin_no_manage_options() {
      add_action('init', function() {
        // Get the administrator role
        $role = get_role('administrator');
    
        // Check if the role exists and add the manage_options capability
        if ($role) {
          $role->add_cap('manage_options');
        }
      });
    }

    public function fix_wp_populate_roles() {
      if (function_exists('populate_roles')) {
        populate_roles();
        echo 'Default roles and capabilities have been restored.';
      } else {
        echo 'populate_roles function is not available, moving to full_restore';
        $this->fix_full_restore();
      }
    }

    public function fix_full_restore() {
      // Remove existing roles to ensure a fresh setup
      remove_role('administrator');
      remove_role('editor');
      remove_role('author');
      remove_role('contributor');
      remove_role('subscriber');
  
      // Add default roles with capabilities
  
      // Administrator role
      add_role('administrator', 'Administrator', [
          'switch_themes' => true,
          'edit_themes' => true,
          'activate_plugins' => true,
          'edit_plugins' => true,
          'edit_users' => true,
          'edit_files' => true,
          'manage_options' => true,
          'moderate_comments' => true,
          'manage_categories' => true,
          'manage_links' => true,
          'upload_files' => true,
          'import' => true,
          'unfiltered_html' => true,
          'edit_posts' => true,
          'edit_others_posts' => true,
          'edit_published_posts' => true,
          'publish_posts' => true,
          'edit_pages' => true,
          'read' => true,
          'level_10' => true,
          'level_9' => true,
          'level_8' => true,
          'level_7' => true,
          'level_6' => true,
          'level_5' => true,
          'level_4' => true,
          'level_3' => true,
          'level_2' => true,
          'level_1' => true,
          'level_0' => true,
          'delete_others_posts' => true,
          'delete_private_posts' => true,
          'delete_published_posts' => true,
          'delete_posts' => true,
          'delete_pages' => true,
          'delete_others_pages' => true,
          'delete_published_pages' => true,
          'delete_private_pages' => true,
          'edit_private_pages' => true,
          'edit_private_posts' => true,
          'edit_published_pages' => true,
          'publish_pages' => true,
          'edit_others_pages' => true,
          'read_private_pages' => true,
          'read_private_posts' => true,
          'delete_users' => true,
          'create_users' => true,
          'unfiltered_upload' => true,
          'edit_dashboard' => true,
          'update_plugins' => true,
          'delete_plugins' => true,
          'install_plugins' => true,
          'update_themes' => true,
          'install_themes' => true,
          'update_core' => true,
          'list_users' => true,
          'remove_users' => true,
          'promote_users' => true,
          'edit_theme_options' => true,
          'delete_themes' => true,
          'export' => true
      ]);
  
      // Editor role
      add_role('editor', 'Editor', [
          'delete_others_pages' => true,
          'delete_others_posts' => true,
          'delete_pages' => true,
          'delete_posts' => true,
          'delete_private_pages' => true,
          'delete_private_posts' => true,
          'delete_published_pages' => true,
          'delete_published_posts' => true,
          'edit_others_pages' => true,
          'edit_others_posts' => true,
          'edit_pages' => true,
          'edit_posts' => true,
          'edit_private_pages' => true,
          'edit_private_posts' => true,
          'edit_published_pages' => true,
          'edit_published_posts' => true,
          'manage_categories' => true,
          'manage_links' => true,
          'moderate_comments' => true,
          'publish_pages' => true,
          'publish_posts' => true,
          'read' => true,
          'read_private_pages' => true,
          'read_private_posts' => true,
          'unfiltered_html' => true,
          'upload_files' => true,
      ]);
  
      // Author role
      add_role('author', 'Author', [
          'delete_posts' => true,
          'delete_published_posts' => true,
          'edit_posts' => true,
          'edit_published_posts' => true,
          'publish_posts' => true,
          'read' => true,
          'upload_files' => true,
      ]);
  
      // Contributor role
      add_role('contributor', 'Contributor', [
          'delete_posts' => true,
          'edit_posts' => true,
          'read' => true,
      ]);
  
      // Subscriber role
      add_role('subscriber', 'Subscriber', [
          'read' => true,
      ]);
    }
  }

  //echo "Page found.";
  $fix = new FixRoleCap();
  
  // Use this if the admin account lost manage_options capability
  //$fix->fix_admin_no_manage_options();
  //

  // Use this if all the roles and capabilities are wiped
  // tries to do it the wordpress way, will fallback to a manual method if not
  //

  // If you can't use the fixes.php page, but do have access to your wp databse via phpMyAdmin or something else,
  // Use the following update:
  // UPDATE wp_options SET option_value = 'a:1:{s:10:"administrator";a:61:{s:13:"switch_themes";b:1;s:11:"edit_themes";b:1;s:16:"activate_plugins";b:1;s:12:"edit_plugins";b:1;s:10:"edit_users";b:1;s:10:"edit_files";b:1;s:14:"manage_options";b:1;s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:7:"upload_files";b:1;s:6:"import";b:1;s:14:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:11:"edit_others_posts";b:1;s:16:"edit_published_posts";b:1;s:12:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:6:"level_10";b:1;s:6:"level_9";b:1;s:6:"level_8";b:1;s:6:"level_7";b:1;s:6:"level_6";b:1;s:6:"level_5";b:1;s:6:"level_4";b:1;s:6:"level_3";b:1;s:6:"level_2";b:1;s:6:"level_1";b:1;s:6:"level_0";b:1;s:16:"delete_others_posts";b:1;s:16:"delete_private_posts";b:1;s:18:"delete_published_posts";b:1;s:13:"delete_posts";b:1;s:13:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:21:"delete_published_pages";b:1;s:19:"delete_private_pages";b:1;s:16:"edit_private_pages";b:1;s:16:"edit_private_posts";b:1;s:18:"edit_published_pages";b:1;s:17:"publish_pages";b:1;s:18:"edit_others_pages";b:1;s:19:"read_private_pages";b:1;s:19:"read_private_posts";b:1;s:15:"delete_users";b:1;s:13:"create_users";b:1;s:17:"unfiltered_upload";b:1;s:15:"edit_dashboard";b:1;s:14:"update_plugins";b:1;s:15:"delete_plugins";b:1;s:15:"install_plugins";b:1;s:14:"update_themes";b:1;s:14:"install_themes";b:1;s:12:"update_core";b:1;s:12:"list_users";b:1;s:15:"remove_users";b:1;s:16:"promote_users";b:1;s:17:"edit_theme_options";b:1;s:13:"delete_themes";b:1;s:7:"export";b:1;}}' WHERE option_name = 'wp_roles';
  //
  
  $fix->fix_wp_populate_roles();
} else {
  echo "Conflicting Class FixRoleCap exists, cannot run.";
}