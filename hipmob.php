<?php
/**
 * @package Hipmob
 * @version 1.7.6
 */
/*
Plugin Name: Hipmob
Plugin URI: https://www.hipmob.com/documentation/integrations/wordpress.html
Description: Adds a Hipmob live chat tab to your website. Use the [hipmob_enabled] and [hipmob_disabled] shortcodes to control the display on each page.
Author: Orthogonal Labs, Inc
Version: 1.7.6
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
if(!function_exists('add_action')){
  echo "The Hipmob plugin: will not work when called directly.";
  exit;
}

define('HIPMOB_FOR_WORDPRESS_VERSION', '1.7.6');

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
    add_option('hipmob_userlabel');
    add_option('hipmob_placeholder');
    add_option('hipmob_window_width');
    add_option('hipmob_window_height');
    add_option('hipmob_window_background_color');
    add_option('hipmob_window_text_color');
    add_option('hipmob_tab_position');
    add_option('hipmob_output_position');
    add_option('hipmob_theme');
    add_option('hipmob_tab_offset');

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
      add_action('admin_enqueue_scripts', array(__CLASS__, 'hipmob_queue_admin_scripts'));
    }
  }
  
  function hipmob_plugin_version_warning() 
  {
    echo '<div id="hipmob_version_warning" class="updated fade"><p><strong>'.sprintf(__('Hipmob %s requires WordPress 3.0 or higher.'), HIPMOB_FOR_WORDPRESS_VERSION) .'</strong></p></div>';
  }
  
  function hipmob_queue_admin_scripts()
  {
    wp_enqueue_script ('hipmob-modal' ,       // handle
		       plugins_url('/hipmob.js', __FILE__),       // source
		       array('jquery-ui-dialog')); // dependencies
  
    // A style available in WP               
    wp_enqueue_style (  'wp-jquery-ui-dialog');
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
    add_settings_field('hipmob_userlabel', 'Hipmob Default User Label', array(__CLASS__, 'hipmob_plugin_settings_userlabel'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_placeholder', 'Hipmob Input Placeholder Text', array(__CLASS__, 'hipmob_plugin_settings_placeholder'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_window_width', 'Hipmob Window Width', array(__CLASS__, 'hipmob_plugin_settings_window_width'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_window_height', 'Hipmob Window Height', array(__CLASS__, 'hipmob_plugin_settings_window_height'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_theme', 'Theme', array(__CLASS__, 'hipmob_plugin_settings_theme'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_tab_background_color', 'Tab Background Color', array(__CLASS__, 'hipmob_plugin_settings_tab_background_color'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_tab_text_color', 'Tab Text Color', array(__CLASS__, 'hipmob_plugin_settings_tab_text_color'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_tab_position', 'Tab Position', array(__CLASS__, 'hipmob_plugin_settings_tab_position'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_output_position', 'Add Widget to Page Header', array(__CLASS__, 'hipmob_plugin_settings_output_position'), 'hipmob-settings-group', 'hipmob_settings_section');
    add_settings_field('hipmob_tab_offset', 'Tab Position Offset', array(__CLASS__, 'hipmob_plugin_settings_tab_offset'), 'hipmob-settings-group', 'hipmob_settings_section');


    register_setting('hipmob-settings-group', 'hipmob_enabled');
    register_setting('hipmob-settings-group', 'hipmob_app_id');
    register_setting('hipmob-settings-group', 'hipmob_title');
    register_setting('hipmob-settings-group', 'hipmob_userlabel');
    register_setting('hipmob-settings-group', 'hipmob_placeholder');
    register_setting('hipmob-settings-group', 'hipmob_window_width');
    register_setting('hipmob-settings-group', 'hipmob_window_height');
    register_setting('hipmob-settings-group', 'hipmob_theme');
    register_setting('hipmob-settings-group', 'hipmob_tab_background_color');
    register_setting('hipmob-settings-group', 'hipmob_tab_text_color');
    register_setting('hipmob-settings-group', 'hipmob_tab_position');
    register_setting('hipmob-settings-group', 'hipmob_output_position');
    register_setting('hipmob-settings-group', 'hipmob_tab_offset');
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
    echo '<input style="width: 240px" name="hipmob_app_id" id="id_hipmob_app_id" type="text" value="'. get_option('hipmob_app_id') . '" />&nbsp;&nbsp;<a id="hipmob_get_appid" style="display: none" class="button-primary">Get your Hipmob app ID</a></div>';
  }

  function hipmob_plugin_settings_title()
  {
    echo '<input style="width: 400px" name="hipmob_title" id="id_hipmob_title" type="text" value="'. get_option('hipmob_title') . '" placeholder="Talk to us." />';
  }

  function hipmob_plugin_settings_userlabel()
  {
    echo '<input style="width: 400px" name="hipmob_userlabel" id="id_hipmob_userlabel" type="text" value="'. get_option('hipmob_userlabel') . '" placeholder="Me" />';
  }

  function hipmob_plugin_settings_placeholder()
  {
    echo '<input style="width: 400px" name="hipmob_placeholder" id="id_hipmob_placeholder" type="text" value="'. get_option('hipmob_placeholder') . '" placeholder="Send us a message" />';
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
    echo '<select id="id_hipmob_tab_position" name="hipmob_tab_position"><option style="padding-right: 5px" value="bottomright" '. selected('bottomright', $opt, false).'>Bottom Right</option><option style="padding-right: 5px" value="bottomcenter" '. selected('bottomcenter', $opt, false).'>Bottom Center</option><option style="padding-right: 5px" value="bottomleft" '. selected('bottomleft', $opt, false).'>Bottom Left</option><option style="padding-right: 5px" value="topright" '. selected('topright', $opt, false).'>Top Right</option><option style="padding-right: 5px" value="topcenter" '. selected('topcenter', $opt, false).'>Top Center</option><option style="padding-right: 5px" value="topleft" '. selected('topleft', $opt, false).'>Top Left</option></select>';
  }

  function hipmob_plugin_settings_tab_offset()
  {
    echo '<input style="width: 75px" name="hipmob_tab_offset" id="id_hipmob_tab_offset" type="text" value="'. get_option('hipmob_tab_offset') . '" placeholder="100" /> px';
  }

  function hipmob_plugin_settings_theme()
  {
    $opt = get_option('hipmob_theme');
    echo '<select id="id_hipmob_theme" name="hipmob_theme"><option style="padding-right: 5px"  value="" '.selected('', $opt, false).'></option><option style="padding-right: 5px" value="gmail" '. selected('gmail', $opt, false).'>Gmail</option><option style="padding-right: 5px" value="fb" '. selected('fb', $opt, false).'>Facebook</option><option style="padding-right: 5px" value="fbactive" '. selected('fbactive', $opt, false).'>Facebook Active</option></select>';
  }

  function hipmob_plugin_section_overview()
  {
    echo '<div><h3>Instructions:</h3><ol>';
    echo '<li>Click the Get your Hipmob app ID button to register for your free Hipmob account and complete the setup of your Hipmob live chat plugin.</li>';
    echo '<li>You can instantly start talking to your visitors <a class="button" target="_blank" href="https://manage.hipmob.com/console">using our browser chat client</a> or <a class="button" target="_blank" href="https://www.hipmob.com/operator#im">using any Jabber/XMPP client</a>.</li></ol></div>';
    echo '<div style="margin-top: 10px">Customize your chat widget: visit <a href="https://www.hipmob.com/documentation/integrations/wordpress.html" target="_blank">https://www.hipmob.com/documentation/integrations/wordpress.html</a> for more information.</div>';

    echo '<div style="margin-top: 10px">Connects to popular CRM tools like Highrise, Salesforce and Zoho CRM to drive sales and conversions.</div>';

    echo '<div style="margin-top: 10px"><strong>NOTE: if you use a cache plugin (such as WP Super Cache) you may need to clear your cache for your changes to take effect.</strong></div>';
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
    unregister_setting('hipmob-settings-group', 'hipmob_userlabel');
    unregister_setting('hipmob-settings-group', 'hipmob_placeholder');
    unregister_setting('hipmob-settings-group', 'hipmob_window_width');
    unregister_setting('hipmob-settings-group', 'hipmob_window_height');
    unregister_setting('hipmob-settings-group', 'hipmob_theme');
    unregister_setting('hipmob-settings-group', 'hipmob_tab_background_color');
    unregister_setting('hipmob-settings-group', 'hipmob_tab_text_color');
    unregister_setting('hipmob-settings-group', 'hipmob_tab_position');
    unregister_setting('hipmob-settings-group', 'hipmob_output_position');
    unregister_setting('hipmob-settings-group', 'hipmob_tab_offset');
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
    global $userdata;
    get_currentuserinfo();
    
    // add the admin chat tab
    $url = "hipmob.s3.amazonaws.com/hipmobchat.min.js";
    $appid = "9e0306c589ed413bb3c0e12a7ea7591c";

    $name = $userdata->display_name . " (".$userdata->user_login.")";
    echo "<script type=\"text/javascript\">var _hmc = _hmc || [];_hmc.push(['app', ".json_encode($appid)."]);_hmc.push(['settings', { 'width': '350px', 'openonmessage': true }]);_hmc.push(['title', \"Help me with my Hipmob integration\"]);_hmc.push(['email',".json_encode(get_option('admin_email'))."]);_hmc.push(['name',".json_encode($name)."]);_hmc.push(['context', ".json_encode("Blog Name: ". get_option("blogname").";Blog URL: ". get_option("siteurl"))."]);_hmc.push(['settings', { 'notify': ['json'] }]); (function(){ var hm = document.createElement('script'); hm.type = 'text/javascript'; hm.async = true; hm.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + ".json_encode($url)."; var b = document.getElementsByTagName('script')[0]; b.parentNode.insertBefore(hm, b); })();</script>";
  }
}

add_action('init', array('HipmobPlugin', 'hipmob_plugin_init'));
add_shortcode('hipmob_enabled', array('HipmobPlugin', 'hipmob_plugin_enable_override'));
add_shortcode('hipmob_disabled', array('HipmobPlugin', 'hipmob_plugin_disable_override'));
