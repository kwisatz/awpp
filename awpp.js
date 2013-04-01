
function awpp_wrapper ($) { 
    var awpp = {
        init: function() {
            this.name = 'Awpp';
            //this.canvas = $('#awpp-map-dom-element');
            // see https://groups.google.com/forum/?fromgroups=#!topic/google-maps-api/hf9h71UVBx0
            this.canvas = document.getElementById( 'awpp-map-dom-element' );
            if( this.canvas )
                this.buildMap();
            else
                $( this.canvas ).html( awpp.name + " error: couldn't retrieve DOM elements." );
        },
        buildMap : function() {
            var mapOptions;
            mapOptions = {
                'zoom' : parseInt( awppMapData.options.zoom ),
                'center' : new google.maps.LatLng( 
                    parseFloat( awppMapData.options.latitude ), 
                    parseFloat( awppMapData.options.longitude ) ),
                'mapTypeId' : google.maps.MapTypeId[ awppMapData.options.type ],
                'mapTypeControl' : awppMapData.options.typeControl == 'off' ? false : true,
                'mapTypeControlOptions' : { style: google.maps.MapTypeControlStyle[ awppMapData.options.typeControl ] },
                'navigationControl' : awppMapData.options.navigationControl == 'off' ? false : true,
                'navigationControlOptions' : { style: google.maps.NavigationControlStyle[ awppMapData.options.navigationControl ] }
            };
            // Override default width/heights from settings
            $( '#awpp-map-dom-element' ).css( 'width', awppMapData.options.mapWidth );
            $( '#awpp-map-dom-element' ).css( 'height', awppMapData.options.mapHeight );
            // Create the map
            try {
                map = new google.maps.Map( this.canvas, mapOptions );
            } catch( e ) {
                $( this.canvas ).html( awpp.name + " error: couln't build map." );
                if( window.console )
                    console.log( 'awpp_buildMap: '+ e );
                return;
            }

            this.addPlacemarks( map );
        },
        addPlacemarks : function( map ) {
            if( awppMapData.markers.length > 0 )
                for( var m in awppMapData.markers ) {
                    this.createMarker( 
                        map, 
                        awppMapData.markers[m]['title'], 
                        awppMapData.markers[m]['latitude'], 
                        awppMapData.markers[m]['longitude'],
                        awppMapData.markers[m]['details'],
                        awppMapData.markers[m]['icon'],
                        parseInt( awppMapData.markers[m]['zIndex'] ) 
                    );
                }
         },
         createMarker : function( map, title, latitude, longitude, details, icon, zIndex ) {
            var infowindowcontent, infowindow, marker;

            if( isNaN( latitude ) || isNaN( longitude ) ) {
                if( window.console )
                    console.log( "awpp_createMarker(): "+ title +" latitude and longitude weren't valid." );
                return false;
            }
            infowindowcontent = '<div class="awpp_placemark"> <h3>'+ title +'</h3> <div>'+ details +'</div> </div>';
            try {
                infowindow = new google.maps.InfoWindow( {
                    content : infowindowcontent,
                    maxWidth : awppMapData.options.infoWindowMaxWidth
                } );
                // Replace commas with periods. Some (human) languages use commas to delimit the fraction from the whole number, but Google Maps doesn't accept that.
                //latitude = parseFloat( latitude.replace( ',', '.' ) );
                //longitude = parseFloat( longitude.replace( ',', '.' ) );

                marker = new google.maps.Marker( {
                   'position'      : new google.maps.LatLng( latitude, longitude ),
                   'map'           : map,
                   'icon'          : icon,
                   'title'         : title,
                   'zIndex'        : 0
                } );

                google.maps.event.addListener( marker, 'click', function() {
                    if( this.previousInfoWindow != undefined )
                        this.previousInfoWindow.close();
                    infowindow.open( map, marker );
                    this.previousInfoWindow = infowindow;
                } );

                return true;

             } catch( e ) {
                $( this.canvas ).append( '<p>' + this.name + " error: couldn't add map placemarks.</p>");            
                if( window.console )
                    console.log( 'awpp_createMarker: '+ e );
            }
        }
    }

    // Kick things off... (wordpress uses jquery in no-conflict mode 
    // (http://codex.wordpress.org/Function_Reference/wp_enqueue_script#jQuery_noConflict_wrappers)
    $( document ).ready( function() {
        awpp.init();
    } );
}

awpp_wrapper( jQuery ); 
