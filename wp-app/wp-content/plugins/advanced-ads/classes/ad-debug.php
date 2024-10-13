<?php
class Advanced_Ads_Ad_Debug {
	/**
	 * Prepare debug mode output.
	 *
	 * @param obj Advanced_Ads_Ad
	 */
	public function prepare_debug_output( Advanced_Ads_Ad $ad ) {
		global $post, $wp_query;

		// set size
		if ( $ad->width > 100 && $ad->height > 100 ){
			$width = $ad->width;
			$height = $ad->height;
		} else {
			$width = 300;
			$height = 250;
		}

		$style = "width:{$width}px;height:{$height}px;background-color:#ddd;overflow:scroll;";
		$style_full = 'width: 100%; height: 100vh; background-color: #ddd; overflow: scroll; position: fixed; top: 0; left: 0; min-width: 600px; z-index: 99999;';

		if ( ! empty( $ad->wrapper['id']) ) {
			$wrapper_id = $ad->wrapper['id'];
		} else {
			$wrapper_id = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix() . mt_rand();
		}

		$content = [];

		if ( $ad->can_display( [ 'ignore_debugmode' => true ] ) ) {
			$content[] = __( 'The ad is displayed on the page', 'advanced-ads' );
		} else {
			$content[] = __( 'The ad is not displayed on the page', 'advanced-ads' );
		}

		// compare current wp_query with global wp_main_query
		if ( ! $wp_query->is_main_query() ) {
			$content[] = sprintf( '<span style="color: red;">%s</span>', __( 'Current query is not identical to main query.', 'advanced-ads' ) );
			// output differences
			$content[] = $this->build_query_diff_table();
		}

		if ( isset( $post->post_title ) && isset( $post->ID ) ) {
			$content[] = sprintf( '%s: %s, %s: %s', __( 'current post', 'advanced-ads' ), $post->post_title, 'ID', $post->ID );
		}
		// compare current post with global post
		if ( $wp_query->post !== $post ){
			$error = sprintf( '<span style="color: red;">%s</span>', __( 'Current post is not identical to main post.', 'advanced-ads' ) );
			if ( isset( $wp_query->post->post_title ) && $wp_query->post->ID ) {
				$error .= sprintf( '<br />%s: %s, %s: %s', __( 'main post', 'advanced-ads' ), $wp_query->post->post_title, 'ID', $wp_query->post->ID );
			}
			$content[] = $error;
		}

		$content[] = $this->build_call_chain( $ad );
		$content[] = $this->build_display_conditions_table( $ad );
		$content[] = $this->build_visitor_conditions_table( $ad );

		if ( $message = self::is_https_and_http( $ad ) ) {
			$content[] = sprintf( '<span style="color: red;">%s</span>', $message );
		}

		$content = apply_filters( 'advanced-ads-ad-output-debug-content', $content, $ad );

		ob_start();

		include( ADVADS_ABSPATH . '/public/views/ad-debug.php' );

		$output = ob_get_clean();

		// apply a custom filter by ad type
		$output = apply_filters( 'advanced-ads-ad-output-debug', $output, $ad );
		$output = apply_filters( 'advanced-ads-ad-output', $output, $ad );

		return $output;

	}

	/**
	 * Build table with differences between current and main query
	 *
	 * @since 1.7.0.3
	 */
	protected function build_query_diff_table(){

		global $wp_query, $wp_the_query;

		$diff_current = array_diff_assoc( $wp_query->query_vars, $wp_the_query->query_vars );
		$diff_main = array_diff_assoc( $wp_the_query->query_vars, $wp_query->query_vars );

		if( ! is_array( $diff_current ) || ! is_array( $diff_main ) ){
		return '';
		}

		ob_start();

		?><table><thead><tr><th></th><th><?php _e( 'current query', 'advanced-ads'); ?></th><th><?php _e( 'main query', 'advanced-ads'); ?></th></tr></thead><?php
		foreach( $diff_current as $_key => $_value ){
		?><tr><td><?php echo $_key; ?></td><td><?php echo $_value; ?></td><td><?php if( isset( $diff_main[$_key] ) ) echo $diff_main[$_key]; ?></td></tr><?php
		}
		?></table><?php

		return ob_get_clean();
	}

	/**
	 * Build call chain (placement->group->ad)
	 *
	 * @param obj Advanced_Ads_Ad
	 * @return string
	 */
	protected function build_call_chain( Advanced_Ads_Ad $ad ) {
		ob_start();

		$options = $ad->options();

		printf( '%s: %s (%s)', __( 'Ad', 'advanced-ads' ), esc_html( $ad->title ), $ad->id );

		if ( isset( $options['group_info']['id'] ) && isset( $options['group_info']['name'] ) ) {
			printf( '<br />%s: %s (%s)', _x( 'Ad Group', 'ad group singular name', 'advanced-ads' ), esc_html( $options['group_info']['name'] ), $options['group_info']['id']  );
		}

		if ( isset( $options['output']['placement_id'] ) ) {
			$placements = Advanced_Ads::get_ad_placements_array();
			$placement_id = $options['output']['placement_id'];
			$placement_name = isset( $placements[ $placement_id ]['name'] ) ? $placements[ $placement_id ]['name'] : '';
			printf( '<br />%s: %s (%s)', __( 'Placement', 'advanced-ads' ), esc_html( $placement_name ), esc_html( $placement_id ) );
		}

		return ob_get_clean();
	}

	/**
	 * Build display conditions table.
	 *
	 * @param obj Advanced_Ads_Ad
	 * @return string
	 */
	protected function build_display_conditions_table( Advanced_Ads_Ad $ad ) {
		$options = $ad->options();

		if ( ! isset( $options['conditions'] )
			|| ! is_array( $options['conditions'] )
			|| ! count( $options['conditions'] ) ) { return; }

		$conditions = array_values( $options['conditions'] );
		$display_conditions = Advanced_Ads_Display_Conditions::get_instance()->conditions;
		$the_query = Advanced_Ads_Display_Conditions::get_instance()->ad_select_args_callback( [] );

		ob_start();
		_e( 'Display Conditions', 'advanced-ads' ); ?>
		<?php
		foreach ( $conditions as $_condition ) {
			if ( ! is_array( $_condition )
				|| ! isset( $_condition['type'] )
				|| ! isset( $display_conditions[ $_condition['type'] ]['check'][1] )
			) { continue; }


			printf( '<div style="margin-bottom: 20px; white-space: pre-wrap; font-family: monospace; width: 100%%; background: %s;"><strong>%s</strong>',
				Advanced_Ads_Display_Conditions::frontend_check( $_condition, $ad ) ? '#e9ffe9' : '#ffe9e9',
				$display_conditions[ $_condition['type'] ]['label'] );

			$check = $display_conditions[ $_condition['type'] ]['check'][1];
			if ( $check === 'check_general' ) {
				printf( '<table border="1"><thead><tr><th></th><th>%s</th><th>%s</th></tr></thead>', __( 'Ad', 'advanced-ads' ), 'wp_the_query' );
			} else {
				printf( '<table border="1"><thead><tr><th>%s</th><th>%s</th></tr></thead>', __( 'Ad', 'advanced-ads' ), 'wp_the_query' );
			}

			switch( $check ) {
				case 'check_post_type':
					printf( '<tr><td>%s</td><td>%s</td></tr>',
						( isset( $_condition['value'] ) && is_array( $_condition['value'] ) ) ? esc_html( implode( ',', $_condition['value'] ) ) : '',
						isset( $the_query['post']['post_type'] ) ? $the_query['post']['post_type'] : '' );
					break;
				case 'check_general':
					if ( isset( $the_query['wp_the_query'] ) && is_array( $the_query['wp_the_query'] ) ) {
						$ad_vars = ( isset( $_condition['value'] ) && is_array( $_condition['value'] ) ) ? $_condition['value'] : [];

						if ( in_array( 'is_front_page', $ad_vars ) ) {
							$ad_vars[] = 'is_home';
						}

						foreach ( $the_query['wp_the_query'] as $_var => $_flag ) {
							printf( '<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
								$_var,
								in_array( $_var, $ad_vars ) ? 1 : 0,
								$_flag );
						}
					}
					break;
				case 'check_author':
					printf( '<tr><td>%s</td><td>%s</td></tr>',
						( isset( $_condition['value'] ) && is_array( $_condition['value'] ) ) ? esc_html( implode( ',', $_condition['value'] ) ) : '',
						isset( $the_query['post']['author'] ) ? $the_query['post']['author'] : '' );
					break;
				case 'check_post_ids':
				case 'check_taxonomies':
					printf( '<tr><td>%s</td><td>post_id: %s<br />is_singular: %s</td></tr>',
						( isset( $_condition['value'] ) && is_array( $_condition['value'] ) ) ? esc_html( implode( ',', $_condition['value'] ) ) : '',
						isset( $the_query['post']['id']) ? $the_query['post']['id'] : '',
						! empty( $the_query['wp_the_query']['is_singular'] ) );
					break;
				case 'check_taxonomy_archive':
					printf( '<tr><td>%s</td><td>term_id: %s<br />is_archive: %s</td></tr>',
						( isset( $_condition['value'] ) && is_array( $_condition['value'] ) ) ? esc_html( implode( ',', $_condition['value'] ) ) : '',
						isset( $the_query['wp_the_query']['term_id']  ) ? $the_query['wp_the_query']['term_id'] : '',
						! empty( $the_query['wp_the_query']['is_archive'] ) );
					break;
				default:
					printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( print_r( $_condition, true ) ), print_r( $the_query, true ) );
					break;
			}

			echo '</table></div>';
		}

		return ob_get_clean();
	}

	/**
	 * Build visitor conditions table.
	 *
	 * @param obj Advanced_Ads_Ad
	 * @return string
	 */
	protected function build_visitor_conditions_table( Advanced_Ads_Ad $ad ) {
		$options = $ad->options();

		if ( ! isset( $options['visitors'] )
			|| ! is_array( $options['visitors'] )
			|| ! count( $options['visitors'] ) ) { return; }

		ob_start();

		$visitor_conditions = Advanced_Ads_Visitor_Conditions::get_instance()->conditions;
		?><?php _e( 'Visitor Conditions', 'advanced-ads' );

		foreach ( $options['visitors'] as $_condition ) {
			if ( ! is_array( $_condition )
				|| ! isset( $_condition['type'] )
				|| ! isset( $visitor_conditions[ $_condition['type'] ]['check'][1] )
			) { continue; }

			$content = '';
			foreach ( $_condition as $_k => $_v ) {
				$content .= esc_html( $_k ) . ': ';
				if ( is_array( $_v ) ) {
					$content .= esc_html( implode( ', ', $_v ) );
				} else {
					$content .= esc_html( $_v );
				}
				$content .= '<br />';
			}

			printf( '<div style="margin-bottom: 20px; white-space: pre-wrap; font-family: monospace; width: 100%%; background: %s;">%s</div>',
				Advanced_Ads_Visitor_Conditions::frontend_check( $_condition, $ad ) ? '#e9ffe9' : '#ffe9e9',
				$content );
		}

		return ob_get_clean();
	}

	/**
	 * Check if the current URL is HTTPS, but the ad code contains HTTP.
	 *
	 * @param obj Advanced_Ads_Ad
	 * @return bool false/string
	 */
	public static function is_https_and_http( Advanced_Ads_Ad $ad ) {
		if ( is_ssl()
			&& ( $ad->type === 'plain' || $ad->type === 'content' )
			// Find img, iframe, script. '\\\\' denotes a single backslash
			&& preg_match( '#\ssrc=\\\\?[\'"]http:\\\\?/\\\\?/#i', $ad->content )
		) {
			return __( 'Your website is using HTTPS, but the ad code contains HTTP and might not work.', 'advanced-ads' );
		}

		return false;
	}
}
