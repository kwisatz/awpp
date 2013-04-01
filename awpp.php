<?php
/* 
 * Plugin Name: Annuaire Client
 * Plugin URI: 
 * Description: Retrieves configurable entries from annuaire.youth.lu and displays them in various formats
 * Version: 0.1.0
 * Author: David Raison
 * Author URI: http://david.raison.lu
 * License: GPL3
 */

// prevent this file from being run outside of wordpress context
if( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
    die( 'Access denied.' );

require_once( dirname(__FILE__) . '/settings.php' );
require_once( dirname(__FILE__) . '/widget.php' );

/**
 * Description of awpp
 *
 * @author kwisatz
 */
class AWPP_Init {
   
       
    public function __construct(){
        // Admin menu
        add_action('admin_init', array( 'AWPP_Settings', 'admin_init'));
        add_action('admin_menu', array( 'AWPP_Settings', 'add_menu'));
        
        // Always? Cf. callback function comment
        //add_action('wp_enqueue_scripts', array( &$this, 'loadResources'), 11);    // that action doesn't seem to work
        add_action('wp', array( &$this, 'loadResources'), 11);
        add_action('wp_head', array( &$this, 'outputHead' ) );
        
        // Widget
        add_action('widgets_init', array('AWPP_Widget', 'register_awpp_widget'));
        
        // Shortcodes http://codex.wordpress.org/Shortcode_API
        $awpp_sc = new AWPP_Shortcode;
        add_shortcode( 'annuaire', array( $awpp_sc, 'create_annuaire_list') ); 
        add_shortcode( 'annuaire-map', array( $awpp_sc, 'create_annuaire_map') );
    }
    
    public function activate(){
        // nothing yet
    }
    
    public function deactivate(){
        // nothing either
    }
    
    public function outputHead(){
        print('<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />');
    }
    
     /**
     * Register and load javascript and stylesheet information
     * @link http://codex.wordpress.org/Function_Reference/wp_register_script
     */
    public function loadResources() {
        wp_register_script(
                'googleMapsAPI',    // handle
                'http'. ( is_ssl() ? 's' : '' ) .'://maps.google.com/maps/api/js?key=AIzaSyD2s8HfAbcc2uaoZ1Nuf8zSwOV9sg5kONI&sensor=false', // src
                array(),            // deps
                false,              // ver
                true                // in_footer
        );
        wp_register_script(
                'awppScript',                       // handle
                plugins_url('awpp.js', __FILE__),   // src
                array( 'googleMapsAPI', 'jquery' ),             // deps
                false,                              // ver
                true                                // in_footer
        );
        wp_register_style(
                'awppStyle',    // handle
                plugins_url('awpp.css', __FILE__),         // src
                array(),            // deps
                false,              // ver
                'all'               // media
        );
        
        // TODO only load when shortcode has been parsed
        wp_enqueue_script( 'googleMapsAPI' );
        wp_enqueue_script( 'awppScript' );
        
        wp_enqueue_style( 'awppStyle' );
    }
    
}

class AWPP_Shortcode {
    
    const PREFIX = 'awpp';
    
    public function __construct(){
        
    }
     /*
     * [annuaire-map] shortcode with region, type and content parameters
     */
    public function create_annuaire_map( $attributes ){
        $options = get_option('awpp_options');
        $geoData = null;
                       
        extract(
                shortcode_atts( 
                        array( 
                            'region' => 'north',
                            'type' => 1,
                            'content' => 'structure',
                            'width' => $options['map_width'],
                            'height' => $options['map_height'],
                            'center' => $options['map_center']
                        ),
                $attributes )
        );
        
        $data = $this->_getDataFromServer( $region, $type, $content );
        
        foreach( $data as $entry ) {
            $geoData[] = $this->_encodeAddress( strip_tags( $entry->titre ), $entry->address );
        } 
                      
        $output = '<div class="wrap">';
        $output .= $this->_displayMap( $geoData, $center, $width, $height );
        $output .= '</div>';
        return '<p>' . $output . '</p>';
    }
    
    /*
     * [annuaire] shortcode with region, type and content parameters
     */
    public function create_annuaire_list( $attributes ){
        $options = get_option('awpp_options');
        $geoData = null;
        
        extract(
                shortcode_atts( 
                        array( 
                            'region' => 'north',
                            'type' => 1,
                            'content' => 'structure',
                            'map' => false,
                            'width' => $options['map_width'],
                            'height' => $options['map_height'],
                            'center' => $options['map_center'],
                            'photos' => true,
                            'limit' => 100
                        ),
                $attributes )
        );
        
        $data = $this->_getDataFromServer( $region, $type, $content );
        $output = '<div class="wrap"><div id="awpp_addresses">';
        $count = 0;    
        
        foreach( $data as $entry ) {
                        
            if( ++$count > $limit ) {
                continue;
            }
                
                $title = strip_tags( $entry->titre );
                    
                $output .= '<p><div class="awpp_heading">' . $title . '</div>';
                $output .= '<div class="awpp_block">'
                        . '<div class="awpp_address">' 
                        . $entry->address->thoroughfare . '<br/>' 
                        . substr( $entry->address->country, 0, 1)
                        . '-' . $entry->address->postal_code 
                        . ' ' . $entry->address->locality.'<br/>'
                        . 'T&eacute;l: ' . $entry->phone;
                   $output .= ( $entry->fax )  ? '<br/>Fax: ' . $entry->fax : '';
                   $output .= ( $entry->link ) ? '<br/><a href="' . $entry->link . '" target="_blank">Homepage</a>' : '';
                $output .= '</div>';
                
                /*
                 * Only display photos if 
                 * 1) they have been requested, 
                 * 2) there is an array of photos and
                 * 3) the first element isn't empty
                 */
                if( $photos && is_array( $entry->photo ) && !empty( $entry->photo[0] ) ) {
                    $options = get_option('awpp_options');
                    $photo = trim( strstr( $entry->photo[0], 'http') );
                    $output .= '<div class="awpp_photo_block">'
                        . '<img src="' . $photo . '" title="'. $title .'" '
                        . 'style="max-width:' . $options['photo_width'] . 'px;"'
                        . 'alt="A photo representing '. $title .'"/>'
                        . '</div>';
                }
                $output .= '<div style="clear:both;"></div></div></p>';
                
                // If map output is requested, encode the geodata
                if ( $map || $map == "yes" ) {
                    $geoData[] = $this->_encodeAddress( $title, $entry->address );
                }
                
            }
                        
            $output .= '</div>';
            
            ( $map == "yes" ) && $output .= $this->_displayMap( $geoData, $center, $width, $height );
            
            $output .= '</div>';    // end wrap
            return '<p>' . $output . '</p>';
    }
    
    private function _getDataFromServer( $region, $type, $content ) {
        $response = wp_remote_get(
            'https://annuaire.youth.lu/webservice/content.json?'
            . 'content=' . $content
            . '&region=' . $region
            . '&type=' . $type);
        
        if ( is_wp_error( $response )) {
            throw new Exception( printf('Something went wrong: %s', $response->get_error_message()) );
        } else {
            if( empty($response['body']) ) {
                throw new Exception( sprintf('Oops, got an empty response!') );
            }
        return json_decode( $response['body'] );
        }
    }
    
    private function _encodeAddress( $name, $data ) {
        $address = $data->thoroughfare 
            . ', ' . $data->postal_code
            . ' ' . $data->locality;
        
        return array(
            'address' => $address,
            'coords' => $this->_googleGeocode( $address ),
            'name' => $name
        );
    }
    
    /**
     * Embedd the DOM element that will be replaced by the actual map
     */
    private function _displayMap( $geoData, $center, $width, $height ){ 
        if ( empty( $geoData ) ) {
            return sprintf('Something went incredibly wrong: No geodata received');
        }
        
        if( !wp_script_is( 'googleMapsAPI', 'queue' ) 
                || !wp_script_is( 'awppScript', 'queue' ) 
                || !wp_style_is( 'awppStyle', 'queue' ) ) {
            return sprintf( __( '<p class="error">%s error: Couldn\'t load resources'), self::PREFIX );
        }
        
        $awppMapData = sprintf(
            "awppMapData.options = %s;\r\nawppMapData.markers = %s",
             json_encode( $this->_getMapOptions( $center, $width, $height ) ),
             json_encode( $this->_getMapPlacemarks( $geoData ) )
        );
        // append arbitrary javascript
        wp_localize_script( 'awppScript', 'awppMapData', array('l10n_print_after' => $awppMapData ) );
                                        
        $output = sprintf( '<div id="%s-map-dom-element">'
                . '<p>'. __( 'Loading map...', 'awpp' ) .'</p>'
                . '<p><img src="%s" alt="'. __( 'Loading', 'awpp' ) .'" /></p>'
                . '</div>',
                self::PREFIX,
                plugins_url( 'images/loading.gif', __FILE__ )
        );
        return $output;
    }
    
    // TODO put this into settings OR for the current right hand side map, do some magic?!
    // e.g. div.height? content.width, etc
    private function _getMapOptions($center, $width, $height ){
        $home = $this->_googleGeocode( $center );
        return array(
            'latitude' => $home['latitude'],
            'longitude' => $home['longitude'],
            'mapWidth' => $width . 'px',
            'mapHeight' => $height . 'px',
            'type'  => 'ROADMAP',
            'zoom' => 9,
            'infoWindowMaxWidth' => 500
        );
    }
    
    private function _getMapPlacemarks( $geoData ){
        foreach( $geoData as $entry ) {
            //$icon = wp_get_attachment_image_src( get_post_thumbnail_id( $pp->ID ) );
            $defaultIcon = apply_filters( self::PREFIX .'default-icon', plugins_url( 'images/default-marker.png', __FILE__ ), $entry['name'] );
            $placemarks[] = array(
                'title'         => $entry['name'],
                'latitude'      => $entry['coords']['latitude'],
                'longitude'     => $entry['coords']['longitude'],
                'details'       => wpautop( $entry['address'] ),
                'icon'          => is_array( $icon ) ? $icon[0] : $defaultIcon
            );
         }
         return $placemarks;
    }
    
   private function _googleGeocode( $address ) {
        $geocodeResponse = wp_remote_get( 
                sprintf( 'http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false',
                        str_replace( ' ', '+', $address ) 
                        )
                );
        if ( is_wp_error( $geocodeResponse )) {
            print('Something went wrong:' . $geocodeResponse->get_error_message());
        }
        if( $geocodeResponse[ 'response' ][ 'code' ] != 200 ) {
            printf(
               __( '<p>%s geocode error: %d %s</p> <p>Response: %s</p>', self::PREFIX ),
               self::PREFIX,
               $geocodeResponse[ 'response' ][ 'code' ],
               $geocodeResponse[ 'response' ][ 'message' ],
               strip_tags( $geocodeResponse[ 'body' ] )
               );
            return false;
        }
        
        // Else decode response and handle geocoding related errors
        $coordinates = json_decode( $geocodeResponse['body'] );
        if( json_last_error() != JSON_ERROR_NONE ) {
            print('Did not get valid json response');
        }
        
        if( isset( $coordinates->status ) && $coordinates->status == 'REQUEST_DENIED' ) {
            printf( __( '%s geocode error: Request Denied.', self::PREFIX), self::PREFIX );
            return false;
        }
        
        if( !isset( $coordinates->results ) || empty( $coordinates->results ) ) {
            print( __( "That address couldn't be geocoded, please make sure that it's correct.", self::PREFIX ) );
            return false;
        }
        
        // If no errors were encountered, we can go on
        return array( 'latitude' => $coordinates->results[ 0 ]->geometry->location->lat,
            'longitude' => $coordinates->results[ 0 ]->geometry->location->lng );
    }
}

if( class_exists( 'AWPP_Init' ) ){
    register_activation_hook(__FILE__, array('AWPP_Init', 'activate') );
    register_deactivation_hook(__FILE__, array('AWPP_Init', 'deactivate') );
    
    $awpp = new AWPP_Init;
    
}


?>
