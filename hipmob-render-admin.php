<div class="wrap">
   <h2><?php _e('Hipmob'); ?></h2>
   <div class="narrow">
   <form method="post" action="options.php" id="hipmob-config" style="width: 600px; ">
   <?php settings_fields( 'hipmob-settings-group' ); ?>
   <?php do_settings_sections( 'hipmob-settings-group' ); ?>
   <p class="submit">
   <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
   </p>
   </form>
   </div>
   </div>
