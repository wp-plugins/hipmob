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
    $opt = get_option('hipmob_window_width'); if($opt) $settings['width'] = intval(trim($opt));
    $opt = get_option('hipmob_window_height'); if($opt) $settings['height'] = intval(trim($opt));
    
    // position
    $opt = get_option('hipmob_tab_position'); if($opt) $settings['position'] = trim($opt);
    if(function_exists('json_encode')) $res .= "_hmc.push(['settings', ".json_encode($settings)."]);";
    
    // tab
    $settings = array();
    $opt = get_option('hipmob_tab_background_color'); if($opt) $settings['background-color'] = trim($opt);
    $opt = get_option('hipmob_tab_text_color'); if($opt) $settings['color'] = trim($opt);
    if(function_exists('json_encode')) $res .= "_hmc.push(['tab', ".json_encode($settings)."]);";
    
    // force the text input height (to avoid confusion from the theme)
    $res .= "_hmc.push(['input', { height: '40px' }]);";

    $res .= '(function(){ var hm = document.createElement(\'script\'); hm.type = \'text/javascript\'; hm.async = true; hm.src = (\'https:\' == document.location.protocol ? \'https://\' : \'http://\') + \'hipmob.s3.amazonaws.com/hipmobchat.min.js\'; var b = document.getElementsByTagName(\'script\')[0]; b.parentNode.insertBefore(hm, b);})();</script>';
    return $res;
  }
}
