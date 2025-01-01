<?php
  if(isset($roles) && isset($capabilities)) {
?>
<div class='wrap'>
  <h1><?php echo esc_html__("Capabilities","rolecap"); ?></h1>
  <p><?php echo esc_html__("Manage user capabilities. You cannot remove 'manage_options' from the Administrator role.", "rolecap"); ?></p>
  <form method='post' action=''>
<?php
    echo wp_nonce_field('rolecap_update_capabilities');
?>
    <input type='hidden' name='capability_form_action' value='update_capabilities'>
      <table class='wp-list-table widefat fixed striped'>
      <thead>
        <tr>
          <th>Capabilities</th>
<?php
    // Output role headers
    foreach ($roles as $role_key => $role) {
      echo "          <th>".esc_html($role['name'])."</th>";
    }
?>
        </tr>
      </thead>
      <tbody>
<?php
    foreach ($capabilities as $capability) {
?>
        <tr>
          <td><?php echo esc_html($capability); ?></td>
<?php        
      // Check each role for this capability
      foreach ($roles as $role_key => $role) {
        $checkbox_name = esc_attr($role_key . '_' . $capability);
        $checked = isset($role['capabilities'][$capability]) && $role['capabilities'][$capability] ? 'checked' : '';
?>    
          <td>
            <input type="checkbox" name="<?php echo esc_html($checkbox_name); ?>" value="1" <?php echo esc_html($checked); ?>>
          </td>
<?php 
      }
?>        
        </tr>
<?php
    }
?>    
      </tbody>
    </table>
    <p><input type='submit' class='button button-primary' value='Save Changes'></p>
  </form>
  
<?php
  }