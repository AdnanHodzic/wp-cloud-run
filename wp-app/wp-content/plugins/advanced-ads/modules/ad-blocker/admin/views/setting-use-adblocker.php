<?php
/**
 * The view to render the option.
 *
 * @var boolean $checked      True, when the option is checked.
 * @var boolean $is_main_site True, when the site is the main site of the current network.
 */
?>
<label>
	<?php if ( $is_main_site ) : ?>
		<input id="advanced-ads-use-adblocker" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[use-adblocker]" <?php checked( $checked, 1, true ); ?>>
	<?php else : ?>
		<?php esc_html_e( 'The ad block disguise can only be set by the super admin on the main site in the network.', 'advanced-ads' ); ?>
	<?php endif ?>
	<?php esc_html_e( 'Prevents ad block software from breaking your website when blocking asset files (.js, .css).', 'advanced-ads' ); ?>
	<?php if ( ! defined( 'AAP_VERSION' ) ) : ?>
		<p>
			<?php
			printf(
				wp_kses(
				// translators: %s is a URL.
					__( 'Learn how to display alternative content to ad block users <a href="%s" target="_blank">in the manual</a>.', 'advanced-ads' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					]
				),
				'https://wpadvancedads.com/manual/ad-blockers/#utm_source=advanced-ads&utm_medium=link&utm_campaign=adblock-manual'
			);
			?>
		</p>
	<?php endif; ?>
</label>
