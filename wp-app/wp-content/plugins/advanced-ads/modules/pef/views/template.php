<?php
/**
 * Final output for the Product Experimentation
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var array  $winner the winner feature.
 * @var string $screen the screen where it's displayed.
 */

?>
<style>
	#support #advads_overview_pef {
		max-width: 998px;
	}
	#advads_overview_pef {
		border: 1px solid #0474a2;
		border-radius: 5px;
		margin: 22px 0;
		color: #1b193a;
	}

	#advads_overview_pef div.aa_overview_pef_upper {
		padding: 44px 44px 44px 208px;
		background: url(<?php echo esc_url( trailingslashit( plugin_dir_url( \AdvancedAds\Modules\ProductExperimentationFramework\FILE ) ) ) . '/assets/aa-pef-bg.svg'; ?>) top left / 40px auto repeat;
		border-radius: 5px 5px 0 0;
		color: inherit;
	}

	#advads_overview_pef p.aa_overview_pef_dismiss {
		position: absolute;
		top: 22px;
		right: 22px;
		margin: 0;
		text-align: right;
		color: inherit;
	}

	#advads_overview_pef p.aa_overview_pef_dismiss a {
		display: block;
		text-decoration: none;
		color: #1b193a;
	}

	#advads_overview_pef p.aa_overview_pef_subhead {
		margin: 0 0 11px;
		font-size: 18px;
		line-height: 18px;
		text-transform: uppercase;
		font-weight: bold;
		color: inherit;
	}

	#advads_overview_pef p.aa_overview_pef_subhead:before {
		content: "";
		position: absolute;
		top: 44px;
		left: 44px;
		width: 120px;
		height: 164px;
		background: url(<?php echo esc_url( trailingslashit( plugin_dir_url( \AdvancedAds\Modules\ProductExperimentationFramework\FILE ) ) ) . '/assets/aa-pef-deco.svg'; ?>) center/contain no-repeat;
	}

	#advads_overview_pef h3.aa_overview_pef_head {
		margin: 0 0 44px;
		font-size: 36px;
		line-height: 36px;
		font-weight: bold;
		color: inherit;
	}

	#advads_overview_pef p.aa_overview_pef_copy {
		margin: 0 0 44px;
		font-size: 18px;
		line-height: 22px;
		font-weight: normal;
		color: inherit;
	}

	#advads_overview_pef p.aa_overview_pef_copy:last-child {
		margin-bottom: 0;
	}

	#advads_overview_pef div.aa_overview_pef_lower {
		padding: 22px 44px 22px 208px;
		color: inherit;
	}

	#advads_overview_pef p.aa_overview_pef_cta {
		margin: 0;
		font-size: 18px;
		font-weight: bold;
		color: inherit;
	}

	#advads_overview_pef a.aa_overview_pef_button {
		display: inline-block;
		margin-left: 22px;
		padding: 8px 22px;
		font-size: 18px;
		font-weight: bold;
		border-radius: 5px;
		color: #fff;
		background: #1b193a;
		text-decoration: none;
	}
</style>
<script>
	jQuery( document ).on( 'click', '.aa_overview_pef_dismiss', function ( ev ) {
		ev.preventDefault();
		wp.ajax.post(
			'advanced_ads_pef',
			{
				_ajax_nonce: '<?php echo esc_js( wp_create_nonce( 'advanced_ads_pef' ) ); ?>',
				version:     '<?php echo esc_js( ADVADS_VERSION ); ?>'
			}
		).done( function () {
			jQuery( '#advads_overview_pef' ).remove();
		} );
	} );
</script>
<div id="advads_overview_pef" class="postbox position-full">
	<div class="aa_overview_pef_upper">
		<p class="aa_overview_pef_dismiss"><a class="dashicons dashicons-dismiss" href="#"></a></p>
		<p class="aa_overview_pef_subhead"><?php echo esc_html( $winner['subheading'] ); ?></p>
		<h3 class="aa_overview_pef_head"><?php echo esc_html( $winner['heading'] ); ?></h3>
		<p class="aa_overview_pef_copy"><?php echo wp_kses_post( $winner['text'] ); ?></p>
	</div>
	<div class="aa_overview_pef_lower">
		<p class="aa_overview_pef_cta">
			<?php echo esc_html( $winner['cta'] ); ?>
			<a class="aa_overview_pef_button" href="<?php echo esc_url( $this->build_link( $winner, $screen ) ); ?>" target="_blank">
				<?php echo esc_html( $winner['cta_button'] ); ?>
			</a>
		</p>
	</div>
</div>
