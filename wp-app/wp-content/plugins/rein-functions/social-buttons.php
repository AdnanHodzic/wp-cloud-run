<?php
function epcl_render_share_buttons( $position = '' ){
    global $post;
    
    $epcl_theme = epcl_get_theme_options();
    $button_class = '';
    $wrapper_class = 'epcl-share';
    if( $position == 'bottom' ){
        $wrapper_class = 'epcl-share-bottom';
        $button_class = 'button circle';
    }
    ?>
    <div class="<?php echo esc_attr($wrapper_class); ?>">
        <?php
        $share_summary = get_the_excerpt();
        $share_url = wp_get_shortlink();
        $share_title = html_entity_decode(get_the_title(), ENT_COMPAT, 'UTF-8');
        $share_media = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'large' );
        ?>
        <?php if( isset( $epcl_theme['enable_single_facebook'] ) && $epcl_theme['enable_single_facebook'] !== '0'): ?>
            <a class="facebook <?php echo esc_attr($button_class); ?>" rel="nofollow noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url($share_url); ?>"
    onclick="window.open(this.href, 'facebook-share','width=580,height=296');return false;"><i class="fa fa-facebook"></i></a>
        <?php endif; ?>
        <?php if( isset( $epcl_theme['enable_single_twitter'] ) && $epcl_theme['enable_single_twitter'] !== '0'): ?>    
        <a class="twitter <?php echo esc_attr($button_class); ?>" rel="nofollow noopener"  href="http://twitter.com/share?text=<?php echo urlencode($share_title); ?>&url=<?php echo esc_url($share_url); ?>"
    onclick="window.open(this.href, 'twitter-share', 'width=550,height=235');return false;"><i class="fa fa-twitter"></i></a>
        <?php endif; ?>
        <?php if( isset( $epcl_theme['enable_single_linkedin'] ) && $epcl_theme['enable_single_linkedin'] == '1'): ?>    
            <a class="linkedin <?php echo esc_attr($button_class); ?>" rel="nofollow noopener"  href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url($share_url); ?>&title=<?php echo urlencode($share_title); ?>&summary=<?php echo urlencode($share_summary); ?>" onclick="window.open(this.href, 'linkedin-share', 'width=490,height=530');return false;"><i class="fa fa-linkedin"></i></a>
        <?php endif; ?>
        <?php if( isset( $epcl_theme['enable_single_pinterest'] ) && $epcl_theme['enable_single_pinterest'] == '1'): ?>    
            <a class="pinterest <?php echo esc_attr($button_class); ?>" rel="nofollow noopener"  href="//pinterest.com/pin/create/link/?url=<?php echo esc_url($share_url); ?>&media=<?php echo esc_url($share_media); ?>&description=<?php echo urlencode($share_title); ?>" onclick="window.open(this.href, 'pinterest-share', 'width=490,height=530');return false;"><i class="fa fa-pinterest"></i></a>
        <?php endif; ?>
        <?php if( isset( $epcl_theme['enable_single_telegram'] ) && $epcl_theme['enable_single_telegram'] == '1'): ?>    
            <a class="telegram <?php echo esc_attr($button_class); ?>" rel="nofollow noopener"  href="https://telegram.me/share/url?url=<?php echo esc_url($share_url); ?>&text=<?php echo urlencode($share_title); ?>" onclick="window.open(this.href, 'telegram-share', 'width=490,height=530');return false;"><i class="fa fa-telegram"></i></a>
        <?php endif; ?>
        <?php if( isset( $epcl_theme['enable_single_vk'] ) && $epcl_theme['enable_single_vk'] == '1'): ?>    
            <a class="vk <?php echo esc_attr($button_class); ?>" rel="nofollow noopener"  href="http://vk.com/share.php?url=<?php echo esc_url($share_url); ?>&title=<?php echo urlencode($share_title); ?>&comment=<?php echo urlencode($share_summary); ?>" onclick="window.open(this.href, 'vk-share', 'width=490,height=530');return false;"><i class="fa fa-vk"></i></a>
        <?php endif; ?>
        <?php if( isset( $epcl_theme['enable_single_email'] ) && $epcl_theme['enable_single_email'] !== '0'): ?>    
            <a class="email <?php echo esc_attr($button_class); ?>" rel="nofollow noopener"  href="mailto:?subject=<?php echo urlencode($share_title); ?>&body=<?php echo esc_url($share_url); ?>" target="_blank"><i class="fa fa-envelope"></i></a>
        <?php endif; ?>
        <?php if( isset( $epcl_theme['enable_single_whatsapp'] ) && $epcl_theme['enable_single_whatsapp'] !== '0'): ?>    
            <a class="whatsapp <?php echo esc_attr($button_class); ?>" rel="nofollow noopener"  href="https://api.whatsapp.com/send?text=<?php echo esc_url($share_url); ?>" data-action="share/whatsapp/share"><i class="fa fa-whatsapp"></i></a>
        <?php endif; ?>
    </div>
    <?php
}

function epcl_render_header_social_buttons(){
    $epcl_theme = epcl_get_theme_options();
    if(empty( $epcl_theme) ) return; 

    $container_class = 'share-buttons';
    $button_class = '';

    if( isset($_GET['header']) ){
        $epcl_theme['header_type'] = $_GET['header'];
    }
    
    if( $epcl_theme['header_type'] == 'classic' ){
        $container_class = 'epcl-social-buttons';
        $button_class = 'button circle';
    }
    ?>

    <div class="<?php echo esc_attr($container_class); ?> hide-on-tablet hide-on-mobile hide-on-desktop-sm">
        <?php if( epcl_get_option('facebook_url') ): ?>
            <a href="<?php echo esc_url( epcl_get_option('facebook_url') ); ?>" class="<?php echo esc_attr($button_class); ?> facebook" target="_blank" aria-label="Facebook" rel="nofollow noopener"><i class="fa fa-facebook"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('twitter_url') ): ?>
            <a href="<?php echo epcl_get_option('twitter_url'); ?>" class="<?php echo esc_attr($button_class); ?> twitter" target="_blank" aria-label="Twitter" rel="nofollow noopener"><i class="fa fa-twitter"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('linkedin_url') ): ?>
            <a href="<?php echo epcl_get_option('linkedin_url'); ?>" class="<?php echo esc_attr($button_class); ?> linkedin" target="_blank" aria-label="Linkedin" rel="nofollow noopener"><i class="fa fa-linkedin"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('instagram_url') ): ?>
            <a href="<?php echo epcl_get_option('instagram_url'); ?>" class="<?php echo esc_attr($button_class); ?> instagram" target="_blank" aria-label="Instagram" rel="nofollow noopener"><i class="fa fa-instagram"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('pinterest_url') ): ?>
            <a href="<?php echo epcl_get_option('pinterest_url'); ?>" class="<?php echo esc_attr($button_class); ?> pinterest" target="_blank" aria-label="Pinterest" rel="nofollow noopener"><i class="fa fa-pinterest"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('dribbble_url') ): ?>
            <a href="<?php echo epcl_get_option('dribbble_url'); ?>" class="<?php echo esc_attr($button_class); ?> dribbble" target="_blank" aria-label="Dribbble" rel="nofollow noopener"><i class="fa fa-dribbble"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('tumblr_url') ): ?>
            <a href="<?php echo epcl_get_option('tumblr_url'); ?>" class="<?php echo esc_attr($button_class); ?> tumblr" target="_blank" aria-label="Tumblr" rel="nofollow noopener"><i class="fa fa-tumblr"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('youtube_url') ): ?>
            <a href="<?php echo epcl_get_option('youtube_url'); ?>" class="<?php echo esc_attr($button_class); ?> youtube" target="_blank" aria-label="Youtube" rel="nofollow noopener"><i class="fa fa-youtube"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('flickr_url') ): ?>
            <a href="<?php echo epcl_get_option('flickr_url'); ?>" class="<?php echo esc_attr($button_class); ?> flickr" target="_blank" aria-label="Flickr" rel="nofollow noopener"><i class="fa fa-flickr"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('twitch_url') ): ?>
            <a href="<?php echo epcl_get_option('twitch_url'); ?>" class="<?php echo esc_attr($button_class); ?> twitch" target="_blank" aria-label="Twitch" rel="nofollow noopener"><i class="fa fa-twitch"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('vk_url') ): ?>
            <a href="<?php echo epcl_get_option('vk_url'); ?>" class="<?php echo esc_attr($button_class); ?> vk" target="_blank" aria-label="Vkontakte" rel="nofollow noopener"><i class="fa fa-vk"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('telegram_url') ): ?>
            <a href="<?php echo epcl_get_option('telegram_url'); ?>" class="<?php echo esc_attr($button_class); ?> telegram" target="_blank" aria-label="Telegram" rel="nofollow noopener"><i class="fa fa-telegram"></i></a>
        <?php endif; ?>
        <?php if( epcl_get_option('discord_url') ): ?>
            <a href="<?php echo epcl_get_option('discord_url'); ?>" class="<?php echo esc_attr($button_class); ?> discord" target="_blank" aria-label="Discord" rel="nofollow noopener"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.3034 5.33716C17.9344 4.71103 16.4805 4.2547 14.9629 4C14.7719 4.32899 14.5596 4.77471 14.411 5.12492C12.7969 4.89144 11.1944 4.89144 9.60255 5.12492C9.45397 4.77471 9.2311 4.32899 9.05068 4C7.52251 4.2547 6.06861 4.71103 4.70915 5.33716C1.96053 9.39111 1.21766 13.3495 1.5891 17.2549C3.41443 18.5815 5.17612 19.388 6.90701 19.9187C7.33151 19.3456 7.71356 18.73 8.04255 18.0827C7.41641 17.8492 6.82211 17.5627 6.24904 17.2231C6.39762 17.117 6.5462 17.0003 6.68416 16.8835C10.1438 18.4648 13.8911 18.4648 17.3082 16.8835C17.4568 17.0003 17.5948 17.117 17.7434 17.2231C17.1703 17.5627 16.576 17.8492 15.9499 18.0827C16.2789 18.73 16.6609 19.3456 17.0854 19.9187C18.8152 19.388 20.5875 18.5815 22.4033 17.2549C22.8596 12.7341 21.6806 8.80747 19.3034 5.33716ZM8.5201 14.8459C7.48007 14.8459 6.63107 13.9014 6.63107 12.7447C6.63107 11.5879 7.45884 10.6434 8.5201 10.6434C9.57071 10.6434 10.4303 11.5879 10.4091 12.7447C10.4091 13.9014 9.57071 14.8459 8.5201 14.8459ZM15.4936 14.8459C14.4535 14.8459 13.6034 13.9014 13.6034 12.7447C13.6034 11.5879 14.4323 10.6434 15.4936 10.6434C16.5442 10.6434 17.4038 11.5879 17.3825 12.7447C17.3825 13.9014 16.5548 14.8459 15.4936 14.8459Z"></path></svg></span></a>
        <?php endif; ?>
        <?php if( epcl_get_option('tiktok_url') ): ?>
            <a href="<?php echo epcl_get_option('tiktok_url'); ?>" class="<?php echo esc_attr($button_class); ?> tiktok" target="_blank" aria-label="TikTok" rel="nofollow noopener"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8.24537V15.5C16 19.0899 13.0899 22 9.5 22C5.91015 22 3 19.0899 3 15.5C3 11.9101 5.91015 9 9.5 9C10.0163 9 10.5185 9.06019 11 9.17393V12.3368C10.5454 12.1208 10.0368 12 9.5 12C7.567 12 6 13.567 6 15.5C6 17.433 7.567 19 9.5 19C11.433 19 13 17.433 13 15.5V2H16C16 4.76142 18.2386 7 21 7V10C19.1081 10 17.3696 9.34328 16 8.24537Z"></path></svg></span></a>
        <?php endif; ?>
        <?php if( epcl_get_option('rss_url') ): ?>
            <a href="<?php echo epcl_get_option('rss_url'); ?>" class="<?php echo esc_attr($button_class); ?> rss" target="_blank" aria-label="RSS" rel="nofollow noopener"><i class="fa fa-rss"></i></a>
        <?php endif; ?>
    </div>
<?php
}

function epcl_render_theme_author( $class = ''){
    return '<p class="published underline-effect '.esc_attr($class).'"><a href="https://estudiopatagon.com/projects/rein-for-wordpress/?ref=rein-wp-footer" target="_blank">Rein</a> Theme by <a href="https://estudiopatagon.com/?ref=rein-wp-footer" target="_blank">EstudioPatagon</a></p>';
}
?>
