<?php

/**
 * Conditions under which to (not) show an ad
 * I don’t like huge arrays like this to clutter my classes
 *  and anyway, this might be needed on multiple places
 *
 * at the bottom, you find a filter to be able to extend / remove your own elements
 *
 * elements
 * key - internal id of the condition; needs to be unique, obviously
 * label - title in the dashboard
 * description - (optional) description displayed in the dashboard
 * type - information / markup type
 *      idfield - input field for comma separated lists of ids
 *      radio - radio button
 *      others - added to not trigger internal sanitization
 *
 * note: ’idfield’ always has a {field}_not version that is created automatically and being its own condition
 *
 * @deprecated since 1.7
 */

// avoid direct execution
if ( ! class_exists( 'Advanced_Ads', false ) ) {
	die();
}

$advanced_ads_slug = Advanced_Ads::get_instance()->get_plugin_slug();

$advanced_ads_ad_conditions = [
	'enabled' => [ // dummy, to let the validation of the general ad conditions past
		// 'label' => __('Post Types', 'advanced-ads'),
		// 'description' => __('Choose the public post types on which to display the ad.', 'advanced-ads'),
		'type' => 'other',
		// 'callback' => array('Advanced_Ads_Display_Condition_Callbacks', 'post_types')
	],
	'posttypes' => [
		'label' => __( 'Post Types', 'advanced-ads' ),
		'description' => __( 'Choose the public post types on which to display the ad.', 'advanced-ads' ),
		'type' => 'textvalues',
		'callback' => ['Advanced_Ads_Display_Condition_Callbacks', 'post_types']
	],
	'categoryids' => [
		'label' => __( 'Categories, Tags and Taxonomies', 'advanced-ads' ),
		'description' => __( 'Choose terms from public category, tag and other taxonomies a post must belong to in order to have ads.', 'advanced-ads' ),
		'type' => 'idfield',
		'callback' => ['Advanced_Ads_Display_Condition_Callbacks', 'terms']
	],
	'categoryarchiveids' => [
		'label' => __( 'Category Archives', 'advanced-ads' ),
		'description' => __( 'comma seperated IDs of category archives', 'advanced-ads' ),
		'type' => 'idfield',
		'callback' => ['Advanced_Ads_Display_Condition_Callbacks', 'category_archives']
	],
	'postids' => [
		'label' => __( 'Individual Posts, Pages and Public Post Types', 'advanced-ads' ),
		'description' => __( 'Choose on which individual posts, pages and public post type pages you want to display or hide ads.', 'advanced-ads' ),
		'type' => 'other',
		'callback' => ['Advanced_Ads_Display_Condition_Callbacks', 'single_posts']
	],
	'is_front_page' => [
		'label' => __( 'Home Page', 'advanced-ads' ),
		'description' => __( 'show on Home page', 'advanced-ads' ),
		'type' => 'radio',
	],
	'is_singular' => [
		'label' => __( 'Singular Pages', 'advanced-ads' ),
		'description' => __( 'show on singular pages/posts', 'advanced-ads' ),
		'type' => 'radio',
	],
	'is_archive' => [
		'label' => __( 'Archive Pages', 'advanced-ads' ),
		'description' => __( 'show on any type of archive page (category, tag, author and date)', 'advanced-ads' ),
		'type' => 'radio',
	],
	'is_search' => [
		'label' => __( 'Search Results', 'advanced-ads' ),
		'description' => __( 'show on search result pages', 'advanced-ads' ),
		'type' => 'radio',
	],
	'is_404' => [
		'label' => __( '404 Page', 'advanced-ads' ),
		'description' => __( 'show on 404 error page', 'advanced-ads' ),
		'type' => 'radio',
	],
	'is_attachment' => [
		'label' => __( 'Attachment Pages', 'advanced-ads' ),
		'description' => __( 'show on attachment pages', 'advanced-ads' ),
		'type' => 'radio',
	],
	'is_main_query' => [
		'label' => __( 'Secondary Queries', 'advanced-ads' ),
		'description' => __( 'allow ads in secondary queries', 'advanced-ads' ),
		'type' => 'radio',
	]
];

return apply_filters( 'advanced-ads-conditions', $advanced_ads_ad_conditions );
