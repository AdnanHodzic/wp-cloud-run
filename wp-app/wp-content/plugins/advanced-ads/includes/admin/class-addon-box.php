<?php
/**
 * Admin Addon Box.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Addon Box.
 */
class Addon_Box {
	/**
	 * Hide active plugins
	 *
	 * @var bool
	 */
	private $hide_activated;

	/**
	 * Internal plugins data
	 *
	 * @var array
	 */
	private $plugins;

	/**
	 * Constructor
	 *
	 * @param bool $hide_activated whether to hide active plugins.
	 */
	public function __construct( $hide_activated = false ) {
		if ( ! is_admin() ) {
			return;
		}
		$this->hide_activated = (bool) $hide_activated;
		$this->build_plugins_data();
	}

	/**
	 * Build the internal plugin data
	 *
	 * @return void
	 */
	private function build_plugins_data() {
		// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned,WordPress.Arrays.MultipleStatementAlignment.LongIndexSpaceBeforeDoubleArrow
		$aa_plugins = [
			'advanced-ads-pro/advanced-ads-pro.php'                 => [
				'id'            => 'pro',
				'constant'      => 'AAP_VERSION',
				'title'         => 'Advanced Ads Pro',
				'description'   => __( 'Take the monetization of your website to the next level.', 'advanced-ads' ),
				'download_link' => 'https://wpadvancedads.com/add-ons/advanced-ads-pro/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'manual'        => 'https://wpadvancedads.com/manual/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual',
			],
			'advanced-ads-responsive/responsive-ads.php'            => [
				'id'            => 'ampads',
				'constant'      => 'AAR_VERSION',
				'title'         => 'AMP Ads',
				'description'   => __( 'Integrate your ads on AMP (Accelerated Mobile Pages) and auto-convert your Google AdSense ad units for enhanced mobile performance.', 'advanced-ads' ),
				'download_link' => 'https://wpadvancedads.com/add-ons/responsive-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'manual'        => 'https://wpadvancedads.com/manual/ads-on-amp-pages/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual',
			],
			'advanced-ads-gam/advanced-ads-gam.php'                 => [
				'id'            => 'gam',
				'constant'      => 'AAGAM_VERSION',
				'title'         => 'Google Ad Manager Integration',
				'description'   => __( 'Simplify the process of implementing ad units from Google Ad Manager swiftly and without errors.', 'advanced-ads' ),
				'download_link' => 'https://wpadvancedads.com/add-ons/google-ad-manager/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'manual'        => 'https://wpadvancedads.com/manual/google-ad-manager-integration-manual/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual',
			],
			'advanced-ads-layer/layer-ads.php'                      => [
				'id'            => 'popuplayer',
				'constant'      => 'AAPLDS_VERSION',
				'title'         => 'PopUp and Layer Ads',
				'description'   => __( 'Capture attention with customizable pop-ups that ensure your ads and messages get noticed. Set timing and closing options for optimal user engagement.', 'advanced-ads' ),
				'download_link' => 'https://wpadvancedads.com/add-ons/popup-and-layer-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'manual'        => 'https://wpadvancedads.com/manual/popup-and-layer-ads-documentation/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual',
			],
			'advanced-ads-selling/advanced-ads-selling.php'         => [
				'id'            => 'sellingads',
				'constant'      => 'AASA_VERSION',
				'title'         => 'Selling Ads',
				'description'   => __( 'Earn more money by enabling advertisers to buy ad space directly on your site’s frontend.', 'advanced-ads' ),
				'download_link' => 'https://wpadvancedads.com/add-ons/selling-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'manual'        => 'https://wpadvancedads.com/manual/selling-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual',
			],
			'advanced-ads-sticky-ads/sticky-ads.php'                => [
				'id'            => 'stickyads',
				'constant'      => 'AASADS_SLUG',
				'title'         => 'Sticky Ads',
				'description'   => __( 'Increase click rates by anchoring ads in sticky positions above, alongside, or below your website.', 'advanced-ads' ),
				'download_link' => 'https://wpadvancedads.com/add-ons/sticky-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'manual'        => 'https://wpadvancedads.com/manual/sticky-ads-documentation/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual',
			],
			'advanced-ads-tracking/tracking.php'                    => [
				'id'            => 'tracking',
				'constant'      => 'AAT_VERSION',
				'title'         => 'Tracking',
				'description'   => __( 'Monitor your ad performance to maximize your revenue.', 'advanced-ads' ),
				'download_link' => 'https://wpadvancedads.com/add-ons/tracking/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'manual'        => 'https://wpadvancedads.com/manual/tracking-documentation/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual',
			],
			'advanced-ads-slider/slider.php'                        => [
				'id'            => 'adslider',
				'constant'      => 'AAS_VERSION',
				'title'         => 'Ad Slider',
				'description'   => __( 'Create a beautiful ad slider and increase the ad impressions per page view. Free add-on for subscribers to our newsletter.', 'advanced-ads' ),
				'download_link' => 'https://wpadvancedads.com/add-ons/slider/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'manual'        => 'https://wpadvancedads.com/manual/ad-slider/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual',
			],
			'advanced-ads-adsense-in-feed/advanced-ads-in-feed.php' => [
				'id'            => 'adsenseinfeed',
				'constant'      => 'AAINF_VERSION',
				'title'         => 'AdSense In-Feed',
				'description'   => __( 'Place AdSense In-feed ads between posts on homepage, category, and archive pages for optimal engagement.', 'advanced-ads' ),
				'download_link' => wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=advanced-ads-adsense-in-feed' ), 'install-plugin_advanced-ads-adsense-in-feed' ),
				'manual'        => 'https://wpadvancedads.com/add-adsense-in-feed-to-wordpress/#Adding_the_In-feed_ad_to_your_WordPress_site',
			],
		];
		// phpcs:enable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned,WordPress.Arrays.MultipleStatementAlignment.LongIndexSpaceBeforeDoubleArrow

		$plugins       = get_plugins();
		$this->plugins = [
			'plugins'       => [],
			'premium_level' => 'free',
		];

		$pro               = false;
		$tracking          = false;
		$bundle_indicators = [
			'ampads',
			'stickyads',
			'gam',
			'sellingads',
			'popuplayer',
		];

		foreach ( $aa_plugins as $file => $data ) {
			if ( array_key_exists( $file, $plugins ) ) {
				$this->plugins['plugins'][ $data['id'] ] = [ 'status' => defined( $data['constant'] ) ? 'active' : 'installed' ];
				if ( in_array( $data['id'], $bundle_indicators, true ) ) {
					$this->plugins['premium_level'] = 'bundle';
				}
				if ( in_array( $data['id'], [ 'pro', 'tracking' ], true ) ) {
					${$data['id']} = true;
				}
			} else {
				$this->plugins['plugins'][ $data['id'] ] = [ 'status' => 'missing' ];
			}
			$this->plugins['plugins'][ $data['id'] ]['file'] = $file;
			if ( isset( $plugins[ $file ] ) ) {
				$this->plugins['plugins'][ $data['id'] ]['name'] = $plugins[ $file ]['Name'];
			}
			$this->plugins['plugins'][ $data['id'] ] += $data;
		}

		if ( 'bundle' === $this->plugins['premium_level'] ) {
			return;
		}

		if ( $pro || $tracking ) {
			$this->plugins['premium_level'] = 'premium';
		}

		if ( $pro && $tracking ) {
			$this->plugins['premium_level'] = 'bundle';
		}
	}

	/**
	 * Get feature grid data for an addon
	 *
	 * @param string $id add-on internal ID.
	 *
	 * @return array
	 */
	private function get_grid_data( $id ) {
		if ( 'pro' === $id ) {
			return [
				__( 'Cache Busting', 'advanced-ads' ),
				__( 'Click Fraud Protection', 'advanced-ads' ),
				__( 'Lazy Loading', 'advanced-ads' ),
				__( 'Anti Ad Blocker', 'advanced-ads' ),
				__( 'Geo Targeting', 'advanced-ads' ),
				__( '+23 Conditions', 'advanced-ads' ),
				__( '+11 Placements', 'advanced-ads' ),
				__( 'Parallax Ads', 'advanced-ads' ),
				__( 'Ad Grids', 'advanced-ads' ),
				__( 'Ad Refresh', 'advanced-ads' ),
				__( 'A/B Tests', 'advanced-ads' ),
			];
		}

		if ( 'tracking' === $id ) {
			return [
				__( 'Impressions & Clicks', 'advanced-ads' ),
				__( 'Click-Through Rate', 'advanced-ads' ),
				__( 'Statistics', 'advanced-ads' ),
				__( 'Google Analytics', 'advanced-ads' ),
				__( 'Local Data Processing', 'advanced-ads' ),
				__( 'Email Reports', 'advanced-ads' ),
				__( 'Link Cloaking', 'advanced-ads' ),
			];
		}

		return [];
	}

	/**
	 * Sort plugins by status (missing|installed|enabled)
	 *
	 * @param array $a plugin data.
	 * @param array $b plugin data.
	 *
	 * @return int
	 */
	private function sort_by_status( $a, $b ) {
		static $order;
		if ( null === $order ) {
			$order = [
				'missing'   => 0,
				'installed' => 1,
				'active'    => 2,
			];
		}

		return $order[ $a['status'] ] < $order[ $b['status'] ] ? 1 : -1;
	}

	/**
	 * Sort extra add-ons by the order field
	 *
	 * @param int $a order to compare.
	 * @param int $b order to compare.
	 *
	 * @return mixed
	 */
	private function sort_by_order( $a, $b ) {
		return (int) $a['order'] - (int) $b['order'];
	}

	/**
	 * Get plugin list to display into a given section
	 *
	 * @param string $section section slug (installed|available|special).
	 *
	 * @return array[]
	 */
	private function get_displayable_items( $section ) {
		switch ( $section ) {
			case 'special':
				$items = [];
				if ( 'missing' === $this->plugins['plugins']['adslider']['status'] ) {
					$items['adslider'] = $this->plugins['plugins']['adslider'];
				}
				if ( 'missing' === $this->plugins['plugins']['adsenseinfeed']['status'] ) {
					$items['adsenseinfeed'] = $this->plugins['plugins']['adsenseinfeed'];
				}

				return $items;
			case 'available':
				if ( 'free' === $this->plugins['premium_level'] ) {
					return [
						'aa'       => [
							'id'     => 'aa',
							'upsell' => true,
						],
						'aalt'     => [ 'id' => 'aalt' ],
						'pro'      => array_merge( $this->plugins['plugins']['pro'], [ 'grid' => true ] ),
						'tracking' => array_merge( $this->plugins['plugins']['tracking'], [ 'grid' => true ] ),
					];
				}

				if ( 'premium' === $this->plugins['premium_level'] ) {
					$items = [
						'aa'   => [
							'id'     => 'aa',
							'upsell' => true,
						],
						'aalt' => [ 'id' => 'aalt' ],
					];

					if ( 'missing' === $this->plugins['plugins']['pro']['status'] ) {
						$items['pro']         = $this->plugins['plugins']['pro'];
						$items['pro']['grid'] = true;
					}

					if ( 'missing' === $this->plugins['plugins']['tracking']['status'] ) {
						$items['tracking']         = $this->plugins['plugins']['tracking'];
						$items['tracking']['grid'] = true;
					}

					return $items;
				}

				if ( 'bundle' === $this->plugins['premium_level'] ) {
					return [ 'aalt' => [ 'id' => 'aalt' ] ];
				}

				return [];
			default: // Installed add-ons.
				if ( 'free' === $this->plugins['premium_level'] && 'missing' === $this->plugins['plugins']['adslider']['status'] && 'missing' === $this->plugins['plugins']['adsenseinfeed']['status'] ) {
					return [
						'none' =>
							[
								'id' => 'none',
							],
					];
				}

				if ( 'bundle' === $this->plugins['premium_level'] ) {
					$items = [
						'aa' => [ 'id' => 'aa' ],
					]
					+ Arr::where(
						$this->get_special_add_ons(),
						function ( $item ) {
							return in_array( $item['status'], [ 'installed', 'active' ], true );
						}
					);

					return $items;
				}

				$displayable_items = Arr::where(
					$this->plugins['plugins'],
					function ( $item ) {
						return 'missing' !== $item['status'];
					}
				);

				usort( $displayable_items, [ $this, 'sort_by_status' ] );

				return $displayable_items;
		}
	}

	/**
	 * Get list of plugins of the "special" section
	 *
	 * @return array
	 */
	private function get_special_add_ons() {
		return Arr::where(
			$this->plugins['plugins'],
			function ( $item ) {
				return in_array( $item['id'], [ 'adslider', 'adsenseinfeed' ], true );
			}
		);
	}

	/**
	 * Print output for a single add-on (not in bundle)
	 *
	 * @param array  $item    plugin data.
	 * @param string $section section slug.
	 *
	 * @return void
	 */
	private function do_single_item( $item, $section ) {
		if ( 'aa' === $item['id'] ) {
			$this->all_access( $item['upsell'] ?? false );

			return;
		}

		if ( 'aalt' === $item['id'] ) {
			$this->all_access_long_term();

			return;
		}

		if ( 'none' === $item['id'] ) {
			?>
			<div class="single-item none">
				<div class="item-details">
					<div class="icon"><img src="<?php echo esc_url( ADVADS_BASE_URL . 'assets/img/add-ons/aa-addons-icons-empty.svg' ); ?>" alt=""/></div>
					<span></span>
					<div class="name"><?php esc_html_e( 'No add-ons installed', 'advanced-ads' ); ?></div>
					<span></span>
					<div class="description">
						<?php esc_html_e( 'Please select from the list below.', 'advanced-ads' ); ?>
						<a href="https://wpadvancedads.com/manual/how-to-install-an-add-on/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-install-add-ons" target="_blank">
							<?php esc_html_e( 'Learn how to download, install, and activate an add-on', 'advanced-ads' ); ?>
						</a>
					</div>
					<span></span>
					<div class="cta"></div>
				</div>
			</div>
			<?php
			return;
		}

		$button_class = $item['status'];

		if ( $this->hide_activated && 'active' === $button_class ) {
			return;
		}

		if ( 'active' === $item['status'] ) {
			$button_class .= ' disabled';
		}

		if ( 'special' === $section && 'adslider' === $item['id'] ) {
			$button_class = Conditional::user_can_subscribe( 'nl_free_addons' ) ? 'subscribe' : 'subscribed';
		}

		$grid_data     = ! empty( $item['grid'] ) ? $this->get_grid_data( $item['id'] ) : false;
		$button_target = 'available' === $section ? '_blank' : '_self';

		?>
		<div class="single-item">
			<div class="item-details <?php echo esc_attr( $item['status'] ); ?>">
				<div class="icon"><img src="<?php echo esc_url( ADVADS_BASE_URL . 'assets/img/add-ons/aa-addons-icons-' . $item['id'] . '.svg' ); ?>" alt=""/></div>
				<span></span>
				<div class="name"><?php echo esc_html( $item['title'] ); ?></div>
				<span></span>
				<div class="description"><?php echo esc_html( $item['description'] ); ?></div>
				<span></span>
				<div class="cta">
					<a href="<?php echo esc_url( $this->get_button_target( $item['id'], $section ) ); ?>"
						<?php if ( 'subscribe' === $button_class ) : ?>
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'advads-newsletter-subscribe' ) ); ?>"
						<?php endif; ?>
						class="<?php echo esc_attr( "button $button_class" ); ?>" target="<?php echo esc_attr( $button_target ); ?>">
						<i class="dashicons"></i>
						<?php
						switch ( $button_class ) {
							case 'subscribe':
								esc_html_e( 'Subscribe now', 'advanced-ads' );
								break;
							case 'subscribed':
								esc_html_e( 'Download', 'advanced-ads' );
								break;
							case 'missing':
								echo 'adsenseinfeed' === $item['id'] ? esc_html__( 'Install now', 'advanced-ads' ) : esc_html__( 'Upgrade', 'advanced-ads' );
								break;
							case 'installed':
								esc_html_e( 'Activate now', 'advanced-ads' );
								break;
							default: // active disabled.
								esc_html_e( 'Active', 'advanced-ads' );
						}
						?>
					</a>
					<?php if ( in_array( $section, [ 'installed', 'special' ], true ) ) : ?>
						<div class="external-link">
							<a href="<?php echo esc_url( $item['manual'] ); ?>" target="_blank">
								<i class="dashicons dashicons-welcome-learn-more"></i>
								<span><?php esc_html_e( 'See the manual', 'advanced-ads' ); ?></span>
							</a>
						</div>
					<?php elseif ( 'available' === $section ) : ?>
						<div class="external-link">
							<a href="<?php echo esc_url( $item['download_link'] ); ?>" target="_blank">
								<span><?php esc_html_e( 'Learn more', 'advanced-ads' ); ?></span>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( $grid_data ) : ?>
				<div class="item-grid">
					<?php foreach ( $grid_data as $elem ) : ?>
						<div class="feature">
							<i class="dashicons"></i>
							<span><?php echo esc_html( $elem ); ?></span>
						</div>
					<?php endforeach; ?>
					<div class="feature more">
						<i class="dashicons"></i>
						<span><?php esc_html_e( 'many more features', 'advanced-ads' ); ?></span>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get the href attribute of a call-to-action link
	 *
	 * @param string $id      internal plugin ID.
	 * @param string $section section slug.
	 *
	 * @return mixed|string
	 */
	private function get_button_target( $id, $section = 'installed' ) {
		if ( 'available' === $section ) {
			$link = $this->plugins['plugins'][ $id ]['download_link'];
			if ( 'tracking' !== $id ) {
				$link = 'https://wpadvancedads.com/pricing/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons';
			}

			return $link;
		}

		if ( 'special' === $section && 'adslider' === $id ) {
			return 'https://wpadvancedads.com/subscriber-resources/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
		}

		if ( 'installed' === $section ) {
			if ( 'installed' === $this->plugins['plugins'][ $id ]['status'] ) {
				if ( version_compare( '6.5.0', get_bloginfo( 'version' ), '<=' ) ) {
					return '#activate-aaplugin_' . wp_create_nonce( 'updates' ) . '_' . $this->plugins['plugins'][ $id ]['file'] . '_' . $this->plugins['plugins'][ $id ]['name'];
				}

				return wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $this->plugins['plugins'][ $id ]['file'] . '&amp', 'activate-plugin_' . $this->plugins['plugins'][ $id ]['file'] );
			}

			return 'https://wpadvancedads.com/account/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons';
		}

		if ( 'special' === $section && 'adsenseinfeed' === $id && 'missing' === $this->plugins['plugins'][ $id ]['status'] ) {
			return wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=advanced-ads-adsense-in-feed' ), 'install-plugin_advanced-ads-adsense-in-feed' );
		}

		return '#';
	}

	/**
	 * Print All Access Long Term output
	 *
	 * @return void
	 */
	private function all_access_long_term() {
		?>
		<div class="single-item">
			<div class="item-details">
				<div class="icon">
					<img src="<?php echo esc_url( ADVADS_BASE_URL . 'assets/img/add-ons/aa-addons-icons-allaccesslt.svg' ); ?>" alt=""/>
				</div>
				<span></span>
				<div class="name">
					<?php esc_html_e( 'Advanced Ads All Access long-term', 'advanced-ads' ); ?>
				</div>
				<span></span>
				<div class="description">
					<?php esc_html_e( 'Secure 4 years of ongoing support and updates with just one payment. Enjoy savings of up to 70% compared to individual add-on purchases.', 'advanced-ads' ); ?>
				</div>
				<span></span>
				<div class="cta">
					<div>
						<a href="https://wpadvancedads.com/add-ons/all-access-long-term/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons" class="button upsell" target="_blank">
							<i class="dashicons"></i>
							<?php esc_html_e( 'Upgrade', 'advanced-ads' ); ?>
						</a>
					</div>
					<div class="external-link">
						<a href="https://wpadvancedads.com/add-ons/all-access-long-term/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons"><?php esc_html_e( 'Learn more', 'advanced-ads' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Print All Access output
	 *
	 * @param bool $is_upsell whether it's an upsell or an installed (assumed) bundle.
	 *
	 * @return void
	 */
	private function all_access( $is_upsell = false ) {
		$all_access_items = [
			'pro',
			'tracking',
			'stickyads',
			'popuplayer',
			'ampads',
			'gam',
			'sellingads',
		];

		?>
		<div class="bundle">
			<div class="bundle-details">
				<div class="icon">
					<img src="<?php echo esc_url( ADVADS_BASE_URL . 'assets/img/add-ons/aa-addons-icons-allaccess.svg' ); ?>" alt=""/>
				</div>
				<span></span>
				<div class="name">
					<?php esc_html_e( 'Advanced Ads All Access', 'advanced-ads' ); ?>
				</div>
				<span></span>
				<div class="description">
					<?php esc_html_e( 'Every tool you need for website success in one package. Enjoy our complete suite of add-ons for limitless possibilities.', 'advanced-ads' ); ?>
				</div>
				<span></span>
				<div class="cta">
					<?php if ( $is_upsell ) : ?>
						<div>
							<a href="https://wpadvancedads.com/pricing/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons" class="button upsell" target="_blank">
								<i class="dashicons"></i><?php esc_html_e( 'Upgrade', 'advanced-ads' ); ?>
							</a>
						</div>
						<div class="external-link">
							<a href="https://wpadvancedads.com/add-ons/all-access/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons"><?php esc_html_e( 'Learn more', 'advanced-ads' ); ?></a>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="bundle-items">
				<?php
				foreach ( $all_access_items as $item ) {
					$this->do_bundle_item( $item, $is_upsell );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Print output of a bundle's item
	 *
	 * @param string $id        internal plugin ID.
	 * @param bool   $is_upsell whether it's for an upsell. False if a bundle is assumed existing.
	 *
	 * @return void
	 */
	private function do_bundle_item( $id, $is_upsell = false ) {
		$button_class = $this->plugins['plugins'][ $id ]['status'];

		if ( 'active' === $this->plugins['plugins'][ $id ]['status'] ) {
			$button_class .= ' disabled';
		}

		if ( $is_upsell ) {
			$button_class = 'disabled';
		}

		$plugin = $this->plugins['plugins'][ $id ];

		?>
		<div class="bundle-item">
			<div class="icon"><img src="<?php echo esc_url( ADVADS_BASE_URL . 'assets/img/add-ons/aa-addons-icons-' . $id . '.svg' ); ?>" alt=""/></div>
			<span></span>
			<div class="name"><?php echo esc_html( $plugin['title'] ); ?></div>
			<span></span>
			<div class="description">
				<?php echo esc_html( $plugin['description'] ); ?>
				<a href="<?php echo esc_url( $plugin['download_link'] ); ?>" target="_blank"><?php esc_html_e( 'Learn more', 'advanced-ads' ); ?></a>
			</div>
			<span></span>
			<div class="cta">
				<div>
					<a href="<?php echo $is_upsell ? '#' : esc_url( $this->get_button_target( $id ) ); ?>"
						class="<?php echo esc_attr( "button $button_class" ); ?>"
						target="<?php echo 'installed' === $plugin['status'] ? '_self' : '_blank'; ?>">
						<?php if ( ! $is_upsell ) : ?>
							<i class="dashicons"></i>
						<?php endif; ?>
						<?php
						if ( $is_upsell ) {
							esc_html_e( 'Included', 'advanced-ads' );
						} elseif ( 'active' === $plugin['status'] ) {
							esc_html_e( 'Active', 'advanced-ads' );
						} elseif ( 'installed' === $plugin['status'] ) {
							esc_html_e( 'Activate now', 'advanced-ads' );
						} else {
							esc_html_e( 'Download', 'advanced-ads' );
						}
						?>
					</a>
				</div>
			</div>
		</div>
		<div class="separator"></div>
		<?php
	}

	/**
	 * Displays the add-on box content
	 *
	 * @param bool $is_dashboard whether it's displayed on the dashboard screen.
	 *
	 * @return void
	 */
	public function display( $is_dashboard = true ) {
		?>
		<div id="advanced-ads-addon-box">
			<span class="subheader"><?php esc_html_e( 'Installed Add-ons', 'advanced-ads' ); ?></span>
			<?php foreach ( $this->get_displayable_items( 'installed' ) as $item ) : ?>
				<?php $this->do_single_item( $item, 'installed' ); ?>
			<?php endforeach; ?>
			<span class="subheader"><?php esc_html_e( 'Available Add-ons', 'advanced-ads' ); ?></span>
			<?php foreach ( $this->get_displayable_items( 'available' ) as $item ) : ?>
				<?php $this->do_single_item( $item, 'available' ); ?>
			<?php endforeach; ?>
			<span class="subheader"><?php esc_html_e( 'Free Add-ons & Special Purpose', 'advanced-ads' ); ?></span>
			<?php foreach ( $this->get_displayable_items( 'special' ) as $item ) : ?>
				<?php $this->do_single_item( $item, 'special' ); ?>
			<?php endforeach; ?>
			<?php
			$add_ons = apply_filters( 'advanced-ads-overview-add-ons', [] );
			uasort( $add_ons, [ $this, 'sort_by_order' ] );
			?>
			<?php foreach ( $add_ons as $add_on ) : ?>
				<?php
				if ( ! $is_dashboard && empty( $add_on['outside_dashboard'] ) ) {
					continue;
				}
				?>
				<div class="single-item add-on <?php echo ! empty( $add_on['class'] ) ? esc_attr( $add_on['class'] ) : ''; ?>">
					<div class="item-details">
						<div class="icon">
							<img src="<?php echo esc_url( ! empty( $add_on['icon'] ) ? $add_on['icon'] : ADVADS_BASE_URL . 'assets/img/add-ons/aa-addons-icons-empty.svg' ); ?>" alt=""/>
						</div>
						<span></span>
						<div class="name"><?php echo esc_html( $add_on['title'] ); ?></div>
						<span></span>
						<div class="description"><?php echo esc_html( $add_on['desc'] ); ?></div>
						<span></span>
						<div class="cta <?php echo ! empty( $add_on['link'] ) && ! empty( $add_on['link_primary'] ) && $add_on['link_primary'] ? 'primary' : 'secondary'; ?>">
							<?php if ( ! empty( $add_on['link'] ) ) : ?>
								<a href="<?php echo esc_url( $add_on['link'] ); ?>" class="button">
									<?php if ( ! empty( $add_on['link_icon'] ) ) : ?>
										<i class="dashicons <?php echo esc_attr( $add_on['link_icon'] ); ?>"></i>
									<?php endif; ?>
									<?php echo ! empty( $add_on['link_title'] ) ? esc_html( $add_on['link_title'] ) : esc_html__( 'Get this add-on', 'advanced-ads' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<footer>
			<a href="https://wpadvancedads.com/manual/how-to-install-an-add-on/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-install-add-ons" target="_blank">
				<?php esc_html_e( 'How to download, install, and activate an add-on', 'advanced-ads' ); ?>
				<span class="screen-reader-text"> (opens in a new tab)</span>
				<span aria-hidden="true" class="dashicons dashicons-external"></span>
			</a>
		</footer>
		<?php
	}
}
