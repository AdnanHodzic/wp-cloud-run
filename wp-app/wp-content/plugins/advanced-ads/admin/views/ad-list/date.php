<?php
/**
 * Render the "Date" column date in the ad list.
 *
 * @var string $published_date ad published date.
 * @var string $modified_date ad modified date.
 */
?>
<div class="advads-ad-list-date">
    <?php if ( $modified_date === $published_date ) : ?>
        <?php echo __( 'Published', 'default' ); ?>
    <?php else: ?>
        <?php echo __( 'Last Modified', 'default' ); ?>
    <?php endif; ?>
    <br/>
    <?php echo esc_html( $modified_date ); ?>
</div>
