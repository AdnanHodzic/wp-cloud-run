<?php
/**
 * Placements page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var array   $placement_types placement types.
 * @var array[] $placements user-defined placements.
 * @var string  $orderby how to order placements.
 * @var bool    $has_placements there are use-defined placements.
 */

use AdvancedAds\Entities;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

$quick_actions           = [];
$quick_actions['delete'] = '<a style="cursor: pointer;" class="advads-delete-tag">' . __( 'Delete', 'advanced-ads' ) . '</a>';

$message = Params::get( 'message' );
?>
<div class="wrap">
	<h2 style="display: none;"><!-- There needs to be an empty H2 headline at the top of the page so that WordPress can properly position admin notifications --></h2>
	<?php
	if ( 'error' === $message ) :
		?>
		<div id="message" class="notice notice-error advads-admin-notice inline"><p><?php esc_html_e( 'Couldn’t create the new placement. Please check your form field and whether the name is already in use.', 'advanced-ads' ); ?></p></div>
	<?php elseif ( 'updated' === $message ) : ?>
		<div id="message" class="notice updated advads-admin-notice inline"><p><?php esc_html_e( 'Placements updated', 'advanced-ads' ); ?></p></div>
		<?php
	endif;

	// Add placement form.
	$modal_slug = 'placement-new';
	ob_start();
	if ( ! $has_placements ) :
		?>
		<p class="description">
			<?php
			echo esc_html( Entities::get_placement_description() );
			?>
			<a href="https://wpadvancedads.com/manual/placements/?utm_source=advanced-ads&utm_medium=link&utm_campaign=placements" target="_blank" class="advads-manual-link"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
		</p>
		<?php
	endif;
	include ADVADS_ABSPATH . 'admin/views/placement-form.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequire

	new Advanced_Ads_Modal(
		[
			'modal_slug'       => $modal_slug,
			'modal_content'    => ob_get_clean(),
			'modal_title'      => __( 'New Placement', 'advanced-ads' ),
			'close_action'     => __( 'Save New Placement', 'advanced-ads' ),
			'close_form'       => 'advads-placements-new-form',
			'close_validation' => 'advads_validate_new_form',
		]
	);

	if ( $has_placements ) :
		$existing_types = array_unique( array_column( $placements, 'type' ) );
		do_action( 'advanced-ads-placements-list-before', $placements );
		?>
		<form method="POST" action="" id="advanced-ads-placements-form">

			<?php
			$columns = [
				[
					'key'          => 'type_name',
					'display_name' => esc_html__( 'Type', 'advanced-ads' ) . ' / ' . esc_html__( 'Name', 'advanced-ads' ),
					'custom_sort'  => true,
				],
				[
					'key'          => 'options',
					'display_name' => esc_html__( 'Output', 'advanced-ads' ),
				],
				[
					'key'          => 'conditons',
					'display_name' => esc_html__( 'Delivery', 'advanced-ads' ),
				],
			];
			?>

			<?php if ( isset( $placement_types ) && ! empty( $placement_types ) ) : ?>
				<div class="tablenav top hidden advads-toggle-with-filters-button">
					<select class="advads_filter_placement_type">
						<option value="0"><?php esc_html_e( '- show all types -', 'advanced-ads' ); ?></option>
						<?php foreach ( $placement_types as $type_name => $placement_type ) : ?>
							<?php if ( in_array( $type_name, $existing_types, true ) ) : ?>
								<option value="<?php echo esc_attr( $type_name ); ?>"><?php echo esc_html( $placement_type['title'] ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
					<input type="text" class="advads_search_placement_name" placeholder="<?php esc_html_e( 'filter by name', 'advanced-ads' ); ?>"/>
				</div>
			<?php endif; ?>

			<table class="wp-list-table advads-placements-table widefat posts advads-table">
				<thead>
				<tr>
					<?php
					foreach ( $columns as $column ) {
						$class               = '';
						$column_display_name = $column['display_name'];

						if ( 'type_name' === $column['key'] ) :
							list ( $order_type, $name ) = explode( '/', $column_display_name );

							printf(
								'<th class="column-primary"><a href="#" class="advads-sort ' . ( 'type' === $orderby ? 'advads-placement-sorted' : '' ) . '"
								data-orderby="type" data-dir="asc">%1$s %2$s</a> / <a href="#" class="advads-sort ' . ( 'name' === $orderby ? 'advads-placement-sorted' : '' ) . '"
								data-orderby="name" data-dir="asc" style="margin-left:9px;">%3$s %2$s<a/></th>',
								esc_html( $order_type ),
								'<span class="advads-placement-sorting-indicator"></span>',
								esc_html( $name )
							);
						else :
							echo '<th>' . esc_html( $column_display_name ) . '</th>';
						endif;

						if ( false && ! empty( $column['custom_sort'] ) ) :
							$column_display_name = '<a href="#" class="advads-sort"
			data-orderby="name" data-dir="asc">
				' . $column_display_name . '
			<span class="advads-placement-sorting-indicator"></span></a>';
						endif;
					}

					do_action( 'advanced-ads-placements-list-column-header' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
					?>
				</tr>
				</thead>
				<tbody>
				<?php
				// Sort placements.
				$placements         = Advanced_Ads_Placements::sort( $placements, $orderby );
				$display_conditions = Advanced_Ads_Display_Conditions::get_instance();
				$visitor_conditions = Advanced_Ads_Visitor_Conditions::get_instance();

				foreach ( $placements as $_placement_slug => $_placement ) :
					unset( $quick_actions['usage'] );
					$type_missing = false;
					if ( isset( $_placement['type'] ) && ! isset( $placement_types[ $_placement['type'] ] ) ) {
						$missed_type        = $_placement['type'];
						$_placement['type'] = 'default';
						$type_missing       = true;
					}
					if ( ( $_placement['type'] ?? 'default' ) === 'default' ) {
						$_placement['type']     = 'default';
						$quick_actions['usage'] = '<a href="#modal-' . esc_attr( $_placement_slug ) . '-usage" class="usage-modal-link">' . esc_html__( 'show usage', 'advanced-ads' ) . '</a>';
					}

					ob_start();

					do_action( 'advanced-ads-placement-options-before-advanced', $_placement_slug, $_placement );

					if ( 'header' !== $_placement['type'] ) :
						$type_options = isset( $placement_types[ $_placement['type'] ]['options'] ) ? $placement_types[ $_placement['type'] ]['options'] : [];

						if ( ! isset( $type_options['placement-ad-label'] ) || $type_options['placement-ad-label'] ) {
							$_label    = isset( $_placement['options']['ad_label'] ) ? $_placement['options']['ad_label'] : 'default';
							$_position = ! empty( $_placement['options']['placement_position'] ) ? $_placement['options']['placement_position'] : 'default';
							$_clearfix = ! empty( $_placement['options']['placement_clearfix'] );

							ob_start();
							include ADVADS_ABSPATH . 'admin/views/placements-ad-label.php';
							$option_content = ob_get_clean();

							Advanced_Ads_Admin_Options::render_option(
								'placement-ad-label',
								__( 'ad label', 'advanced-ads' ),
								$option_content
							);
						}

						if ( ! empty( $placement_types[ $_placement['type'] ]['options']['show_position'] ) ) :
							ob_start();
							include ADVADS_ABSPATH . 'admin/views/placements-ad-label-position.php';
							$option_content = ob_get_clean();
							Advanced_Ads_Admin_Options::render_option(
								'placement-position',
								__( 'Position', 'advanced-ads' ),
								$option_content
							);
						endif;

						// Renders inline css option.
						ob_start();
						include ADVADS_ABSPATH . 'admin/views/placements-inline-css.php';
						$option_content = ob_get_clean();

						$inline_css     = $_placement['options']['inline-css'] ?? '';
						$placement_type = $_placement['type'] ?? '';
						$show_option    = ! ( 'custom_position' === $placement_type && empty( $inline_css ) );

						if ( $show_option ) {
							Advanced_Ads_Admin_Options::render_option(
								'placement-inline-css',
								__( 'Inline CSS', 'advanced-ads' ),
								$option_content
							);
						}

						// Show Pro features if Pro is not activated.
						if ( ! defined( 'AAP_VERSION' ) ) {
							// Minimum Content Length.
							Advanced_Ads_Admin_Options::render_option(
								'placement-content-minimum-length',
								__( 'Minimum Content Length', 'advanced-ads' ),
								'is_pro_pitch',
								__( 'Minimum length of content before automatically injected ads are allowed in them.', 'advanced-ads' )
							);

							// Words Between Ads.
							Advanced_Ads_Admin_Options::render_option(
								'placement-skip-paragraph',
								__( 'Words Between Ads', 'advanced-ads' ),
								'is_pro_pitch',
								__( 'A minimum amount of words between automatically injected ads.', 'advanced-ads' )
							);
						}
					endif;

					// show the conditions pitch on the `head` placement as well.
					if ( ! defined( 'AAP_VERSION' ) ) {
						// Display Conditions for placements.
						Advanced_Ads_Admin_Options::render_option(
							'placement-display-conditions',
							__( 'Display Conditions', 'advanced-ads' ),
							'is_pro_pitch',
							__( 'Use display conditions for placements.', 'advanced-ads' ) .
							' ' . __( 'The free version provides conditions on the ad edit page.', 'advanced-ads' )
						);

						// Visitor Condition for placements.
						Advanced_Ads_Admin_Options::render_option(
							'placement-visitor-conditions',
							__( 'Visitor Conditions', 'advanced-ads' ),
							'is_pro_pitch',
							__( 'Use visitor conditions for placements.', 'advanced-ads' ) .
							' ' . __( 'The free version provides conditions on the ad edit page.', 'advanced-ads' )
						);
					}

					do_action( 'advanced-ads-placement-options-after-advanced', $_placement_slug, $_placement );
					$advanced_options = ob_get_clean();
					?>

					<tr id="single-placement-<?php echo esc_attr( $_placement_slug ); ?>"
						class="advanced-ads-placement-row"
						<?php self::render_order_data( $placement_types, $_placement ); ?>>

						<td class="column-primary">
							<?php
							if ( $advanced_options ) {
								new Advanced_Ads_Modal(
									[
										'modal_slug'    => $_placement_slug,
										'modal_content' => $advanced_options,
										/* translators: 1: "Options", 2: the name of a placement. */
										'modal_title'   => sprintf( '%1$s: %2$s', __( 'Options', 'advanced-ads' ), $_placement['name'] ),
										'close_form'    => 'advanced-ads-placements-form',
										'close_action'  => __( 'Close and save', 'advanced-ads' ),
									]
								);
							}
							?>
							<?php if ( $type_missing ) :  // Type is not given. ?>
								<p class="advads-notice-inline advads-error">
									<?php
									printf(
										wp_kses(
										// Translators: %s is the name of a placement.
											__( 'Placement type "%s" is missing and was reset to "default".<br/>Please check if the responsible add-on is activated.', 'advanced-ads' ),
											[
												'br' => [],
											]
										),
										esc_html( $missed_type )
									);
									?>
								</p>
							<?php elseif ( isset( $_placement['type'] ) ) : ?>
								<div class="advads-form-type">
									<?php if ( isset( $placement_types[ $_placement['type'] ]['image'] ) ) : ?>
										<img src="<?php echo esc_url( $placement_types[ $_placement['type'] ]['image'] ); ?>" alt="<?php echo esc_attr( $placement_types[ $_placement['type'] ]['title'] ); ?>"/>
										<p class="advads-form-description">
											<strong><?php echo esc_html( $placement_types[ $_placement['type'] ]['title'] ); ?></strong>
										</p>
										<div class="advads-table-name">
											<a href="#modal-<?php echo esc_attr( $_placement_slug ); ?>" class="row-title" data-placement="<?php echo esc_attr( $_placement_slug ); ?>">
												<?php echo esc_html( $_placement['name'] ); ?>
											</a><br/>
										</div>
									<?php else : ?>
										<?php echo esc_html( $placement_types[ $_placement['type'] ]['title'] ); ?>
									<?php endif; ?>
								</div>
							<?php else : ?>
								<?php __( 'default', 'advanced-ads' ); ?>
							<?php endif; ?>
							<div class="row-actions">
								<span class="edit">
								<a href="#modal-<?php echo esc_attr( $_placement_slug ); ?>" class="" data-placement="<?php echo esc_attr( $_placement_slug ); ?>">
									<?php esc_html_e( 'Edit', 'advanced-ads' ); ?>
								</a> |
								</span>
								<?php $last_key = array_search( end( $quick_actions ), $quick_actions, true ); ?>
								<?php foreach ( $quick_actions as $quick_action => $action_link ) : ?>
									<span class='<?php echo esc_attr( $quick_action ); ?> '>
									<?php
									echo wp_kses(
										$action_link,
										[
											'a' => [
												'class' => [],
												'href'  => [],
												'style' => 'cursor: pointer',
											],
										]
									);
									?>
								</span>
									<?php if ( $quick_action !== $last_key ) : ?>
										<span class="separator"> | </span>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
							<button type="button" class="toggle-row"><span class="screen-reader-text">Mehr Details anzeigen</span></button>
							<input type="hidden" class="advads-placement-slug" value="<?php echo esc_attr( $_placement_slug ); ?>"/>
							<?php
							if ( 'default' === ( $_placement['type'] ?? '' ) ) {
								ob_start();
								?>
								<div class="advads-usage">
									<h2><?php esc_html_e( 'shortcode', 'advanced-ads' ); ?></h2>
									<code>
										<input type="text" onclick="this.select();" value='[the_ad_placement id="<?php echo esc_attr( $_placement_slug ); ?>"]' readonly/>
									</code>
									<h2><?php esc_html_e( 'template (PHP)', 'advanced-ads' ); ?></h2>
									<code>
										<input type="text" onclick="this.select();" value="if( function_exists('the_ad_placement') ) { the_ad_placement('<?php echo esc_attr( $_placement_slug ); ?>'); }" readonly/>
									</code>
								</div>
								<?php
								new Advanced_Ads_Modal(
									[
										'modal_slug'    => $_placement_slug . '-usage',
										'modal_content' => ob_get_clean(),
										'modal_title'   => __( 'Usage', 'advanced-ads' ),
									]
								);
							}
							?>
						</td>
						<td class="advads-placements-table-options">
							<?php do_action( 'advanced-ads-placement-options-before', $_placement_slug, $_placement ); ?>

							<?php
							Advanced_Ads_Admin_Options::render_option(
								'placement-item',
								/* translators: 1: "Ad", 2: "Group". */ // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
								sprintf(
									'%1$s / %2$s',
									__( 'Ad', 'advanced-ads' ),
									__( 'Group', 'advanced-ads' )
								),
								Advanced_Ads_Placements::get_items_for_placement_markup( $_placement_slug, $_placement )
							);

							switch ( $_placement['type'] ) :
								case 'post_content':
									$option_index = isset( $_placement['options']['index'] ) ? absint( max( 1, (int) $_placement['options']['index'] ) ) : 1;
									$option_tag   = $_placement['options']['tag'] ?? 'p';

									// Automatically select the 'custom' option.
									if ( ! empty( $_COOKIE['advads_frontend_picker'] ) ) {
										$option_tag = ( $_COOKIE['advads_frontend_picker'] === $_placement_slug ) ? 'custom' : $option_tag;
									}

									$option_xpath = isset( $_placement['options']['xpath'] ) ? stripslashes( $_placement['options']['xpath'] ) : '';
									$positions    = [
										'after'  => __( 'after', 'advanced-ads' ),
										'before' => __( 'before', 'advanced-ads' ),
									];
									ob_start();
									include ADVADS_ABSPATH . 'admin/views/placements-content-index.php';
									if ( ! defined( 'AAP_VERSION' ) ) {
										include ADVADS_ABSPATH . 'admin/views/upgrades/repeat-the-position.php';
									}

									do_action( 'advanced-ads-placement-post-content-position', $_placement_slug, $_placement );
									$option_content = ob_get_clean();

									Advanced_Ads_Admin_Options::render_option(
										'placement-content-injection-index',
										__( 'position', 'advanced-ads' ),
										$option_content
									);

									if ( ! extension_loaded( 'dom' ) ) :
										?>
										<p><span class="advads-notice-inline advads-error"><?php esc_html_e( 'Important Notice', 'advanced-ads' ); ?>: </span>
											<?php
											printf(
												// translators: %s is a list of PHP extensions.
												esc_html__( 'Missing PHP extensions could cause issues. Please ask your hosting provider to enable them: %s', 'advanced-ads' ),
												'dom (php_xml)'
											);
											?>
										</p>
									<?php endif; ?>
									<?php
									break;
							endswitch;
							do_action( 'advanced-ads-placement-options-after', $_placement_slug, $_placement );

							// Information after options.
							if ( isset( $_placement['type'] ) && 'header' === $_placement['type'] ) :
								?>
								<br/><p>
								<?php
								printf(
									wp_kses(
									// translators: %s is a URL.
										__( 'Tutorial: <a href="%s" target="_blank">How to place visible ads in the header of your website</a>.', 'advanced-ads' ),
										[
											'a' => [
												'href'   => [],
												'target' => [],
											],
										]
									),
									'https://wpadvancedads.com/place-ads-in-website-header/?utm_source=advanced-ads&utm_medium=link&utm_campaign=header-ad-tutorial'
								);
								?>
							</p>
							<?php endif; ?>
							<?php if ( $advanced_options ) : ?>
								<div class="advads-placements-show-options">
									<a href="#modal-<?php echo esc_attr( $_placement_slug ); ?>" data-placement="<?php echo esc_attr( $_placement_slug ); ?>"><?php esc_html_e( 'show all options', 'advanced-ads' ); ?></a>
								</div>
							<?php endif; ?>
						</td>
						<td class="advads-placement-conditions">
							<?php if ( ! empty( $_placement['options']['placement_conditions']['display'] ) ) : ?>
								<h4><?php echo esc_html__( 'Display Conditions', 'advanced-ads' ); ?></h4>
								<ul>
									<?php foreach ( $_placement['options']['placement_conditions']['display'] as $condition ) : ?>
										<?php if ( array_key_exists( $condition['type'], (array) $display_conditions->conditions ) ) : ?>
											<li>
												<?php echo esc_html( $display_conditions->conditions[ $condition['type'] ]['label'] ); ?>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php if ( ! empty( $_placement['options']['placement_conditions']['visitors'] ) ) : ?>
								<h4><?php echo esc_html__( 'Visitor Conditions', 'advanced-ads' ); ?></h4>
								<ul>
									<?php foreach ( $_placement['options']['placement_conditions']['visitors'] as $condition ) : ?>
										<?php if ( array_key_exists( $condition['type'], $visitor_conditions->conditions ) ) : ?>
											<li>
												<?php echo esc_html( $visitor_conditions->conditions[ $condition['type'] ]['label'] ); ?>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php if ( $advanced_options ) : ?>
								<a href="#modal-<?php echo esc_attr( $_placement_slug ); ?>" data-placement="<?php echo esc_attr( $_placement_slug ); ?>" class="advads-mobile-hidden"><?php esc_html_e( 'edit conditions', 'advanced-ads' ); ?></a>
							<?php endif; ?>
						</td>
						<?php do_action( 'advanced-ads-placements-list-column', $_placement_slug, $_placement ); ?>
						<td class="hidden">
							<input type="checkbox"
								id="advads-placements-item-delete-<?php echo esc_attr( $_placement_slug ); ?>"
								class="advads-placements-item-delete"
								name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][delete]"
								value="1"
							/>
							<label for="advads-placements-item-delete-<?php echo esc_attr( $_placement_slug ); ?>"><?php echo esc_html_x( 'delete', 'checkbox to remove placement', 'advanced-ads' ); ?></label>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<div class="tablenav bottom">
				<input type="submit" id="advads-save-placements-button" class="button button-primary" value="<?php esc_html_e( 'Save Placements', 'advanced-ads' ); ?>"/>
				<?php wp_nonce_field( 'advads-placement', 'advads_placement', true ); ?>
				<a href="#modal-placement-new" class="button" title="<?php esc_html_e( 'Create a new placement', 'advanced-ads' ); ?>"><?php esc_html_e( 'New Placement', 'advanced-ads' ); ?></a>
				<?php do_action( 'advanced-ads-placements-list-buttons', $placements ); ?>
			</div>
			<input type="hidden" name="advads-last-edited-placement" id="advads-last-edited-placement" value="0"/>
		</form>
		<?php
		include ADVADS_ABSPATH . 'admin/views/frontend-picker-script.php';
		do_action( 'advanced-ads-placements-list-after', $placements );

	else :
		?>
		<script>
			window.location.hash = '#modal-<?php echo esc_html( $modal_slug ); ?>';
		</script>
	<?php endif; ?>
</div>
