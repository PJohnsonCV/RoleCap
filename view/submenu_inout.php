<?php
  if(isset($original)) {
?>
<div class='wrap'>
  <h1><?php echo esc_html__("Import / Export Settings","rolecap"); ?></h1>
  <p><?php echo esc_html__("Options to view your current settings, import different settings, and restore your roles and capabilities back to the point you installed the plugin.", "rolecap"); ?></p>
  <h2>Export</h2>
  <p><?php echo esc_html__("Your current settings. Select all and copy into a file to keep.","rolecap"); ?></p>
  <textarea cols='80' rows='8' readonly><?php echo $roles; ?></textarea>
  <hr>
  <h2>Import</h2>
  <p class='notice notice-error'><?php echo esc_html__("You could brick your backend access if you enter invalid settings.", "rolecap"); ?></p>
  <p class='notice notice-warning'><?php echo esc_html__("Check fixes.php in the RoleCap plugin folder for details how to fix.", "rolecap"); ?></p>
  <p><?php echo esc_html__("Enter a valid serialized string for roles and capabilities and click 'Import'.","rolecap"); ?></p>
  <form method='post' action=''>
    <?php echo wp_nonce_field('rolecap_import_rolecap'); ?>
    <input type='hidden' name='capability_form_action' value='import_capabilities'>
    <textarea cols='80' rows='8' name='import_string'></textarea><br>
    <input type='submit' class='button button-secondary' value='<?php echo esc_html__("Import", "rolecap"); ?>'>
  </form>
  <hr>
  <h2>Revert to Installation</h2>
  <form method='post' action=''>
    <?php echo wp_nonce_field('rolecap_reset_capabilities'); ?>
    <input type='hidden' name='capability_form_action' value='reset_capabilities'>
    <p><?php echo esc_html__("Restore the capabilities available when the plugin was installed:","rolecap"); ?>
    <br>
    <textarea cols='80' rows='4' readonly><?php echo serialize(($original)); ?></textarea>
    <br>
    <input type='submit' class='button button-secondary' value='<?php echo _e("Reset Defaults", "rolecap"); ?>'></p>
  </form>
</div>
<?php
  }
