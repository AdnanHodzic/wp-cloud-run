<?php
/**
 * Importers Manager.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use WP_Error;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Importers Manager.
 */
class Manager implements Integration_Interface {

	/**
	 * Importer history option key.
	 *
	 * @var string
	 */
	const HISTORY_OPTION_KEY = '_advads_importer_history';

	/**
	 * Hold all registered importers
	 *
	 * @var array
	 */
	private $importers = [];

	/**
	 * Hold message to display on page
	 *
	 * @var string|WP_Error
	 */
	private $message = false;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		$this->register_importers();
		add_action( 'admin_init', [ $this, 'handle_action' ] );
	}

	/**
	 * Get importers
	 *
	 * @return array
	 */
	public function get_importers(): array {
		return $this->importers;
	}

	/**
	 * Handle importing
	 *
	 * @return void
	 */
	public function handle_action(): void {
		// Early bail!!
		$action = WordPress::current_action();

		if ( 'advads_import' === $action && check_admin_referer( 'advads_import' ) ) {
			$importer      = Params::post( 'importer' );
			$importer      = $this->importers[ $importer ];
			$this->message = $importer->import();
		}

		if ( 'advads_export' === $action && check_admin_referer( 'advads_export' ) ) {
			$exporter      = new Plugin_Exporter();
			$this->message = $exporter->download_file();
		}

		if ( 'advads_import_delete' === $action && check_admin_referer( 'advads_import_delete' ) ) {
			$this->rollback_import();
		}
	}

	/**
	 * Rollback import
	 *
	 * @return void
	 */
	private function rollback_import(): void {
		$session_key  = Params::get( 'session_key' );
		$session_data = $this->delete_session_history( $session_key );
		if ( ! $session_data ) {
			return;
		}

		$importer = $this->get_importer( $session_data['importer_id'] );
		$importer->rollback( $session_key );

		$this->message = __( 'History deleted successfully.', 'advanced-ads' );
	}

	/**
	 * Register importers
	 *
	 * @return void
	 */
	private function register_importers(): void {
		$this->register_importer( Tutorials::class );
		$this->register_importer( Google_Sheet::class );
		$this->register_importer( Ad_Inserter::class );
		$this->register_importer( Ads_WP_Ads::class );
		$this->register_importer( Amp_WP_Ads::class );
		$this->register_importer( Quick_Adsense::class );
		$this->register_importer( WP_Quads::class );
		$this->register_importer( XML_Importer::class );
		$this->register_importer( Api_Ads::class );
	}

	/**
	 * Register custom type.
	 *
	 * @param string $classname Type class name.
	 *
	 * @return void
	 */
	public function register_importer( $classname ): void {
		$importer = new $classname();

		$this->importers[ $importer->get_id() ] = $importer;
	}

	/**
	 * Get the registered importer
	 *
	 * @param string $id Importer to get.
	 *
	 * @return mixed|bool
	 */
	public function get_importer( $id ) {
		return $this->importers[ $id ] ?? false;
	}

	/**
	 * Display any message
	 *
	 * @return void
	 */
	public function display_message(): void {
		// Early bail!!
		if ( empty( $this->message ) ) {
			return;
		}

		if ( is_array( $this->message ) ) {
			foreach ( $this->message as $message ) {
				$type    = $message[0] ?? 'success';
				$message = $message[1] ?? '';
				?>
				<div class="notice notice-<?php echo esc_attr( $type ); ?>">
					<p><?php echo $message; // phpcs:ignore ?></p>
				</div>
				<?php
			}

			return;
		}

		$type    = 'success';
		$message = $this->message;

		if ( is_wp_error( $this->message ) ) {
			$type    = 'error';
			$message = $this->message->get_error_message();
		}

		?>
		<div class="notice notice-<?php echo $type; // phpcs:ignore ?>">
			<p><?php echo $message; // phpcs:ignore ?></p>
		</div>
		<?php
	}

	/**
	 * Add row to session history
	 *
	 * @param string $importer_id Importer id.
	 * @param string $key         Session key.
	 * @param int    $count       Ad and Placement created.
	 *
	 * @return void
	 */
	public function add_session_history( $importer_id, $key, $count ): void {
		$history = get_option( self::HISTORY_OPTION_KEY, [] );

		$history[ $key ] = [
			'importer_id' => $importer_id,
			'session_key' => $key,
			'count'       => $count,
			'created_at'  => wp_date( 'U' ),
		];

		update_option( self::HISTORY_OPTION_KEY, $history );
	}

	/**
	 * Delete row from session history
	 *
	 * @param string $key Session key.
	 *
	 * @return array|bool
	 */
	public function delete_session_history( $key ) {
		$return  = false;
		$history = get_option( self::HISTORY_OPTION_KEY, [] );

		if ( isset( $history[ $key ] ) ) {
			$return = $history[ $key ];
			unset( $history[ $key ] );
			update_option( self::HISTORY_OPTION_KEY, $history );
		}

		return $return;
	}
}
