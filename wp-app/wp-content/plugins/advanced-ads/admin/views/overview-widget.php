<div id="<?php echo esc_attr( $id ); ?>" class="postbox position-<?php echo esc_attr( $position ); ?>">
	<?php if ( ! empty( $title ) ) : ?>
		<h2>
			<?php
			// phpcs:ignore
			echo $title;
			?>
		</h2>
	<?php endif; ?>
	<div class="inside">
		<?php
		// phpcs:ignore
		echo $content;
		?>
	</div>
</div>
