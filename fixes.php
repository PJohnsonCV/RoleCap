<?php
// Copy this file to your main wordpress folder, where wp-admin, wp-content, and wp-includes live
// The file needs to be in root to make use of wp-load.php
// Scroll to the bottom of this file to uncomment the appopriate function you need to use (read the comments)

if(!class_exists('FixRoleCap')){
  class FixRoleCap {
    public function __construct() {
      // Load WordPress environment
      echo ("<br>If the following directory is not your root wordpress folder, this will not work: <br>". dirname(__FILE__));
      require_once( dirname(__FILE__) . '/wp-load.php' );
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

  echo "Page found.";
  $fix = new FixRoleCap();
  
  // Use this if the admin account lost manage_options capability
  //$fix->fix_admin_no_manage_options();
  
  // Use this if all the roles and capabilities are wiped
  // tries to do it the wordpress way, will fallback to a manual method if not
  //$fix->fix_wp_populate_roles();
} else {
  echo "Conflicting Class FixRoleCap exists, cannot run.";
}