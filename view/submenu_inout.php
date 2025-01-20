<?php
  if(isset($original)) {
?>
<div class='wrap'>
  <h1><?php echo esc_html__("Import / Export Settings","rolecap"); ?></h1>
  <p><?php echo esc_html__("Options to view your current settings, import different settings, and restore your roles and capabilities back to the point you installed the plugin.", "rolecap"); ?></p>
  <h2>Export</h2>
  <p><?php echo esc_html__("These are your current settings. Select all and copy into a file to keep.","rolecap"); ?></p>
  <textarea cols='80' rows='30' readonly><?php echo $roles; ?></textarea>
  <p></p>
  <hr>
  <h2>Import</h2>
  <p class='notice notice-error'>
    <strong>WARNING</strong> <em><?php echo esc_html__("You could brick your backend access if you enter invalid settings.", "rolecap"); ?></em><br>
    <?php echo esc_html__("It is strongly recommended to create a backup of your wp_options table BEFORE changing any settings using RoleCap."); ?><br>
    <?php echo esc_html__("Check fixes.php in the RoleCap plugin folder for details how to fix some errors, but if you lack database access, you might not be able to repair the site.", "rolecap"); ?>
  </p>
  <form method='post' action=''>
    <?php echo wp_nonce_field('rolecap_import_rolecap'); ?>
    <input type='hidden' name='capability_form_action' value='import_capabilities'>
    <textarea cols='80' rows='8' name='import_string' placeholder='<?php echo esc_html__("Enter a valid serialized string for roles and capabilities and click 'Import'.","rolecap"); ?>'></textarea><br>
    <input type='submit' class='button button-secondary' value='<?php echo esc_html__("Import", "rolecap"); ?>'>
  </form>
  <br>
  <hr>
  <h2>Revert to Installation</h2>
  <p><?php echo esc_html__("These are the roles and capabilities that were set when the RoleCap plugin was installed. Click the Reset Defaults button to revert back to these.","rolecap"); ?></p>
  <form method='post' action=''>
    <?php echo wp_nonce_field('rolecap_reset_capabilities'); ?>
    <input type='hidden' name='capability_form_action' value='reset_capabilities'>
    <textarea cols='80' rows='10' readonly><?php echo serialize(($original)); ?></textarea>
    <br>
    <input type='submit' class='button button-secondary' value='<?php echo _e("Reset Defaults", "rolecap"); ?>'>
  </form>
</div>
<?php
  }
