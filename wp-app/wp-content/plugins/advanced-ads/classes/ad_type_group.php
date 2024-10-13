<?php

use AdvancedAds\Entities;

/**
 * Advanced Ads Plain Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2014-2016 Thomas Maier, Advanced Ads GmbH
 *
 * Class containing information about the plain text/code ad type
 *
 * see ad-type-content.php for a better sample on ad type
 *
 * @since 1.7.1.1
 *
 */
class Advanced_Ads_Ad_Type_Group extends Advanced_Ads_Ad_Type_Abstract{

	/**
	 * ID - internal type of the ad type
	 *
	 */
	public $ID = 'group';

	/**
	 * Set basic attributes
	 */
	public function __construct() {
		$this->title = __( 'Ad Group', 'advanced-ads' );
		$this->description = __( 'Choose an existing ad group. Use this type when you want to assign the same display and visitor conditions to all ads in that group.', 'advanced-ads' );
		$this->parameters = [
		    'group_id' => 0
		];

		// on save, remove the group in which the ad is itself to prevent infinite loops
		add_action( 'save_post_advanced_ads', [$this, 'remove_from_ad_group'], 1 );
	}

	/**
	 * When saving the ad, remove it from the ad group, if this is the group assigned as ad content
	 * see also: /admin/includes/class-ad-groups-list.php::update_groups()
	 *
	 * @param integer $post_id ID of the post.
	 */
	public function remove_from_ad_group( $post_id ){

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce is verified before calling the hook
	    if( ! isset( $_POST['post_type'] ) || $_POST['post_type'] !== Entities::POST_TYPE_AD ){
		return;
	    }

	    if( isset( $_POST[ 'advanced_ad' ]['output']['group_id'] ) ){
			$group_id = (int) $_POST['advanced_ad']['output']['group_id'];
		if( isset( $_POST['tax_input']['advanced_ads_groups'] ) ){
		    if(( $key = array_search( $group_id, $_POST['tax_input']['advanced_ads_groups'])) !== false ) {
			$res = wp_remove_object_terms( $post_id, $group_id, Entities::TAXONOMY_AD_GROUP );
			unset( $_POST['tax_input']['advanced_ads_groups'][$key] );
		    }
		}
	    }
	    // phpcs:enable
	}


	/**
	 * Output for the ad parameters metabox
	 *
	 * this will be loaded using ajax when changing the ad type radio buttons
	 * echo the output right away here
	 * name parameters must be in the "advanced_ads" array
	 *
	 * @param obj $ad ad object
	 */
	public function render_parameters($ad){

		$group_id = ( isset( $ad->output['group_id'] ) ) ? $ad->output['group_id'] : '';

		$select = [];
		$model = Advanced_Ads::get_instance()->get_model();

		// load all ad groups
		$groups = $model->get_ad_groups();

		if( ! is_array( $groups ) || ! count( $groups )  ){
		    return;
		}

		?><label for="advads-group-id" class="label"><?php _e('ad group', 'advanced-ads'); ?></label><div><select name="advanced_ad[output][group_id]" id="advads-group-id"><?php

		foreach ( $groups as $_group ) {
		    ?><option value="<?php echo $_group->term_id; ?>" <?php selected( $_group->term_id, $group_id ); ?>><?php echo $_group->name; ?></option><?php
		}

		?></select></div><hr/><?php

	}

	/**
	 * Prepare the ads frontend output
	 *
	 * @param obj $ad ad object
	 * @return str $content ad content prepared for frontend output
	 */
	public function prepare_output($ad){
		$group_id = ( isset( $ad->output['group_id'] ) ) ? absint( $ad->output['group_id'] ) : 0;

		if( $group_id ){
		    return get_ad_group( $group_id, $ad->args );
		}
	}

}
