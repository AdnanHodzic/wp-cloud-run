<?php
/**
 * Import Tutorials.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use AdvancedAds\Interfaces\Importer as Interface_Importer;

defined( 'ABSPATH' ) || exit;

/**
 * Tutorials.
 */
class Tutorials extends Importer implements Interface_Importer {

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string {
		return 'import-tutorials';
	}

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string {
		return __( 'Tutorials', 'advanced-ads' );
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
		return '<span class="dashicons dashicons-book"></span>';
	}

	/**
	 * Detect the importer in database.
	 *
	 * @return bool True if detected; otherwise, false.
	 */
	public function detect(): bool {
		return true;
	}

	/**
	 * Show import button or not.
	 *
	 * @return bool
	 */
	public function show_button(): bool {
		return false;
	}

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render_form(): void {
		?>
		<p class="text-base m-0">
			<?php
			echo wp_kses_post(
				__( 'While these other import options are still in beta, we do have resources on how to make setting up your first ad easier than ever.  If you have any specific feature requests please make sure to contact us or <a href="https://wpadvancedads.com/support/missing-feature/">request a feature</a>.', 'advanced-ads' )
			);
			?>
		</p>

		<h3 class="mt-8"><?php esc_html_e( 'Quick Links', 'advanced-ads' ); ?></h3>

		<ul class="text-primary space-y-4">
			<li>
				<a href="https://wpadvancedads.com/manual/import-export/?utm_source=advanced-ads&utm_medium=link&utm_campaign=tools-quicklinks" target="_blank">
					<span><?php esc_html_e( 'Import and Export', 'advanced-ads' ); ?></span>
				</a>
			</li>
			<li>
				<a href="https://wpadvancedads.com/manual/ad-templates/?utm_source=advanced-ads&utm_medium=link&utm_campaign=tools-quicklinks" target="_blank">
					<span><?php esc_html_e( 'Ad Templates', 'advanced-ads' ); ?></span>
				</a>
			</li>
		</ul>
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
}
