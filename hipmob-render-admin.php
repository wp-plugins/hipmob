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
   <script type="text/javascript">jQuery(document).ready(function(){
       var sel = jQuery("#id_hipmob_theme");
       var sync_theme_settings = function(){
	   if(sel.val() == 'gmail'){
	     jQuery("#id_hipmob_tab_background_color").val("#222222");
	     jQuery("#id_hipmob_tab_text_color").val("#FFFFFF");
	   }else if(sel.val() == 'fb'){
	     jQuery("#id_hipmob_tab_background_color").val("#EBEEF3");
	     jQuery("#id_hipmob_tab_text_color").val("#333333");
	   }else if(sel.val() == 'fbactive'){
	     jQuery("#id_hipmob_tab_background_color").val("#6e87b1");
	     jQuery("#id_hipmob_tab_text_color").val("#FFFFFF");
	   }else{
	     jQuery("#id_hipmob_tab_background_color").val("");
	     jQuery("#id_hipmob_tab_text_color").val("");
	   }
       };
       sel.on('change', sync_theme_settings);
       sync_theme_settings();
     });
</script>