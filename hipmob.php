<?php
/**
 * @package Hipmob
 * @version 1.2.1
 */
/*
Plugin Name: Hipmob
Plugin URI: https://www.hipmob.com/documentation/integrations/wordpress.html
Description: Adds a Hipmob live chat tab to your website. Use the [hipmob_enabled] and [hipmob_disabled] shortcodes to control the display on each page.
Author: Orthogonal Labs, Inc
Version: 1.2.1
Author URI: https://www.hipmob.com/documentation/integrations/wordpress.html
*/
/*  Copyright 2012 Femi Omojola (email : femi@hipmob.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( !function_exists( 'add_action' ) ) {
  echo "The Hipmob plugin: will not work when called directly.";
  exit;
}

define('HIPMOB_FOR_WORDPRESS_VERSION', '1.2.1');

class HipmobPlugin
{
  static $active = false;
  static $admin = false;
  static $output_top = false;

  function hipmob_plugin_init()
  {
    // add all the options
    add_option('hipmob_enabled');
    add_option('hipmob_app_id');
    add_option('hipmob_title');
    add_option('hipmob_window_width');
    add_option('hipmob_window_height');
    add_option('hipmob_window_background_color');
    add_option('hipmob_window_text_color');
    add_option('hipmob_tab_position');
    add_option('hipmob_output_position');

    // add us to the footer
    add_action('wp_footer', array(__CLASS__, "hipmob_plugin_add_chat_tab_footer"), 100);
    add_action('wp_head', array(__CLASS__, "hipmob_plugin_add_chat_tab_header"), 100);
    
    // see if we're enabled
    if(get_option("hipmob_enabled")) self::$active = true;
    if(get_option("hipmob_output_position")) self::$output_top = true;

    // and add us to the menus if we should be enabled
    if((function_exists('current_user_can') && current_user_can('manage_options')) || 
       (function_exists('is_admin') && is_admin())){
      add_action('admin_init', array(__CLASS__, 'hipmob_plugin_admin_init'));
      add_action('admin_menu', array(__CLASS__, 'hipmob_plugin_admin_menu'));
      add_filter('plugin_action_links', array(__CLASS__, 'hipmob_plugin_action_links'), 10, 2);
      register_deactivation_hook(plugin_basename( dirname(__FILE__).'/hipmob.php' ), array(__CLASS__, 'hipmob_plugin_deactivation_hook'));
      add_action('admin_footer', array(__CLASS__, "hipmob_plugin_add_help_chat"), 100);
    }
  }
  
  function hipmob_plugin_version_warning() 
  {
    echo '<div id="hipmob_version_warning" class="updated fade"><p><strong>'.sprintf(__('Hipmob %s requires WordPress 3.0 or higher.'), HIPMOB_FOR_WORDPRESS_VERSION) .'</strong></p></div>';
  }
  
  function hipmob_plugin_admin_init()
  {
    global $wp_version;
    
    // all admin functions are disabled in old versions
    if ( !function_exists('is_multisite') && version_compare( $wp_version, '3.0', '<' ) ) {
      add_action('admin_notices', array(__CLASS__, 'hipmob_plugin_version_warning')); 
      return; 
    }
    
    // and, register our settings
    add_settings_section('hipmob_settings_section', '', array(__CLASS__, 'hipmob_plugin_section_overview'), 'hipmob-settings-group');
    add_settings_field('hipmob_enabled', 'Enabled', array(__CLASS__, 'hipmob_plugin_settings_enabled'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_app_id', 'Hipmob Application ID', array(__CLASS__, 'hipmob_plugin_settings_app_id'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_title', 'Hipmob Window Title', array(__CLASS__, 'hipmob_plugin_settings_title'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_window_width', 'Hipmob Window Width', array(__CLASS__, 'hipmob_plugin_settings_window_width'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_window_height', 'Hipmob Window Height', array(__CLASS__, 'hipmob_plugin_settings_window_height'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_tab_background_color', 'Tab Background Color', array(__CLASS__, 'hipmob_plugin_settings_tab_background_color'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_tab_text_color', 'Tab Text Color', array(__CLASS__, 'hipmob_plugin_settings_tab_text_color'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_tab_position', 'Tab Position', array(__CLASS__, 'hipmob_plugin_settings_tab_position'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_output_position', 'Add Widget to Page Header', array(__CLASS__, 'hipmob_plugin_settings_output_position'), 'hipmob-settings-group', 'hipmob_settings_section');

    register_setting('hipmob-settings-group', 'hipmob_enabled');
    register_setting('hipmob-settings-group', 'hipmob_app_id');
    register_setting('hipmob-settings-group', 'hipmob_title');
    register_setting('hipmob-settings-group', 'hipmob_window_width');
    register_setting('hipmob-settings-group', 'hipmob_window_height');
    register_setting('hipmob-settings-group', 'hipmob_tab_background_color');
    register_setting('hipmob-settings-group', 'hipmob_tab_text_color');
    register_setting('hipmob-settings-group', 'hipmob_tab_position');
    register_setting('hipmob-settings-group', 'hipmob_output_position');
  }
  
  function hipmob_plugin_settings_enabled()
  {
    echo '<input name="hipmob_enabled" id="id_hipmob_enabled" type="checkbox" value="true" '. checked("true", get_option('hipmob_enabled'), false) .' /> Enable Hipmob live chat on all pages by default';
  }

  function hipmob_plugin_settings_output_position()
  {
    echo '<input name="hipmob_output_position" id="id_hipmob_output_position" type="checkbox" value="true" '. checked("true", get_option('hipmob_output_position'), false) .' /> Add the Hipmob live chat widget to the &lt;head&gt; of the page (by default the live chat widget is added just before the closing &lt;body&gt; tag): this can fix certain theme errors that prevent the Hipmob live chat widget from appearing';
  }

  function hipmob_plugin_settings_app_id()
  {
    echo '<input style="width: 300px" name="hipmob_app_id" id="id_hipmob_app_id" type="text" value="'. get_option('hipmob_app_id') . '" />';
  }

  function hipmob_plugin_settings_title()
  {
    echo '<input style="width: 400px" name="hipmob_title" id="id_hipmob_title" type="text" value="'. get_option('hipmob_title') . '" placeholder="Talk to us." />';
  }

  function hipmob_plugin_settings_window_width()
  {
    echo '<input style="width: 75px" name="hipmob_window_width" id="id_hipmob_window_width" type="text" value="'. get_option('hipmob_window_width') . '" placeholder="300" /> px';
  }

  function hipmob_plugin_settings_window_height()
  {
    echo '<input style="width: 75px" name="hipmob_window_height" id="id_hipmob_window_height" type="text" value="'. get_option('hipmob_window_height') . '" placeholder="350" /> px';
  }

  function hipmob_plugin_settings_tab_background_color()
  {
    echo '<input style="width: 75px" name="hipmob_tab_background_color" id="id_hipmob_tab_background_color" type="text" value="'. get_option('hipmob_tab_background_color') . '" placeholder="#dedede" />';
  }

  function hipmob_plugin_settings_tab_text_color()
  {
    echo '<input style="width: 75px" name="hipmob_tab_text_color" id="id_hipmob_tab_text_color" type="text" value="'. get_option('hipmob_tab_text_color') . '" placeholder="#383838" />';
  }

  function hipmob_plugin_settings_tab_position()
  {
    $opt = get_option('hipmob_tab_position');
    echo '<select id="id_hipmob_tab_position" name="hipmob_tab_position"><option value="bottomright" '. selected('bottomright', $opt, false).'>Bottom Right</option><option value="bottomcenter" '. selected('bottomcenter', $opt, false).'>Bottom Center</option><option value="bottomleft" '. selected('bottomleft', $opt, false).'>Bottom Left</option><option value="topright" '. selected('topright', $opt, false).'>Top Right</option><option value="topcenter" '. selected('topcenter', $opt, false).'>Top Center</option><option value="topleft" '. selected('topleft', $opt, false).'>Top Left</option></select>';
  }

  function hipmob_plugin_section_overview()
  {
    echo '<div>Configure the Hipmob Wordpress chat plugin by providing the application ID (from your Hipmob account) and customize the look and feel. Visit <a href="https://www.hipmob.com/documentation/integrations/wordpress.html" target="_blank">https://www.hipmob.com/documentation/integrations/wordpress.html</a> for more information.</div><div style="margin-top: 10px">Get your free Hipmob account at <a href="https://manage.hipmob.com/" target="_blank">https://manage.hipmob.com/</a>.</div><div style="margin-top: 10px"><strong>NOTE: if you use a cache plugin (such as WP Super Cache) you may need to clear your cache for changes to take effect.</strong></div>';
  }

  function hipmob_plugin_admin_menu()
  {
    add_options_page('Hipmob', 'Hipmob', 'manage_options', 'hipmob-config', array(__CLASS__, 'hipmob_plugin_settings_view'));
  }
  
  function hipmob_plugin_action_links($links, $file)
  {
    if ( $file == plugin_basename( dirname(__FILE__).'/hipmob.php' ) && function_exists("admin_url")) {
      $links[] = '<a href="' . admin_url( 'options-general.php?page=hipmob-config' ) . '">' . __('Settings') . '</a>';
    }
    return $links;
  }
  
  function hipmob_plugin_deactivation_hook()
  {
    unregister_setting('hipmob-settings-group', 'hipmob_enabled');
    unregister_setting('hipmob-settings-group', 'hipmob_app_id');
    unregister_setting('hipmob-settings-group', 'hipmob_title');
    unregister_setting('hipmob-settings-group', 'hipmob_window_width');
    unregister_setting('hipmob-settings-group', 'hipmob_window_height');
    unregister_setting('hipmob-settings-group', 'hipmob_tab_background_color');
    unregister_setting('hipmob-settings-group', 'hipmob_tab_text_color');
    unregister_setting('hipmob-settings-group', 'hipmob_tab_position');
    unregister_setting('hipmob-settings-group', 'hipmob_output_position');
  }
  
  function hipmob_plugin_settings_view()
  {
    if(!((function_exists('current_user_can') && current_user_can('manage_options')) || 
	 (function_exists('is_admin') && is_admin()))) return false;
    self::$admin = true;
    include('hipmob-render-admin.php');
  }
  
  function hipmob_plugin_enable_override($atts, $content)
  {
    self::$active = true;
  }
  
  function hipmob_plugin_disable_override($atts, $content)
  {
    self::$active = false;
  }

  function hipmob_plugin_add_chat_tab_header()
  {
    if(!self::$active) return;
    if(!self::$output_top) return;
    include('hipmob-render.php');
    $view = new HipmobWindow('hipmob', __FILE__);
    echo $view->display();
  }

  function hipmob_plugin_add_chat_tab_footer()
  {
    if(!self::$active) return;
    if(self::$output_top) return;
    include('hipmob-render.php');
    $view = new HipmobWindow('hipmob', __FILE__);
    echo $view->display();
  }

  function hipmob_plugin_add_help_chat()
  {
    if(!self::$admin) return;

    // add the admin chat tab
    echo "<script type=\"text/javascript\">var _hmc = _hmc || [];_hmc.push(['app', '9e0306c589ed413bb3c0e12a7ea7591c']);_hmc.push(['settings', { 'width': '350px' }]);_hmc.push(['title', \"Help me with my Hipmob integration\"]);(function(){ var hm = document.createElement('script'); hm.type = 'text/javascript'; hm.async = true; hm.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'hipmob.s3.amazonaws.com/hipmobchat.min.js'; var b = document.getElementsByTagName('script')[0]; b.parentNode.insertBefore(hm, b); })();</script>";
  }
}

add_action('init', array('HipmobPlugin', 'hipmob_plugin_init'));
add_shortcode('hipmob_enabled', array('HipmobPlugin', 'hipmob_plugin_enable_override'));
add_shortcode('hipmob_disabled', array('HipmobPlugin', 'hipmob_plugin_disable_override'));
