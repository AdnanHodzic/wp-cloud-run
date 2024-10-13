<?php
/**
 * Display message indicating that ads are disabled for a specific post type.
 * Get the labels for the specific post type
 * @var object $labels to render setting hint form
 */
?>
<p><?php
    printf(
        /* translators: %s post type plural name */
        esc_html__( 'Ads are disabled for all %s', 'advanced-ads' ),
        $labels->name
    );
?></p>
