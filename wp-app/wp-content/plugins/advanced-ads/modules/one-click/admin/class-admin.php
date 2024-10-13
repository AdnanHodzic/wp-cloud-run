<?php
/**
 * The class is responsible for adding widget in the WordPress admin area.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick\Admin;

use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Modules\OneClick\Helpers;
use AdvancedAds\Modules\OneClick\Options;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin.
 */
class Admin implements Integration_Interface {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'advanced-ads-overview-add-ons', [ $this, 'add_addon_row' ] );
		add_action( 'advanced-ads-overview-widgets-after', [ $this, 'add_metabox' ] );
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue(): void {
		if ( ! Conditional::is_screen_advanced_ads() ) {
			return;
		}

		wp_advads()->registry->enqueue_style( 'app' );
		wp_advads()->registry->enqueue_script( 'app' );
	}

	/**
	 * Add row in addon widget
	 *
	 * @param array $addons Hold addons.
	 *
	 * @return array
	 */
	public function add_addon_row( $addons ): array {
		$is_connected = false !== Options::pubguru_config();

		$defaults = [
			'title' => __( 'MonetizeMore & PubGuru Integration', 'advanced-ads' ),
			'desc'  => __( 'Enables MonetizeMore users to link their settings with the PubGuru insights & analytics dashboard.', 'advanced-ads' ),
			'order' => 1000,
			'link'  => '#',
			'icon'  => ADVADS_BASE_URL . 'assets/img/add-ons/aa-addons-icons-m2pg.svg',
		];

		$addons['monetizemore-connect'] = $defaults + [
			'class'        => 'js-pubguru-connect',
			'link_title'   => __( 'Connect now', 'advanced-ads' ),
			'link_icon'    => 'dashicons-plus',
			'link_primary' => true,
		];

		$addons['monetizemore-disconnect'] = $defaults + [
			'class'      => 'js-pubguru-disconnect',
			'link_title' => __( 'Disconnect now', 'advanced-ads' ),
			'link_icon'  => 'dashicons-dismiss',
		];

		$key = $is_connected ? 'monetizemore-connect' : 'monetizemore-disconnect';

		$addons[ $key ]['class'] .= ' hidden';

		return $addons;
	}

	/**
	 * Add metabox
	 *
	 * @return void
	 */
	public function add_metabox(): void {
		$id     = 'advads-m2-connect';
		$config = Options::pubguru_config();
		$style  = false === $config ? 'style="display:none;"' : 'style="display:block;"';
		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="postbox position-full" <?php echo $style; // phpcs:ignore ?>>
			<h2>
				<?php
				if ( false === $config ) :
					esc_html_e( 'PubGuru OneClick Consent & Privacy Policy', 'advanced-ads' );
				else :
					esc_html_e( 'Connecting Your With Your PubGuru Account Settings', 'advanced-ads' );
			endif;
				?>
			</h2>
			<div class="inside">
			<div class="main">
				<?php $this->display_metabox(); ?>
				<?php do_action( 'advanced-ads-overview-widget-content-' . $id, $id ); ?>
			</div>
			</div>
		</div>
		<div id="pubguru-notices"></div>

		<?php
	}

	/**
	 * Pubguru connect widget
	 *
	 * @return void
	 */
	public function display_metabox(): void {
		$pubguru_config  = Options::pubguru_config();
		$has_traffic_cop = Helpers::has_traffic_cop();

		include ADVADS_ABSPATH . 'views/admin/metabox-pubguru-connect.php';
	}

	/**
	 * Is current page is PubGuru page
	 *
	 * @return bool
	 */
	public static function is_pubguru_page(): bool {
		global $hook_suffix;

		return null !== $hook_suffix && false !== strpos( $hook_suffix, 'advanced-ads' );
	}
}
