<?php
class HipmobWindow
{
  private $plugin_name;
  private $plugin_file;
  private $nonce_name;
  
  public function __construct($plugin_name, $plugin_file)
  {
    $this->plugin_name = $plugin_name;
    $this->plugin_file = $plugin_file;
  }

  public function display()
  {
    // verify that we can actually render right: the only thing we need is an app id
    $app_id = get_option('hipmob_app_id');
    if(!$app_id) return;
    $res = '<script type="text/javascript">var _hmc = _hmc || [];_hmc.push([\'app\', \''.esc_js(trim($app_id)).'\']);';
    
    // and, start building the bits: title
    $opt = get_option('hipmob_title');
    if($opt) $res .= '_hmc.push([\'title\', \''.esc_js(trim($opt)).'\']);';
    else $res .= '_hmc.push([\'title\', \'Talk to us\']);';
    
    // width/height
    $settings = array();
    $tab = array();
    $opt = get_option('hipmob_window_width'); if($opt) $settings['width'] = intval(trim($opt));
    $opt = get_option('hipmob_window_height'); if($opt) $settings['height'] = intval(trim($opt));

    // userlabel
    $opt = get_option('hipmob_userlabel'); if($opt) $settings['userlabel'] = trim($opt);

    // placeholder
    $opt = get_option('hipmob_placeholder'); if($opt) $settings['placeholder'] = trim($opt);

    // position
    $opt = get_option('hipmob_tab_position'); if($opt) $settings['position'] = trim($opt);
    
    // tab offset
    $opt = get_option('hipmob_tab_offset'); if($opt) $settings['offset'] = intval(trim($opt));

    $opt = get_option('hipmob_theme');
    if($opt == "gmail"){
      $tab['background-color'] = '#222222'; $tab['color'] = '#FFFFFF'; $tab['font-size'] = '12px'; $tab['font-weight'] = 'bold';
      $settings['border-color'] = '#222222';
      $res .= "_hmc.push(['statusicons', {'online': '".plugins_url()."/hipmob/themes/gmail/online.png','offline': '".plugins_url()."/hipmob/themes/gmail/offline.png','disconnected': '".plugins_url()."/hipmob/themes/gmail/disconnected.png' }]);_hmc.push(['controlicons', {'open': '".plugins_url()."/hipmob/themes/gmail/open.png','close': '".plugins_url()."/hipmob/themes/gmail/close.png' }]);";
    }else if($opt == "fb"){
      $tab['background-color'] = '#EBEEF3'; $tab['color'] = '#333333'; $tab['font-family'] = 'lucida grande,tahoma,verdana,arial,sans-serif'; $tab['font-size'] = '12px';
      $settings['border-color'] = '#BBC2CD';
      $res .= "_hmc.push(['statusicons', {'online': '".plugins_url()."/hipmob/themes/fb/online.png','offline': '".plugins_url()."/hipmob/themes/fb/offline.png','disconnected': '".plugins_url()."/hipmob/themes/fb/disconnected.png' }]);_hmc.push(['controlicons', {'open': '".plugins_url()."/hipmob/themes/fb/open.png','close': '".plugins_url()."/hipmob/themes/fb/close.png' }]);";
    }else if($opt == "fbactive"){
      $tab['background-color'] = '#6e87b1'; $tab['color'] = '#FFFFFF'; $tab['font-family'] = 'lucida grande,tahoma,verdana,arial,sans-serif'; $tab['font-size'] = '12px'; $tab['font-weight'] = 'bold';
      $settings['border-color'] = '#3D5E95';  
      $res .= "_hmc.push(['statusicons', {'online': '".plugins_url()."/hipmob/themes/fbactive/online.png','offline': '".plugins_url()."/hipmob/themes/fbactive/offline.png','disconnected': '".plugins_url()."/hipmob/themes/fbactive/disconnected.png' }]);_hmc.push(['controlicons', {'open': '".plugins_url()."/hipmob/themes/fbactive/open.png','close': '".plugins_url()."/hipmob/themes/fbactive/close.png' }]);";
    }else{
      // tab
      $opt = get_option('hipmob_tab_background_color'); if($opt) $tab['background-color'] = trim($opt);
      $opt = get_option('hipmob_tab_text_color'); if($opt) $tab['color'] = trim($opt);
    }
    if(function_exists('json_encode')){
      $res .= "_hmc.push(['settings', ".json_encode($settings)."]);";
      $res .= "_hmc.push(['tab', ".json_encode($tab)."]);";
    }
    
    // force the text input height (to avoid confusion from the default theme)
    $res .= "_hmc.push(['input', { height: '40px' }]);";

    $res .= '(function(){ var hm = document.createElement(\'script\'); hm.type = \'text/javascript\'; hm.async = true; hm.src = (\'https:\' == document.location.protocol ? \'https://\' : \'http://\') + \'hipmob.s3.amazonaws.com/hipmobchat.min.js\'; var b = document.getElementsByTagName(\'script\')[0]; b.parentNode.insertBefore(hm, b);})();</script>';
    return $res;
  }
}
