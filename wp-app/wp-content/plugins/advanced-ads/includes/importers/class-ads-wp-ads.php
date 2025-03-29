<?php // phpcs:ignoreFile
/**
 * Ads for WP Ads.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use AdvancedAds\Interfaces\Importer as Interface_Importer;

defined( 'ABSPATH' ) || exit;

/**
 * Ads for WP Ads.
 */
class Ads_WP_Ads extends Importer implements Interface_Importer {

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string {
		return 'ads_wp_ads';
	}

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string {
		return __( 'Ads for WP Ads', 'advanced-ads' );
	}

	/**
	 * Get a description of the importer.
	 *
	 * @return string The description of the importer.
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Get the icon to this importer.
	 *
	 * @return string The icon for the importer.
	 */
	public function get_icon(): string {
		return '<span class="dashicons dashicons-insert"></span>';
	}

	/**
	 * Detect the importer in database.
	 *
	 * @return bool True if detected; otherwise, false.
	 */
	public function detect(): bool {
		return false;
	}

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render_form(): void {
		?>
		<fieldset>
			<p><label><input type="radio" name="import_type" checked="checked" /> <?php esc_html_e( 'Import Ads', 'advanced-ads' ); ?></label></p>
			<p><label><input type="radio" name="import_type" /> <?php esc_html_e( 'Import Groups', 'advanced-ads' ); ?></label></p>
			<p><label><input type="radio" name="import_type" /> <?php esc_html_e( 'Import Placements', 'advanced-ads' ); ?></label></p>
			<p><label><input type="radio" name="import_type" /> <?php esc_html_e( 'Import Settings', 'advanced-ads' ); ?></label></p>
		</fieldset>
		<?php
	}

	/**
	 * Import data.
	 *
	 * @return WP_Error|string
	 */
	public function import() {
		return '';
	}

	public function importampforwp_ads(){
			global $redux_builder_amp;
			$args = array(
					  'post_type' => 'quads-ads'
					);
			$the_query = new WP_Query( $args );
			$ad_count = $the_query->found_posts;
			$post_status = 'publish';
			$amp_options       = get_option('redux_builder_amp');
			$user_id          = get_current_user_id();
			$after_the_percentage_value = '';

			for($i=1; $i<=6; $i++){
			   if($amp_options['enable-amp-ads-'.$i] != 1){
					continue;
			   }
			   $ad_type    =  $amp_options['enable-amp-ads-type-'.$i];
			   if(($ad_type== 'adsense' && (empty($amp_options['enable-amp-ads-text-feild-client-'.$i]) || empty($amp_options['enable-amp-ads-text-feild-slot-'.$i]))) || ($ad_type== 'mgid' && (empty($amp_options['enable-amp-ads-mgid-field-data-pub-'.$i]) || empty($amp_options['enable-amp-ads-mgid-field-data-widget-'.$i])))){
				continue;
			   }
			   $ad_count++;
			   switch ($i) {
						case 1:
								$position   =   'amp_below_the_header';
								break;
						case 2:
								$position   =   'amp_below_the_footer';
								break;
						case 3:
								$position   =   'amp_above_the_post_content';
								break;
						case 4:
								$position   =   'amp_below_the_post_content';
								break;
						case 5:
								$position   =   'amp_below_the_title';
								break;
						case 6:
								$position   =   'amp_above_related_post';
								break;
					}
				switch ($amp_options['enable-amp-ads-select-'.$i]) {
					case '1':
						$g_data_ad_width    = '300';
						$g_data_ad_height   = '250';
						break;
					case '2':
						$g_data_ad_width    = '336';
						$g_data_ad_height   = '280';
						break;
					case '3':
						$g_data_ad_width    = '728';
						$g_data_ad_height   = '90';
						break;
					case '4':
						$g_data_ad_width    = '300';
						$g_data_ad_height   = '600';
						break;
					case '5':
						$g_data_ad_width    = '320';
						$g_data_ad_height   = '100';
						break;
					case '6':
						$g_data_ad_width    = '200';
						$g_data_ad_height   = '50';
						break;
					case '7':
						$g_data_ad_width    = '320';
						$g_data_ad_height   = '50';
						break;
					default:
						$g_data_ad_width = '300';
						$g_data_ad_height= '250';
						break;
				}
				if($ad_type== 'mgid'){
					if($i == 2){
						$position   =   'ad_shortcode';
					}
					$post_title ='MGID Ad '.$i.' (Migrated from AMP)';
					$g_data_ad_width = $amp_options['enable-amp-ads-mgid-width-'.$i];
					$g_data_ad_height= $amp_options['enable-amp-ads-mgid-height-'.$i];
				}else{
					$post_title ='Adsense Ad '.$i.' (Migrated from AMP)';
				}
				$ads_post = array(
							'post_author' => $user_id,
							'post_title'  => $post_title,
							'post_status' => $post_status,
							'post_name'   => $post_title,
							'post_type'   => 'quads-ads',

						);
				if($amp_options['enable-amp-ads-resp-'.$i]){
					$adsense_type = 'responsive';
				}else{
					 $adsense_type = 'normal';
				}
				$post_id          = wp_insert_post($ads_post);
				$visibility_include =array();
				if($i == 3){
				 $display_on =  $amp_options['made-amp-ad-3-global'];
				 $j =0;
				 foreach ($display_on as $display_on_data) {
					switch ($display_on_data) {
						case '1':
							$visibility_include[$j]['type']['label'] = 'Post Type';
							$visibility_include[$j]['type']['value'] = 'post_type';
							$visibility_include[$j]['value']['label'] = "post";
							$visibility_include[$j]['value']['value'] = "post";
							$j++;
							break;
						case '2':
							$visibility_include[$j]['type']['label'] = 'Post Type';
							$visibility_include[$j]['type']['value'] = 'post_type';
							$visibility_include[$j]['value']['label'] = "page";
							$visibility_include[$j]['value']['value'] = "page";
							$j++;
							break;
						case '4':
							$visibility_include[$j]['type']['label'] = 'General';
							$visibility_include[$j]['type']['value'] = 'general';
							$visibility_include[$j]['value']['label'] = "Show Globally";
							$visibility_include[$j]['value']['value'] = "show_globally";
							$j++;
							break;
					}
				 }
				}else{
						$visibility_include[0]['type']['label'] = 'General';
						$visibility_include[0]['type']['value'] = 'general';
						$visibility_include[0]['value']['label'] = "Show Globally";
						$visibility_include[0]['value']['value'] = "show_globally";
				}

				$adforwp_meta_key = array(
					'ad_type'                       => $ad_type ,
					'g_data_ad_client'              => $amp_options['enable-amp-ads-text-feild-client-'.$i],
					'g_data_ad_slot'                => $amp_options['enable-amp-ads-text-feild-slot-'.$i],
					'data_publisher'                => $amp_options['enable-amp-ads-mgid-field-data-pub-'.$i],
					'data_widget'                   => $amp_options['enable-amp-ads-mgid-field-data-widget-'.$i],
					'data_container'                => $amp_options['enable-amp-ads-mgid-field-data-con-'.$i],
					'g_data_ad_width'               => $g_data_ad_width,
					'g_data_ad_height'              => $g_data_ad_height,
					'adsense_type'                  => $adsense_type,
					'enabled_on_amp'                => 1,
					'visibility_include'            => $visibility_include,
					'position'                      => $position,
					'imported_from'                 => 'ampforwp_ads',
					'label'                         =>  $post_title,
					'ad_id'                         => $post_id,
					'code'                          => '',
					'enable_one_end_of_post'        =>'',
					'quads_ad_old_id'               => 'ad'.$ad_count,
					'ad_label_check'                => $amp_options['ampforwp-ads-sponsorship'],
					'ad_label_text'                 => $amp_options['ampforwp-ads-sponsorship-label'],
				);

				foreach ($adforwp_meta_key as $key => $val){
					update_post_meta($post_id, $key, $val);
				}
			}
			if ( defined( 'ADVANCED_AMP_ADS_VERSION' ) ) {
				// Incontent Ads
				for($i=1; $i<=6; $i++){
					if($redux_builder_amp['ampforwp-incontent-ad-'.$i] != 1){
						continue;
				   }
				   $ad_type    =  $redux_builder_amp['ampforwp-advertisement-type-incontent-ad-'.$i];
				   $ad_type_label   = '';
				   if($ad_type== '4'){
					continue;
				   }
				   if(($ad_type== '1' && (empty($redux_builder_amp['ampforwp-adsense-ad-data-ad-client-incontent-ad-'.$i]) || empty($redux_builder_amp['ampforwp-adsense-ad-data-ad-slot-incontent-ad-'.$i]))) || ($ad_type== '5' && (empty($redux_builder_amp['ampforwp-mgid-ad-Data-Publisher-incontent-ad-'.$i]) || empty($redux_builder_amp['ampforwp-mgid-ad-Data-Widget-incontent-ad-'.$i])))){
					continue;
				   }
					$ad_count++;
					$g_data_ad_width = '';
					$g_data_ad_height= '';
					if($ad_type == '1'){
						$ad_type_label      = 'adsense';
						$post_title         = 'Adsense Ad '.$i.' Incontent Ad (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-adsense-ad-width-incontent-ad-'.$i];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-adsense-ad-height-incontent-ad-'.$i];
						$position = $redux_builder_amp['ampforwp-adsense-ad-position-incontent-ad-'.$i];
					}else if($ad_type == '2'){
						$ad_type_label      = 'double_click';
						$post_title         = 'DoubleClick Ad '.$i.' Incontent Ad (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-doubleclick-ad-width-incontent-ad-'.$i];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-doubleclick-ad-height-incontent-ad-'.$i];
						$position = $redux_builder_amp['ampforwp-doubleclick-ad-position-incontent-ad-'.$i];
					}else if($ad_type == '3'){
						$ad_type_label      = 'plain_text';
						$post_title         = 'Plain Text Ad '.$i.' Incontent Ad (Migrated from AMP)';
						$position = $redux_builder_amp['ampforwp-custom-ads-ad-position-incontent-ad-'.$i];
					}else if($ad_type == '5'){
						$ad_type_label      = 'mgid';
						$post_title         ='MGID Ad '.$i.' Incontent Ad (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-mgid-ad-width-incontent-ad-'.$i];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-mgid-ad-height-incontent-ad-'.$i];
						$position = $redux_builder_amp['ampforwp-mgid-ad-position-incontent-ad-'.$i];
					}
					if($redux_builder_amp['adsense-rspv-ad-incontent-'.$i]){
						$adsense_type = 'responsive';
					}else{
						 $adsense_type = 'normal';
					}
					$ads_post = array(
								'post_author' => $user_id,
								'post_title'  => $post_title,
								'post_status' => $post_status,
								'post_name'   => $post_title,
								'post_type'   => 'quads-ads',
							);
					$post_id          = wp_insert_post($ads_post);
					$visibility_include =array();

					$visibility_include[0]['type']['label'] = 'Post Type';
					$visibility_include[0]['type']['value'] = 'post_type';
					$visibility_include[0]['value']['label'] = "post";
					$visibility_include[0]['value']['value'] = "post";
					$doubleclick_ad_data_slot = explode('/', $redux_builder_amp['ampforwp-doubleclick-ad-data-slot-incontent-ad-'.$i]);
					$adlabel =  'above';
					if($redux_builder_amp['ampforwp-ad-sponsorship-location'] == '2'){
						$adlabel =  'below';
					}
					$paragraph_number = '1';

							  switch ($position) {
							case '20-percent':
									$position                     =   'after_the_percentage';
									$after_the_percentage_value   =   '20';
									break;
							case '40-percent':
									$position                     =   'after_the_percentage';
									$after_the_percentage_value   =   '40';
									break;
							case '50-percent':
									$position                     =   'after_the_percentage';
									$after_the_percentage_value   =   '50';
									break;
						   case '60-percent':
									$position                     =   'after_the_percentage';
									$after_the_percentage_value   =   '60';
									break;
							case '80-percent':
									$position                     =   'after_the_percentage';
									$after_the_percentage_value   =   '80';
									break;
							case 'custom':
									$position   =   'code';
									break;
							default:
									if(is_numeric($position)){
										$paragraph_number = $position;
										$position = 'after_paragraph';
									}
							break;
						}
						$network_code = '';
						$doubleclick_flag = 2;
						if(isset($doubleclick_ad_data_slot[0]) && !empty($doubleclick_ad_data_slot[0])){
							   $doubleclick_flag = 3;
							$network_code = $doubleclick_ad_data_slot[0];
						}
						if(isset($doubleclick_ad_data_slot[1]) && !empty($doubleclick_ad_data_slot[1])){
							if($doubleclick_flag == 3){
								$ad_unit_name = $doubleclick_ad_data_slot[1];
							}else{
								$network_code = $doubleclick_ad_data_slot[1];
								if(isset($doubleclick_ad_data_slot[2]) && !empty($doubleclick_ad_data_slot[2])){
									$ad_unit_name = $doubleclick_ad_data_slot[2];
								}
							}
						}

					$adforwp_meta_key = array(
						'ad_type'                       => $ad_type_label ,
						'g_data_ad_client'              => $redux_builder_amp['ampforwp-adsense-ad-data-ad-client-incontent-ad-'.$i],
						'g_data_ad_slot'                => $redux_builder_amp['ampforwp-adsense-ad-data-ad-slot-incontent-ad-'.$i],
						'data_publisher'                => $redux_builder_amp['ampforwp-mgid-ad-Data-Publisher-incontent-ad-'.$i],
						'data_widget'                   => $redux_builder_amp['ampforwp-mgid-ad-Data-Widget-incontent-ad-'.$i],
						'data_container'                => $redux_builder_amp['ampforwp-mgid-ad-Data-Container-incontent-ad-'.$i],
						'network_code'                  => $network_code,
						'ad_unit_name'                  => $ad_unit_name,
						'code'                          => $redux_builder_amp['ampforwp-custom-advertisement-incontent-ad-'.$i],
						'g_data_ad_width'               => $g_data_ad_width,
						'g_data_ad_height'              => $g_data_ad_height,
						'adsense_type'                  => $adsense_type,
						'enabled_on_amp'                => 1,
						'visibility_include'            => $visibility_include,
						'position'                      => $position,
						'after_the_percentage_value'    => $after_the_percentage_value,
						'paragraph_number'              => $paragraph_number,
						'imported_from'                 => 'ampforwp_ads',
						'label'                         =>  $post_title,
						'ad_id'                         => $post_id,
						'enable_one_end_of_post'        =>'',
						'quads_ad_old_id'               => 'ad'.$ad_count,
						'ad_label_check'                => $redux_builder_amp['ampforwp-ad-sponsorship'],
						'adlabel'                       => $adlabel,
						'ad_label_text'                 => $redux_builder_amp['ampforwp-ad-sponsorship-label'],
					);

					foreach ($adforwp_meta_key as $key => $val){
						update_post_meta($post_id, $key, $val);
					}

						require_once QUADS_PLUGIN_DIR . '/admin/includes/migration-service.php';
						$this->migration_service = new QUADS_Ad_Migration();
						$this->migration_service->quadsUpdateOldAd('ad'.$ad_count, $adforwp_meta_key);

				}
				// General Ads
				for($i=1; $i<=10; $i++){
				   if($amp_options['ampforwp-standard-ads-'.$i] != 1){
						continue;
				   }
				   $ad_type    =  $amp_options['ampforwp-advertisement-type-standard-'.$i];
					if(($ad_type== '1' && (empty($redux_builder_amp['ampforwp-adsense-ad-data-ad-client-standard-'.$i]) || empty($redux_builder_amp['ampforwp-adsense-ad-data-ad-slot-standard-'.$i])))|| ($ad_type== '2' && empty($redux_builder_amp['ampforwp-doubleclick-ad-data-slot-standard-'.$i])) || ($ad_type== '5' && (empty($redux_builder_amp['ampforwp-mgid-data-ad-data-publisher-standard-'.$i]) || empty($redux_builder_amp['ampforwp-mgid-data-ad-data-widget-standard-'.$i])))){
					continue;
				   }
					$ad_count++;
				   switch ($i) {
							case 1:
									$position   =   'amp_below_the_header';
									break;
							case 2:
									$position   =   'amp_below_the_footer';
									break;
							case 3:
									$position   =   'amp_above_the_footer';
									break;
							case 4:
									$position   =   'amp_above_the_post_content';
									break;
							case 5:
									$position   =   'amp_below_the_post_content';
									break;
							case 6:
									$position   =   'amp_below_the_title';
									break;
							case 7:
									$position   =   'amp_above_related_post';
									break;
							case 8:
									$position   =   'amp_below_author_box';
									break;
							case 9:
									$position   =   'amp_ads_in_loops';
									break;
							case 10:
									$position   =   'amp_doubleclick_sticky_ad';
									break;
						}

									$g_data_ad_width = '';
					$g_data_ad_height= '';
					 $adsense_type = 'normal';
					if($ad_type == '1'){
						$ad_type_label      = 'adsense';
						$post_title         = 'Adsense Ad '.$i.' General Options (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-adsense-ad-width-standard-'.$i];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-adsense-ad-height-standard-'.$i];
						if($amp_options['adsense-rspv-ad-type-standard-'.$i]){
							$adsense_type = 'responsive';
						}else{
							 $adsense_type = 'normal';
						}
					}else if($ad_type == '2'){
						$ad_type_label      = 'double_click';
						$post_title         = 'DoubleClick Ad '.$i.' General Options (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-doubleclick-ad-width-standard-'.$i];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-doubleclick-ad-height-standard-'.$i];
						$adsense_type = 'normal';
					}else if($ad_type == '3'){
						$ad_type_label      = 'plain_text';
						$post_title         = 'Ad '.$i.' General Options (Migrated from AMP)';
					}else if($ad_type == '5'){
						$ad_type_label      = 'mgid';
						$post_title         ='MGID Ad '.$i.' General Options (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-mgid-ad-width-standard-'.$i];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-mgid-ad-height-standard-'.$i];
						$adsense_type = 'normal';
					}
					$ads_post = array(
								'post_author' => $user_id,
								'post_title'  => $post_title,
								'post_status' => $post_status,
								'post_name'   => $post_title,
								'post_type'   => 'quads-ads',

							);
					$post_id          = wp_insert_post($ads_post);
					$visibility_include =array();
					$visibility_include[0]['type']['label'] = 'Post Type';
					$visibility_include[0]['type']['value'] = 'post_type';
					$visibility_include[0]['value']['label'] = "post";
					$visibility_include[0]['value']['value'] = "post";

						$network_code = '';
						$ad_unit_name = '';
						$doubleclick_flag = 2;
						$doubleclick_ad_data_slot = explode('/', $redux_builder_amp['ampforwp-doubleclick-ad-data-slot-standard-'.$i]);
						if(isset($doubleclick_ad_data_slot[0]) && !empty($doubleclick_ad_data_slot[0])){
							   $doubleclick_flag = 3;
							$network_code = $doubleclick_ad_data_slot[0];
						}
						if(isset($doubleclick_ad_data_slot[1]) && !empty($doubleclick_ad_data_slot[1])){
							if($doubleclick_flag == 3){
								$ad_unit_name = $doubleclick_ad_data_slot[1];
							}else{
								$network_code = $doubleclick_ad_data_slot[1];
								if(isset($doubleclick_ad_data_slot[2]) && !empty($doubleclick_ad_data_slot[2])){
									$ad_unit_name = $doubleclick_ad_data_slot[2];
								}
							}
						}

					$adforwp_meta_key = array(
						'ad_type'                       => $ad_type_label ,
						'g_data_ad_client'              => $redux_builder_amp['ampforwp-adsense-ad-data-ad-client-standard-'.$i],
						'g_data_ad_slot'                => $redux_builder_amp['ampforwp-adsense-ad-data-ad-slot-standard-'.$i],
						'data_publisher'                => $redux_builder_amp['ampforwp-mgid-ad-Data-Publisher-standard-'.$i],
						'data_widget'                   => $redux_builder_amp['ampforwp-mgid-ad-Data-Widget-standard-'.$i],
						'data_container'                => $redux_builder_amp['ampforwp-mgid-ad-Data-Container-standard-'.$i],
						'network_code'                  => $network_code,
						'ad_unit_name'                  => $ad_unit_name,
						'code'                          => $redux_builder_amp['ampforwp-custom-advertisement-standard-'.$i],
						'g_data_ad_width'               => $g_data_ad_width,
						'g_data_ad_height'              => $g_data_ad_height,
						'adsense_type'                  => $adsense_type,
						'enabled_on_amp'                => 1,
						'visibility_include'            => $visibility_include,
						'position'                      => $position,
						'imported_from'                 => 'ampforwp_ads',
						'label'                         =>  $post_title,
						'ad_id'                         => $post_id,
						'enable_one_end_of_post'        => '',
						'quads_ad_old_id'               => 'ad'.$ad_count,
						'ad_label_check'                => $redux_builder_amp['ampforwp-ad-sponsorship'],
						'adlabel'                       => $adlabel,
						'ad_label_text'                 => $redux_builder_amp['ampforwp-ad-sponsorship-label'],
					);

					foreach ($adforwp_meta_key as $key => $val){
						update_post_meta($post_id, $key, $val);
					}
					require_once QUADS_PLUGIN_DIR . '/admin/includes/migration-service.php';
						$this->migration_service = new QUADS_Ad_Migration();
						$this->migration_service->quadsUpdateOldAd('ad'.$ad_count, $adforwp_meta_key);
				}

				if($amp_options['ampforwp-after-featured-image-ad']){
					$ad_count++;
					$ad_type    =  $amp_options['ampforwp-after-featured-image-ad-type'];
					$g_data_ad_width        = '';
					$g_data_ad_height       = '';
					$adsense_type = 'normal';
					if($ad_type == '1'){
						$ad_type_label      = 'adsense';
						$post_title         = 'Adsense Ad '.$ad_count.' (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-after-featured-image-ad-type-1-width'];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-after-featured-image-ad-type-1-height'];
						if($redux_builder_amp['adsense-rspv-ad-after-featured-img']){
							$adsense_type = 'responsive';
						}else{
							 $adsense_type = 'normal';
						}
					}else if($ad_type == '2'){
						$ad_type_label      = 'double_click';
						$post_title         = 'DoubleClick Ad '.$ad_count.' (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-after-featured-image-ad-type-2-width'];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-after-featured-image-ad-type-2-height'];
					}else if($ad_type == '3'){
						$ad_type_label      = 'plain_text';
						$post_title         = 'Adsense Ad '.$ad_count.' (Migrated from AMP)';
					}else if($ad_type == '5'){
						$ad_type_label      = 'mgid';
						$post_title         = 'MGID Ad '.$ad_count.' (Migrated from AMP)';
						$g_data_ad_width    = $redux_builder_amp['ampforwp-after-featured-image-ad-type-5-width'];
						$g_data_ad_height   = $redux_builder_amp['ampforwp-after-featured-image-ad-type-5-height'];
					}
					$network_code = '';
						$ad_unit_name = '';
						$doubleclick_flag = 2;
						$doubleclick_ad_data_slot = explode('/', $redux_builder_amp['ampforwp-after-featured-image-ad-type-2-ad-data-slot']);
						if(isset($doubleclick_ad_data_slot[0]) && !empty($doubleclick_ad_data_slot[0])){
							   $doubleclick_flag = 3;
							$network_code = $doubleclick_ad_data_slot[0];
						}
						if(isset($doubleclick_ad_data_slot[1]) && !empty($doubleclick_ad_data_slot[1])){
							if($doubleclick_flag == 3){
								$ad_unit_name = $doubleclick_ad_data_slot[1];
							}else{
								$network_code = $doubleclick_ad_data_slot[1];
								if(isset($doubleclick_ad_data_slot[2]) && !empty($doubleclick_ad_data_slot[2])){
									$ad_unit_name = $doubleclick_ad_data_slot[2];
								}
							}
						}

						$visibility_include =array();
						$visibility_include[0]['type']['label'] = 'Post Type';
						$visibility_include[0]['type']['value'] = 'post_type';
						$visibility_include[0]['value']['label'] = "post";
						$visibility_include[0]['value']['value'] = "post";
						$ads_post = array(
								'post_author' => $user_id,
								'post_title'  => $post_title,
								'post_status' => $post_status,
								'post_name'   => $post_title,
								'post_type'   => 'quads-ads',

							);
						$post_id          = wp_insert_post($ads_post);

					 $adforwp_meta_key = array(
						'ad_type'                       => $ad_type_label ,
						'g_data_ad_client'              => $redux_builder_amp['ampforwp-after-featured-image-ad-type-1-data-ad-client'],
						'g_data_ad_slot'                => $redux_builder_amp['ampforwp-after-featured-image-ad-type-1-data-ad-slot'],
						'data_publisher'                => $redux_builder_amp['ampforwp-after-featured-image-ad-type-5-Data-publisher'],
						'data_widget'                   => $redux_builder_amp['ampforwp-after-featured-image-ad-type-5-Data-widget'],
						'data_container'                => $redux_builder_amp['ampforwp-after-featured-image-ad-type-5-Data-Container'],
						'network_code'                  => $network_code,
						'ad_unit_name'                  => $ad_unit_name,
						'code'                          => $redux_builder_amp['ampforwp-after-featured-image-ad-custom-advertisement'],
						'g_data_ad_width'               => $g_data_ad_width,
						'g_data_ad_height'              => $g_data_ad_height,
						'adsense_type'                  => $adsense_type,
						'enabled_on_amp'                => 1,
						'visibility_include'            => $visibility_include,
						'position'                      => 'amp_after_featured_image',
						'imported_from'                 => 'ampforwp_ads',
						'label'                         =>  $post_title,
						'ad_id'                         => $post_id,
						'enable_one_end_of_post'        => '',
						'quads_ad_old_id'               => 'ad'.$ad_count,
						'ad_label_check'                => $redux_builder_amp['ampforwp-ad-sponsorship'],
						'adlabel'                       => $adlabel,
						'ad_label_text'                 => $redux_builder_amp['ampforwp-ad-sponsorship-label'],
					);

					foreach ($adforwp_meta_key as $key => $val){
						update_post_meta($post_id, $key, $val);
					}
					require_once QUADS_PLUGIN_DIR . '/admin/includes/migration-service.php';
						$this->migration_service = new QUADS_Ad_Migration();
						$this->migration_service->quadsUpdateOldAd('ad'.$ad_count, $adforwp_meta_key);
				}
			}
			return  array('status' => 't', 'data' => 'Ads have been successfully imported');

		}
}
