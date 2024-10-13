<fieldset>
    <label>
	    <input type="radio" value="on" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[content-injection-enabled]" <?php echo ( $enabled === 'on' || ( $enabled === '' && ! empty( $everywhere ) ) ) ? 'checked' : ''; ?> onclick="toggle_visibility(this, '[name=\'<?php echo esc_attr( ADVADS_SLUG ); ?>[content-injection-everywhere]\']');" />
        on
    </label>
    <label>
	    <input type="radio" value="off" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[content-injection-enabled]"  <?php echo ( $enabled === 'off' || $everywhere === 0 ) ? 'checked' : ''; ?>  onclick="toggle_visibility(this, '[name=\'<?php echo esc_attr( ADVADS_SLUG ); ?>[content-injection-everywhere]\']');" />
        off
    </label>
</fieldset>
<br/>
<select name="<?php echo esc_attr( ADVADS_SLUG ); ?>[content-injection-everywhere]" style="min-width: 85px; display:<?php echo ( $enabled === 'off' || $everywhere === 0 ) ? 'none' : 'block'; ?>" >
        <option value="1" <?php selected( $everywhere, 1 ); ?>>1</option>
        <option value="2" <?php selected( $everywhere, 2 ); ?>>2</option>
        <option value="3" <?php selected( $everywhere, 3 ); ?>>3</option>
        <option value="4" <?php selected( $everywhere, 4 ); ?>>4</option>
        <option value="5" <?php selected( $everywhere, 5 ); ?>>5</option>
        <option value="6" <?php selected( $everywhere, 6 ); ?>>6</option>
        <option value="7" <?php selected( $everywhere, 7 ); ?>>7</option>
        <option value="8" <?php selected( $everywhere, 8 ); ?>>8</option>
        <option value="9" <?php selected( $everywhere, 9 ); ?>>9</option>
        <option value="10" <?php selected( $everywhere, 10 ); ?>>10</option>
        <option value="-1" <?php echo ( $everywhere === -1 || $everywhere > 10 ) ? 'selected' : ''; ?>>all</option>
</select>
<p class="description">
    <?php
    printf(
        wp_kses(
            // translators: %s is a URL.
            __( 'To ensure compatibility, ads are not injected into excerpts or the full content of posts on archive pages. However, by enabling this option, you can override this restriction and set a limit on the number of posts where ads will be injected. Please note that if you want to insert ads between post listing items on archive pages, you can utilize the Post list placement (<a href="%s" target="_blank">manual</a>) feature.', 'advanced-ads' ),
            [
                'a' => [
                    'href' => [],
                    'target' => [],
                ],
            ]
        ),
        esc_url('https://wpadvancedads.com/manual/placement-post-lists/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-content-injection')
    );
    ?>
</p>
