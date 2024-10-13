<?php
/**
 * Render a row with add-on information on the Advanced Ads overview page
 *
 * @var array $_addon add-on information.
 */
?>
<tr<?php echo isset( $_addon['class'] ) ? ' class="' . esc_attr( $_addon['class'] ) . '"' : ''; ?>><th>
			  <?php
		// phpcs:ignore
		echo $_addon['title'];
				?>
		</th>
	<td>
	<?php
		// phpcs:ignore
		echo $_addon['desc'];
	?>
		</td>
	<td><?php if ( isset( $_addon['link'] ) && $_addon['link'] ) : ?>
	<a class="button <?php echo ( isset( $_addon['link_primary'] ) ) ? 'button-primary' : 'button-secondary'; ?>" href="<?php echo esc_url( $_addon['link'] ); ?>" target="_blank">
								<?php
								echo esc_html( $link_title );
								?>
	</a>
		<?php
	endif;
		?>
	</td>
</tr>
