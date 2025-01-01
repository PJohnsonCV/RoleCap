<?php
  if(isset($roles)) {
?>
<div class='wrap'>
  <h1><?php echo esc_html__("Roles","rolecap"); ?></h1>
  <p><?php esc_html__("Manage user roles.", "rolecap"); ?></p>
  <p>List the roles and edit in-line here</p>
  <p>Space to add a new role type here (set capabilities to none + level 0)</p>
</div>
<?php
  }