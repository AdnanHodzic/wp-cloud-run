<?php
/**
 * @var $post
 */

$exclude   = get_post_meta( $post->ID, '_sitemap_exclude', true );
$priority  = get_post_meta( $post->ID, '_sitemap_priority', true );
$frequency = get_post_meta( $post->ID, '_sitemap_frequency', true );

wp_enqueue_style( 'sgg-meta-box', GRIM_SG_URL . 'assets/css/meta-box.min.css', array(), GRIM_SG_VERSION );

wp_nonce_field( 'sgg_pro_meta_box', 'sgg_pro_meta_box_nonce' );
?>
<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
	<p><?php esc_html_e( 'Custom Sitemap Options for the Current Post such as Exclude from Sitemap, Post Priority, Post Frequency.', 'xml-sitemap-generator-for-google' ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<tr>
			<td>
				<label for="_sitemap_exclude"><?php esc_html_e( 'Exclude from Sitemap', 'xml-sitemap-generator-for-google' ); ?></label>
			</td>
			<td>
				<input type="checkbox" name="_sitemap_exclude" id="_sitemap_exclude" value="1" <?php checked( $exclude, '1' ); ?> <?php disabled( ! sgg_pro_enabled() ); ?> />
			</td>
		</tr>
		<tr>
			<td>
				<label for="_sitemap_priority"><?php esc_html_e( 'Post Priority', 'xml-sitemap-generator-for-google' ); ?></label>
			</td>
			<td>
				<select name="_sitemap_priority" id="_sitemap_priority" <?php disabled( ! sgg_pro_enabled() ); ?>>
					<option value=""><?php esc_html_e( 'Default', 'xml-sitemap-generator-for-google' ); ?></option>
					<option value="0" <?php selected( $priority, '0' ); ?>>0.0</option>
					<option value="1" <?php selected( $priority, '1' ); ?>>0.1</option>
					<option value="2" <?php selected( $priority, '2' ); ?>>0.2</option>
					<option value="3" <?php selected( $priority, '3' ); ?>>0.3</option>
					<option value="4" <?php selected( $priority, '4' ); ?>>0.4</option>
					<option value="5" <?php selected( $priority, '5' ); ?>>0.5</option>
					<option value="6" <?php selected( $priority, '6' ); ?>>0.6</option>
					<option value="7" <?php selected( $priority, '7' ); ?>>0.7</option>
					<option value="8" <?php selected( $priority, '8' ); ?>>0.8</option>
					<option value="9" <?php selected( $priority, '9' ); ?>>0.9</option>
					<option value="10" <?php selected( $priority, '10' ); ?>>1.0</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="_sitemap_frequency"><?php esc_html_e( 'Post Frequency', 'xml-sitemap-generator-for-google' ); ?></label>
			</td>
			<td>
				<select name="_sitemap_frequency" id="_sitemap_frequency" <?php disabled( ! sgg_pro_enabled() ); ?>>
					<option value=""><?php esc_html_e( 'Default', 'xml-sitemap-generator-for-google' ); ?></option>
					<option value="always" <?php selected( $frequency, 'always' ); ?>><?php esc_html_e( 'Always', 'xml-sitemap-generator-for-google' ); ?></option>
					<option value="hourly" <?php selected( $frequency, 'hourly' ); ?>><?php esc_html_e( 'Hourly', 'xml-sitemap-generator-for-google' ); ?></option>
					<option value="daily" <?php selected( $frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'xml-sitemap-generator-for-google' ); ?></option>
					<option value="weekly" <?php selected( $frequency, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'xml-sitemap-generator-for-google' ); ?></option>
					<option value="monthly" <?php selected( $frequency, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'xml-sitemap-generator-for-google' ); ?></option>
					<option value="yearly" <?php selected( $frequency, 'yearly' ); ?>><?php esc_html_e( 'Yearly', 'xml-sitemap-generator-for-google' ); ?></option>
					<option value="never" <?php selected( $frequency, 'never' ); ?>><?php esc_html_e( 'Never', 'xml-sitemap-generator-for-google' ); ?></option>
				</select>
			</td>
		</tr>
	</table>

	<?php sgg_show_pro_overlay( array( 'utm' => 'meta-box' ) ); ?>
</div>
