<?php
/**
 * Render the view navigation items on the ad list.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var array $views_new                list of views.
 * @var bool  $show_trash_delete_button if the trash delete button is visible.
 */

use AdvancedAds\Framework\Utilities\Str;

?>
<ul class="advanced-ads-ad-list-views">
	<?php
	foreach ( $views as $view ) :
			$view  = str_replace( [ ')', '(' ], '', $view );
			$class = Str::contains( 'current', $view ) ? 'advads-button-primary' : 'advads-button-secondary';
		?>
		<li class="button <?php echo esc_attr( $class ); ?>">
			<?php
			echo wp_kses(
				$view,
				[
					'a'    => [ 'href' => [] ],
					'span' => [ 'class' => [] ],
				]
			);
			?>
		</li>
	<?php endforeach; ?>
</ul>
<?php if ( $show_trash_delete_button ) : ?>
	<button type="submit" name="delete_all" id="delete_all" class="button advads-button-primary">
		<span class="dashicons dashicons-trash"></span><?php esc_html_e( 'Empty Trash', 'advanced-ads' ); ?>
	</button>
	<?php
endif;

