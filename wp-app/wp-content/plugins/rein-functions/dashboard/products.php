<?php
if( !defined('ABSPATH') ){ exit; }

function epcl_products_page(){
	$cache = get_transient( 'epcl_feed_products' );
    $user_id = get_current_user_id();
    update_user_meta( $user_id, EPCL_THEMESLUG . '_themes_visited', true);
	if ( false === $cache ) {
        $url = 'https://estudiopatagon.com/feed-products/?theme=rein-wp';
		$feed = wp_remote_get( esc_url_raw( $url ), array(
					'sslverify' => false,
					'timeout' => 30,
				) );
		
		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'epcl_feed_products', $cache, 3600 );
			}
		} else {
            $cache = '<div class="error"><p>' . __( 'There was an error retrieving the products list from the server. Please try again later.', 'epcl-framework' ) . '</div>';			
		}
	}

	echo $cache;
}

// delete_transient('epcl_feed_products');