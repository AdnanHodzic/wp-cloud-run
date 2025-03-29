<?php
/**
 * Enable Privacy Module.
 *
 * @since   1.47.0
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var bool   $module_enabled                Whether the privacy module is enabled.
 * @var array  $methods                       Available privacy methods.
 * @var string $current_method                Currently chosen method.
 * @var string $custom_cookie_name            Name of custom cookie, if this setting is chosen.
 * @var string $custom_cookie_value           (Partial) Value of custom cookie, if this setting is chosen.
 * @var bool   $show_non_personalized_adsense Whether to show non-personalized ads until custom cookie consent is given.
 * @var string $opening_link_to_pro           Opening link tag for link to Pro (either upsell or settings).
 */

use AdvancedAds\Compatibility\Compatibility;
use AdvancedAds\Tracking\Helpers;
use AdvancedAds\Utilities\Conditional;

?>
<input name="<?php echo esc_attr( Advanced_Ads_Privacy::OPTION_KEY ); ?>[enabled]" id="<?php echo esc_attr( Advanced_Ads_Privacy::OPTION_KEY ); ?>_enabled" type="checkbox" <?php checked( $module_enabled ); ?> class="advads-has-sub-settings"/>
<label for="<?php echo esc_attr( Advanced_Ads_Privacy::OPTION_KEY ); ?>_enabled">
	<?php esc_html_e( 'Show ads only to users who give their permission to cookies and ads.', 'advanced-ads' ); ?>
</label>

<div class="advads-sub-settings">
	<h4><?php esc_html_e( 'Consent method', 'advanced-ads' ); ?></h4>
	<ul>
		<?php
		foreach ( $methods as $method => $options ) :
			$checked = checked( $method, $current_method, false );
			?>
			<li>
				<label>
					<input type="radio" name="<?php echo esc_attr( Advanced_Ads_Privacy::OPTION_KEY ); ?>[consent-method]" value="<?php echo esc_attr( $method ); ?>" <?php echo esc_attr( $checked ); ?> />
					<?php
					echo esc_html( $options['label'] );
					if ( ! empty( $options['manual_url'] ) ) :
						?>
						&ndash; <a href="<?php echo esc_url( $options['manual_url'] ); ?>" target="_blank" class="advads-manual-link">
						<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
					</a>
					<?php endif; ?>
				</label>

				<?php if ( 'custom' === $method ) : ?>

					<div style="margin: 10px 24px;">
						<label>
							<?php esc_html_e( 'Cookie name', 'advanced-ads' ); ?>
							<input type="text" name="<?php echo esc_attr( Advanced_Ads_Privacy::OPTION_KEY ); ?>[custom-cookie-name]" value="<?php echo esc_attr( $custom_cookie_name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'advanced-ads' ); ?>" <?php echo $method === $current_method ? 'required' : ''; ?>/>
						</label>
						<label>
							<?php esc_html_e( 'contains', 'advanced-ads' ); ?>
							<?php esc_html_e( 'value', 'advanced-ads' ); ?>
							<input type="text" name="<?php echo esc_attr( Advanced_Ads_Privacy::OPTION_KEY ); ?>[custom-cookie-value]" value="<?php echo esc_attr( $custom_cookie_value ); ?>" placeholder="<?php esc_attr_e( 'Value', 'advanced-ads' ); ?>"/>
						</label>
						<label style="display: block; margin-top: 5px; margin-bottom: 7px;">
							<input type="checkbox" name="<?php echo esc_attr( Advanced_Ads_Privacy::OPTION_KEY ); ?>[show-non-personalized-adsense]" <?php checked( $show_non_personalized_adsense ); ?> />
							<?php esc_html_e( 'Show non-personalized AdSense ads until consent is given.', 'advanced-ads' ); ?>
						</label>
						<?php if ( Compatibility::borlabs_cookie_adsense_auto_ads_code_exists() ) : ?>
							<p class="description">
								<?php require GADSENSE_BASE_PATH . 'admin/views/borlabs-cookie-auto-ads-warning.php'; ?>
							</p>
						<?php endif; ?>
					</div>

					<?php if ( apply_filters( 'advanced-ads-privacy-custom-show-warning', ! empty( $checked ) && Conditional::has_cache_plugins() ) ) : ?>
						<p class="description" style="margin: 5px 0 10px 23px;">
							<span class="advads-notice-inline advads-error"><?php esc_html_e( 'It seems that a caching plugin is activated.', 'advanced-ads' ); ?></span>
							<br>
							<?php
							esc_html_e( 'Your users’ consent might get cached and show ads to users who didn’t give their consent yet. ', 'advanced-ads' );
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes already escaped.
							echo $opening_link_to_pro;
							esc_html_e( 'Cache-busting in Advanced Ads Pro solves that.', 'advanced-ads' );
							echo '</a>';
							?>
						</p>
					<?php endif; ?>
				<?php elseif ( 'iab_tcf_20' === $method ) : ?>
					<?php if ( apply_filters( 'advanced-ads-privacy-tcf-show-warning', ! empty( $checked ) ) ) : ?>
						<p class="description" style="margin: 5px 0 10px 23px;">
							<?php
							esc_html_e( 'Ads are loaded after the user gives their consent and reloads the page.', 'advanced-ads' );
							echo ' ';
							printf(
							/* Translators: 1: opening link tag with link to Advanced Ads Pro 2: closing link tag */
								esc_html__( 'Install %1$sAdvanced Ads Pro%2$s to reload the ads instantly without an additional page request.', 'advanced-ads' ),
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes already escaped.
								$opening_link_to_pro,
								'</a>'
							);
							?>
						</p>
					<?php endif; ?>
					<?php
					if ( method_exists( Helpers::class, 'has_tcf_conflict' ) ) :
						if ( Helpers::has_tcf_conflict() ) :
							?>
							<p class="advads-notice-inline advads-error">
								<?php esc_html_e( 'The selected tracking method is not compatible with the TCF 2.0 integration.', 'advanced-ads' ); ?>
							</p>
							<?php
						endif;
					endif;
					?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
