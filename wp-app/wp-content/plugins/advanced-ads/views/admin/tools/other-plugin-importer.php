<?php
/**
 * Render Importers
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Utilities\WordPress;

$importers = wp_advads()->importers;
?>
<div class="advads-other-plugin-importer mt-8">
	<header>
		<h2 class="advads-h2"><?php esc_html_e( 'Other Plugins', 'advanced-ads' ); ?></h2>
		<p class="text-sm"><?php esc_html_e( 'To make things even easier, we are working on new ways to import your settings and data from other plugins.', 'advanced-ads' ); ?></p>
	</header>
	<div class="advads-tab-container">
		<div class="advads-tab-menu">
			<?php
			foreach ( $importers->get_importers() as $importer ) :
				if ( ! $importer->is_detected() ) {
					continue;
				}
				?>
			<a id="nav-<?php echo esc_attr( $importer->get_id() ); ?>" href="#<?php echo esc_attr( $importer->get_id() ); ?>">
				<?php echo $importer->get_icon(); // phpcs:ignore ?>
				<span><?php echo esc_html( $importer->get_title() ); ?></span>
			</a>
			<?php endforeach; ?>
		</div>
		<div class="advads-tab-content">
			<?php
			foreach ( $importers->get_importers() as $importer ) :
				if ( ! $importer->is_detected() ) {
					continue;
				}
				?>
			<form
				id="<?php echo esc_attr( $importer->get_id() ); ?>"
				class="advads-tab-target"
				method="post"
				action="<?php echo esc_url( Params::server( 'REQUEST_URI' ) . '#' . $importer->get_id() ); ?>">
				<?php wp_nonce_field( 'advads_import' ); ?>
				<input type="hidden" name="action" value="advads_import">
				<input type="hidden" name="importer" value="<?php echo esc_attr( $importer->get_id() ); ?>">
				<div class="advads-tab-content-body">
					<?php if ( $importer->get_description() ) : ?>
					<p class="text-base"><?php echo esc_html( $importer->get_description() ); ?></p>
					<?php endif; ?>
					<div class="pt-2">
						<?php $importer->render_form(); ?>
					</div>
				</div>
				<?php if ( $importer->show_button() ) : ?>
				<div class="advads-tab-content-footer">
					<button class="button button-primary button-large" type="submit">
						<?php esc_html_e( 'Start Importing', 'advanced-ads' ); ?>&nbsp;<?php echo esc_html( $importer->get_title() ); ?>
					</button>
				</div>
				<?php endif; ?>
			</form>
			<?php endforeach; ?>
		</div>
	</div>
</div>
