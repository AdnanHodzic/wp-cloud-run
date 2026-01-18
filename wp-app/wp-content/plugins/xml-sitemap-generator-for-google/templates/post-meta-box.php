<?php
/**
 * @var $post
 */

$exclude   = get_post_meta( $post->ID, '_sitemap_exclude', true );
$priority  = get_post_meta( $post->ID, '_sitemap_priority', true );
$frequency = get_post_meta( $post->ID, '_sitemap_frequency', true );

$frequency = isset( $frequency ) ? (string) $frequency : '';
$priority  = isset( $priority ) ? (string) $priority : '';

$frequency_options = array(
	''         => __( 'Default', 'xml-sitemap-generator-for-google' ),
	'always'   => __( 'Always', 'xml-sitemap-generator-for-google' ),
	'hourly'   => __( 'Hourly', 'xml-sitemap-generator-for-google' ),
	'daily'    => __( 'Daily', 'xml-sitemap-generator-for-google' ),
	'weekly'   => __( 'Weekly', 'xml-sitemap-generator-for-google' ),
	'monthly'  => __( 'Monthly', 'xml-sitemap-generator-for-google' ),
	'yearly'   => __( 'Yearly', 'xml-sitemap-generator-for-google' ),
	'never'    => __( 'Never', 'xml-sitemap-generator-for-google' ),
);


wp_enqueue_style( 'sgg-meta-box', GRIM_SG_URL . 'assets/css/meta-box.min.css', array(), GRIM_SG_VERSION );
wp_enqueue_style( 'sgg-icons', GRIM_SG_URL . 'assets/fonts/icons/style.css', array(), GRIM_SG_VERSION );
wp_enqueue_script( 'sgg-scripts', GRIM_SG_URL . 'assets/js/scripts.js', array( 'jquery' ), GRIM_SG_VERSION, true );

wp_nonce_field( 'sgg_pro_meta_box', 'sgg_pro_meta_box_nonce' );
?>
<div class="grim-section grim-section-post-meta pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<p class="grim-section-desc"><?php esc_html_e( 'Custom Sitemap Options for the Current Post such as Exclude from Sitemap, Post Priority, Post Frequency.', 'xml-sitemap-generator-for-google' ); ?></p>

	<table class="grim-table wp-list-table widefat fixed striped">
		<?php if ( ! apply_filters( 'xml_sitemap_disable_post_meta__exclude_sitemap', false ) ) { ?>
			<tr>
				<td>
					<label for="_sitemap_exclude"><?php esc_html_e( 'Exclude from Sitemap', 'xml-sitemap-generator-for-google' ); ?></label>
				</td>
				<td>
					<label for="_sitemap_exclude">
						<input class="grim-default-checkbox" type="checkbox" name="_sitemap_exclude" id="_sitemap_exclude" value="1" <?php checked( $exclude, '1' ); ?> <?php disabled( ! sgg_pro_enabled() ); ?> />
					</label>
				</td>
			</tr>
		<?php } ?>
		<?php if ( ! apply_filters( 'xml_sitemap_disable_post_meta__sitemap_priority', false ) ) { ?>
			<tr>
				<td>
					<label for="_sitemap_priority"><?php esc_html_e( 'Post Priority', 'xml-sitemap-generator-for-google' ); ?></label>
				</td>
				<td>
					<div class="grim-select grim-select-default"
						 data-name="_sitemap_priority">
						<div class="grim-select__trigger">
							<span>
								<?php
								if ( $priority === '' ) {
									esc_html_e( 'Default', 'xml-sitemap-generator-for-google' );
								} else {
									echo esc_html( number_format( (int) $priority / 10, 1 ) );
								}
								?>
							</span>
							<i class="grim-icon-chevron-down"></i>
						</div>

						<div class="grim-options">
							<div class="grim-option <?php echo $priority === '' ? 'selected' : ''; ?>" data-value="">
								<?php esc_html_e( 'Default', 'xml-sitemap-generator-for-google' ); ?>
							</div>

							<?php for ( $i = 0; $i <= 10; $i++ ) :
								$label = number_format( $i / 10, 1 );
								?>
								<div class="grim-option <?php echo (string) $priority === (string) $i ? 'selected' : ''; ?>"
									 data-value="<?php echo esc_attr( $i ); ?>">
									<?php echo esc_html( $label ); ?>
								</div>
							<?php endfor; ?>
						</div>
					</div>

					<select id="_sitemap_priority"
							name="_sitemap_priority"
							class="grim-hidden-select"
							hidden>
						<option value="" <?php selected( $priority, '' ); ?>>
							<?php esc_html_e( 'Default', 'xml-sitemap-generator-for-google' ); ?>
						</option>

						<?php for ( $i = 0; $i <= 10; $i++ ) :
							$label = number_format( $i / 10, 1 );
							?>
							<option value="<?php echo esc_attr( $i ); ?>" <?php selected( (string) $priority, (string) $i ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
		<?php } ?>
		<?php if ( ! apply_filters( 'xml_sitemap_disable_post_meta__sitemap_frequency', false ) ) { ?>
			<tr>
				<td>
					<label for="_sitemap_frequency"><?php esc_html_e( 'Post Frequency', 'xml-sitemap-generator-for-google' ); ?></label>
				</td>
				<td>
					<div class="grim-select grim-select-default"
						 data-name="_sitemap_frequency">
						<div class="grim-select__trigger">
							<span>
								<?php echo esc_html( $frequency_options[ $frequency ] ?? __( 'Default', 'xml-sitemap-generator-for-google' ) ); ?>
							</span>
							<i class="grim-icon-chevron-down"></i>
						</div>

						<div class="grim-options">
							<?php foreach ( $frequency_options as $opt_value => $label ) : ?>
								<div class="grim-option <?php echo (string) $frequency === (string) $opt_value ? 'selected' : ''; ?>"
									 data-value="<?php echo esc_attr( $opt_value ); ?>">
									<?php echo esc_html( $label ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<select id="_sitemap_frequency"
							name="_sitemap_frequency"
							class="grim-hidden-select"
							hidden>
						<?php foreach ( $frequency_options as $opt_value => $label ) : ?>
							<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( (string) $frequency, (string) $opt_value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		<?php } ?>
	</table>

	<?php sgg_show_pro_overlay( array( 'utm' => 'meta-box' ) ); ?>
</div>
