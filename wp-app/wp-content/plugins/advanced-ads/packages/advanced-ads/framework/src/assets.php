<?php
/**
 * Temporary CSS and JS will be move to main plugin
 *
 * @package AdvancedAds\Framework
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework;

// Early bail!!
if ( ! function_exists( 'add_action' ) ) {
	return;
}

/**
 * Register CSS
 *
 * @return void
 */
function advanced_ads_framework_css() {
	?>
	<style>
		.advads-placements-table .advads-option-placement-page-peel-position div.clear {
			content: ' ';
			display: block;
			float: none;
			clear: both;
		}
		.advads-field-position table tbody tr td {
			width: 3em !important;
			height: 2em;
			text-align: center;
			vertical-align: middle;
			padding: 0;
		}

		/* Switch */
		.advads-field-switch input[type=checkbox] {
			--active: #3e6cf4;
			--active-inner: #fff;
			--focus: 2px rgba(39, 94, 254, .3);
			--border: #BBC1E1;
			--border-hover: #3e6cf4;
			--background: #fff;
			--disabled: #F6F8FF;
			--disabled-inner: #E1E6F9;
			-webkit-appearance: none;
			-moz-appearance: none;
			height: 21px;
			outline: none;
			display: inline-block;
			vertical-align: top;
			position: relative;
			margin: 0;
			cursor: pointer;
			border: 1px solid var(--bc, var(--border));
			background: var(--b, var(--background));
			transition: background 0.3s, border-color 0.3s, box-shadow 0.2s;
		}
		.advads-field-switch input[type=checkbox]:after {
			content: "";
			display: block;
			left: 0;
			top: 0;
			position: absolute;
			transition: transform var(--d-t, 0.3s) var(--d-t-e, ease), opacity var(--d-o, 0.2s);
		}
		.advads-field-switch input[type=checkbox]:checked {
			--b: var(--active);
			--bc: var(--active);
			--d-o: .3s;
			--d-t: .6s;
			--d-t-e: cubic-bezier(.2, .85, .32, 1.2);
		}
		.advads-field-switch input[type=checkbox]:disabled {
			--b: var(--disabled);
			cursor: not-allowed;
			opacity: 0.9;
		}
		.advads-field-switch input[type=checkbox]:disabled:checked {
			--b: var(--disabled-inner);
			--bc: var(--border);
		}
		.advads-field-switch input[type=checkbox]:disabled + label {
			cursor: not-allowed;
		}
		.advads-field-switch input[type=checkbox]:hover:not(:checked):not(:disabled) {
			--bc: var(--border-hover);
		}
		.advads-field-switch input[type=checkbox]:focus {
			box-shadow: 0 0 0 var(--focus);
		}
		.advads-field-switch input[type=checkbox] + label {
			font-size: 14px;
			line-height: 21px;
			display: inline-block;
			vertical-align: top;
			cursor: pointer;
			margin-left: 4px;
		}
		.advads-field-switch input[type=checkbox].switch {
			width: 38px;
			border-radius: 11px;
		}
		.advads-field-switch input[type=checkbox].switch:after {
			left: 2px;
			top: 2px;
			border-radius: 50%;
			width: 15px;
			height: 15px;
			background: var(--ab, var(--border));
			transform: translateX(var(--x, 0));
		}
		.advads-field-switch input[type=checkbox].switch:checked {
			--ab: var(--active-inner);
			--x: 17px;
		}
		.advads-field-switch .switch:before {
			display: none !important;
		}
	</style>
	<?php
}
add_action( 'admin_head', __NAMESPACE__ . '\\advanced_ads_framework_css', 100, 0 );

/**
 * Register JS
 *
 * @return void
 */
function advanced_ads_framework_js() {
	?>
	<script>
		jQuery(document).ready(function($) {
			if (undefined !== jQuery.fn.wpColorPicker) {
				$('.advads-field-color .advads-field-input input').wpColorPicker({defaultColor: '#5d5d5d'});
			}
		});
	</script>
	<?php
}
add_action( 'admin_footer', __NAMESPACE__ . '\\advanced_ads_framework_js', 100, 0 );
