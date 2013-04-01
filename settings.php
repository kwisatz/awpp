<?php

/**
 * Description of AWPP_Settings
 *
 * @author kwisatz
 * Plugin Name: Annuaire Client
 * Plugin URI: 
 * Description: Retrieves configurable entries from annuaire.youth.lu and displays them in various formats
 * Version: 0.1.0
 * Author: David Raison
 * Author URI: http://david.raison.lu
 * License: GPL3
 */

class AWPP_Settings {
    
    public function __construct(){
        // nothing yet
    }
    
    public function add_menu(){
        add_options_page('Annuaire Client Settings', 'Annuaire Client', 'manage_options', 'awpp', array('AWPP_Settings', 'plugin_settings_page'));
    }
    
    public function admin_init(){
        register_setting('awpp-group', 'awpp_options');
        add_settings_section('awpp_main', 'Main Settings', array(&$this, 'awpp_section_text'), 'awpp');
        add_settings_field('awpp_photo_width', 'Default maximum photo width (px)', array( 'AWPP_Settings', 'awpp_photo_width_input'), 'awpp', 'awpp_main');
                
        add_settings_section('awpp_map', 'Map Settings', array(&$this, 'awpp_section_map_text'), 'awpp');
        add_settings_field('awpp_map_width', 'Default map width (px)', array( 'AWPP_Settings', 'awpp_map_width_input'), 'awpp', 'awpp_map');
        add_settings_field('awpp_map_height', 'Default map height (px)', array( 'AWPP_Settings', 'awpp_map_height_input'), 'awpp', 'awpp_map');
        add_settings_field('awpp_map_center', 'Center map here', array( 'AWPP_Settings', 'awpp_map_center_input'), 'awpp', 'awpp_map');
    }
    
    public function awpp_section_text(){
        print('<p>See <a href="http://annuaire.youth.lu/fr/help/api/v1/guide">the API guide</a> for more information on the settings to put here.</p>');
    }
    
    public function awpp_section_map_text(){
        print('<p>Define your default map settings here.</p>');
    }
    
    public function awpp_photo_width_input(){
        $options = get_option('awpp_options');
        print('<input type="number" step="10" min="0" id="awpp_photo_width_input"'
                . 'name="awpp_options[photo_width]" size="40" class="small-text"'
                . 'value="' . $options['photo_width'] . '"/>');
    }
    
    public function awpp_map_width_input(){
        $options = get_option('awpp_options');
        print('<input type="number" step="10" min="0" id="awpp_map_width_input"'
                . 'name="awpp_options[map_width]" size="40" class="small-text"'
                . 'value="' . $options['map_width'] . '"/>');
    }
    
    public function awpp_map_height_input(){
        $options = get_option('awpp_options');
        print('<input type="number" step="10" min="0" id="awpp_map_height_input"'
                . 'name="awpp_options[map_height]" size="40" class="small-text"'
                . 'value="' . $options['map_height'] . '"/>');
    }
    
    public function awpp_map_center_input(){
        $options = get_option('awpp_options');
        print('<input type="text" step="10" min="0" id="awpp_map_center_input"'
                . 'name="awpp_options[map_center]" size="40" class="medium-text"'
                . 'value="' . $options['map_center'] . '"/>');
    }
    
    /*
    public function awpp_type_input(){
        $options = get_option('awpp_options');
        print('<input type="text" id="awpp_type_input" name="awpp_options[org_type]" size="40" value="' . $options['org_type'] . '"/>');
    }
    
    public function awpp_region_input(){
        $options = get_option('awpp_options');
        print('<input type="text" id="awpp_region_input" name="awpp_options[region]" size="40" value="' . $options['region'] . '"/>');
    }
    
    public function awpp_content_input(){
        $options = get_option('awpp_options');
        print('<input type="text" id="awpp_content_input" name="awpp_options[content]" size="40" value="' . $options['content'] . '"/>');
    }
     * */
         
    public function plugin_settings_page(){
        if(!current_user_can('manage_options')){
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        print('<div class="wrap">');
        screen_icon();
        print('<h2>Annuaire Client</h2>');
        print('<p>The annuaire client will use these settings as defaults, but you can override them on individual displays using shortcode arguments.</p>');
        print('<form method="post" action="options.php">');
        settings_fields('awpp-group');
        do_settings_sections( 'awpp' );
        submit_button();
        print('</form></div>');
    }
}

?>
