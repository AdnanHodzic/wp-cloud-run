<?php // phpcs:ignoreFile
/**
 * Quick Adsense.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Interfaces\Importer as Interface_Importer;

defined( 'ABSPATH' ) || exit;

/**
 * Quick Adsense.
 */
class Quick_Adsense extends Importer implements Interface_Importer {

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string {
		return 'quick_adsense';
	}

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string {
		return __( 'Quick Adsense', 'advanced-ads' );
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

	public function adsforwp_import_all_quick_adsense_ads(){
		global $wpdb;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$nonce = Params::get( 'adsforwp_security_nonce', '' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'adsforwp_ajax_check_nonce' ) ){
				return;
		}

		$wpdb->query('START TRANSACTION');
		$result = array();
		$user_id = get_current_user_id();
		global $quickAdsenseAdsDisplayed;
		global $ampfowpAdsenseAdsId;
		global $quickAdsenseBeginEnd;
		$ampfowpAdsenseAdsId = array();
		$settings = get_option('quick_adsense_settings');

		for($i = 1; $i <= 10; $i++) {
			if(isset($settings['onpost_ad_'.$i.'_content']) && !empty($settings['onpost_ad_'.$i.'_content'])) {
			$ads_post = array(
						'post_author' => $user_id,
						'post_title'  => 'Custom Ad '.$i.' (Migrated from Quick Adsense)',
						'post_status' => 'publish',
						'post_name'   => 'Custom Ad '.$i.' (Migrated from Quick Adsense)',
						'post_type'   => 'adsforwp',

						);
			$post_id = wp_insert_post($ads_post);
			$ads_content = $settings['onpost_ad_'.$i.'_content'];
			$ads_alignment = $settings['onpost_ad_'.$i.'_alignment'];
			$ads_margin = $settings['onpost_ad_'.$i.'_margin'];

				$wheretodisplay = '';
				$ad_align       = '';
				$pragraph_no    = '';
				$adposition     = '';


				if($ads_alignment == 1){
				$ad_align ='left';
				if(!empty($ads_margin)){
					$ads_align_margin = array('ad_margin_top' => $ads_margin,'ad_margin_bottom' => $ads_margin,'ad_margin_left' => 0,'ad_margin_right' => $ads_margin);
				}
				}elseif($ads_alignment == 2){
				$ad_align ='center';
				if(!empty($ads_margin)){
					$ads_align_margin = array('ad_margin_top' => $ads_margin,'ad_margin_bottom' => $ads_margin,'ad_margin_left' => 0,'ad_margin_right' => 0);
				}
				}elseif($ads_alignment == 3){
				$ad_align ='right';
				if(!empty($ads_margin)){
					$ads_align_margin = array('ad_margin_top' => $ads_margin,'ad_margin_bottom' => $ads_margin,'ad_margin_left' => $ads_margin,'ad_margin_right' => 0);
				}
				}elseif($ads_alignment == 4){
				$ad_align = 'none';
				if(!empty($ads_margin)){
					$ads_align_margin = array('ad_margin_top' => 0,'ad_margin_bottom' => 0,'ad_margin_left' => 0,'ad_margin_right' => 0);
				}
				}
				if( isset($settings['enable_on_posts']) && $settings['enable_on_posts'] == 1){
				$data_group_array['group-0'] = array(
										'data_array' => array(
												array(
													'key_1' => 'post_type',
													'key_2' => 'equal',
													'key_3' => 'post',
												)
											)
										);
				}
				if( isset($settings['enable_on_pages']) && $settings['enable_on_pages'] == 1){
				$data_group_array['group-1'] = array(
										'data_array' => array(
												array(
													'key_1' => 'post_type',
													'key_2' => 'equal',
													'key_3' => 'page',
												)
											)
										);
				}

				//enable_position_before_last_para, ad_before_last_para


				if($settings['ad_beginning_of_post'] == $i){
				if($settings['enable_position_beginning_of_post'] == 1){
					$wheretodisplay = 'before_the_content';
				}
				}elseif($settings['ad_end_of_post'] == $i){
				if( $settings['enable_position_end_of_post'] == 1){
					$wheretodisplay = 'after_the_content';
				}
				}elseif($settings['ad_middle_of_post'] == $i){
				if($settings['enable_position_middle_of_post'] == 1 ){
					$wheretodisplay = 'between_the_content';
				}
				}

				for($j = 1; $j <= 3; $j++) {
				if($settings['ad_after_para_option_'.$j] == $i){
					if($settings['enable_position_after_para_option_'.$j] == 1){
					$wheretodisplay = 'between_the_content';
					$numberofparas = 'number_of_paragraph';
					$display_tag_name = 'p_tag';
					$paragraph_number = $settings['position_after_para_option_'.$j];
					if($settings['enable_jump_position_after_para_option_'.$j] == 1){
						$ads_on_every_paras = 1;
					}
					}elseif($settings['enable_position_after_image_option_'.$j] == 1){
					$wheretodisplay = 'between_the_content';
					$numberofparas = 'number_of_paragraph';
					$display_tag_name = 'img_tag';
					$paragraph_number = $settings['position_after_para_option_'.$j];
					if($settings['enable_jump_position_after_para_option_'.$j] == 1){
						$ads_on_every_paras = 1;
					}
					}
				}
				}

				//enable_on_posts
				//enable_on_pages
				//enable_on_homepage
				$adforwp_meta_key = array(
					'select_adtype'     => 'custom',
					'custom_code'       => $ads_content,
					'adposition'        => $adposition,
					'paragraph_number'  => $pragraph_no,
					'adsforwp_ad_align' => $ad_align,
					'adsforwp_ad_margin'=> $ads_align_margin,
					'imported_from'     => 'quick_adsense',
					'wheretodisplay'    => $wheretodisplay,
					'display_tag_name'  => $display_tag_name,
					'adposition'        => $numberofparas,
					'paragraph_number'  => $paragraph_number,
					'ads_on_every_paragraphs_number' => $ads_on_every_paras,
					'data_group_array'  => $data_group_array
				);

				foreach ($adforwp_meta_key as $key => $val){
				$result[] =  update_post_meta($post_id, $key, $val);
				}
			}
			}
			for($i = 1; $i <= 10; $i++) {
			if(isset($settings['widget_ad_'.$i.'_content']) && !empty($settings['widget_ad_'.$i.'_content'])) {
			$ads_post = array(
						'post_author' => $user_id,
						'post_title'  => 'Custom widget Ad '.$i.' (Migrated from Quick Adsense)',
						'post_status' => 'publish',
						'post_name'   => 'Custom widget Ad '.$i.' (Migrated from Quick Adsense)',
						'post_type'   => 'adsforwp',

						);
			$post_id = wp_insert_post($ads_post);
			$ads_content = $settings['widget_ad_'.$i.'_content'];
			$ads_alignment = $settings['onpost_ad_'.$i.'_alignment'];
			$ads_margin = $settings['onpost_ad_'.$i.'_margin'];

				$wheretodisplay = '';
				$ad_align       = '';
				$pragraph_no    = '';
				$adposition     = '';


				if($ads_alignment == 1){
				$ad_align ='left';
				if(!empty($ads_margin)){
					$ads_align_margin = array('ad_margin_top' => $ads_margin,'ad_margin_bottom' => $ads_margin,'ad_margin_left' => 0,'ad_margin_right' => $ads_margin);
				}
				}elseif($ads_alignment == 2){
				$ad_align ='center';
				if(!empty($ads_margin)){
					$ads_align_margin = array('ad_margin_top' => $ads_margin,'ad_margin_bottom' => $ads_margin,'ad_margin_left' => 0,'ad_margin_right' => 0);
				}
				}elseif($ads_alignment == 3){
				$ad_align ='right';
				if(!empty($ads_margin)){
					$ads_align_margin = array('ad_margin_top' => $ads_margin,'ad_margin_bottom' => $ads_margin,'ad_margin_left' => $ads_margin,'ad_margin_right' => 0);
				}
				}elseif($ads_alignment == 4){
				$ad_align = 'none';
				if(!empty($ads_margin)){
					$ads_align_margin = array('ad_margin_top' => 0,'ad_margin_bottom' => 0,'ad_margin_left' => 0,'ad_margin_right' => 0);
				}
				}

				$data_group_array['group-0'] = array(
										'data_array' => array(
												array(
													'key_1' => 'show_globally',
													'key_2' => 'equal',
													'key_3' => 'post',
												)
											)
										);

				$adforwp_meta_key = array(
				'select_adtype'     => 'custom',
				'custom_code'       => $ad_code,
				'adposition'        => $adposition,
				'paragraph_number'  => $pragraph_no,
				'adsforwp_ad_align' => $ad_align,
				'imported_from'     => 'quick_adsense',
				'wheretodisplay'    => $wheretodisplay,
				'data_group_array'  => $data_group_array
				);

				foreach ($adforwp_meta_key as $key => $val){
				$result[] =  update_post_meta($post_id, $key, $val);
				}
			}
		}
		//die;
		if (is_wp_error($result) ){
			echo $result->get_error_message();
			$wpdb->query('ROLLBACK');
		}else{
		$wpdb->query('COMMIT');
		return $result;
		}
	}
}
