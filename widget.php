<?php

/**
 * Description of widget
 *
 * @author kwisatz
 */
class Awpp_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
                'awpp_widget',  // Base ID
                'Awpp Widget',  // Name
                array('description' => __('Awpp widget') )  // Args
        );
    }
    
   /**
     *  Register the widget class
     */
    public function register_awpp_widget(){
        register_widget('AWPP_Widget');
    }  
    
    public function form( $instance ){
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
	} else {
            $title = __( 'New title', 'text_domain' );
	}
        
        if ( isset( $instance[ 'region' ] ) ) {
            $region = $instance[ 'region' ];
	} else {
            $region = 'center';
	}
        
         if ( isset( $instance[ 'limit' ] ) ) {
            $limit = $instance[ 'limit' ];
	} else {
            $limit = 5;
	}
        
        print('<p><label for="' . $this->get_field_id('title') . '"' . _e( "Title") . '</label>');
        print('<input class="widefat" id="' . $this->get_field_id( 'title' ) 
                . '" name="' . $this->get_field_name( 'title' ) 
                . '" type="text" value="' . esc_attr( $title ) . '"/></p>');
        print('<p><label for="' . $this->get_field_id('region') . '"' . _e( "Region") . '</label>');
        print('<input class="widefat" id="' . $this->get_field_id( 'region' ) 
                . '" name="' . $this->get_field_name( 'region' ) 
                . '" type="text" value="' . esc_attr( $region ) . '"/></p>');
        print('<p><label for="' . $this->get_field_id('type') . '"' . _e( "Type") . '</label>');
        print('<input class="widefat" id="' . $this->get_field_id( 'type' ) 
                . '" name="' . $this->get_field_name( 'type' ) 
                . '" type="text" value="' . esc_attr( $title ) . '"/></p>');
        print('<p><label for="' . $this->get_field_id('limit') . '"' . _e( "Limit") . '</label>');
        print('<input class="widefat small-text" id="' . $this->get_field_id( 'limit' ) 
                . '" name="' . $this->get_field_name( 'limit' ) 
                . '" type="number" value="' . esc_attr( $limit ) . '"/></p>');
    }
    
    public function update( $new_instance, $old_instance ){
        $instance = array();
	$instance['title'] = strip_tags( $new_instance['title'] );
        $instance['region'] = strip_tags( $new_instance['region'] );
        $instance['limit'] = strip_tags( $new_instance['limit'] );
	return $instance;
    }
    
    public function widget( $args, $instance ){
        extract( $args );
	$title = apply_filters( 'widget_title', $instance['title'] );
	echo $before_widget;
	if ( ! empty( $title ) )
		echo $before_title . $title . $after_title;
        
        //echo __( 'Hello, World!', 'text_domain' );
        $sc = new AWPP_Shortcode();
        print( $sc->create_annuaire_list( array(
            'region' => $instance['region'],
            'photos' => false,
            'limit' => $instance['limit']
            )
         ) );
        
	echo $after_widget;
    }


}

?>
