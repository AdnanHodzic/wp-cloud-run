<?php
if(!defined('ABSPATH')){ exit; }

function epcl_welcome_page() {
    $lb_update_data = null;
    $theme = wp_get_theme( EPCL_THEMESLUG );
    $base_config = array(
        'theme_name' => $theme->name,
        'theme_version' => $theme->version,
        'subscribe_url' => 'https://estudiopatagon.com/subscribe/?source=wp-plugin&aca=rein-welcome',
        'docs_url' => 'https://themes.estudiopatagon.com/docs/'.EPCL_THEMESLUG.'-wp/',
        'support_url' => 'https://estudiopatagon.ticksy.com/',
        'customizer_url' => admin_url( 'admin.php?page=epcl-theme-options' ),
        'php_max_input_vars' => 'https://estudiopatagon.ticksy.com/article/15543',
        'php_max_execution_time' => 'https://estudiopatagon.ticksy.com/article/15544',
        'php_max_memory' => 'https://estudiopatagon.ticksy.com/article/15545'
    );
    $lbapi = new LicenseBoxAPI();
    $license_file = get_option( EPCL_THEMESLUG . '_license_key_file');
    if( !$lbapi->check_local_license_exist() && $license_file != '' ){
        $lbapi->create_license( false, false, $license_file );  
    }
    $lb_verify_res = $lbapi->verify_license();

    // delete_transient('licensebox_next_update_check');
    if(false === ($lb_update_res = get_transient('licensebox_next_update_check'))){
        $lb_update_data = $lbapi->check_update();
        set_transient('licensebox_next_update_check', $lb_update_data, 24*HOUR_IN_SECONDS);
    }
    $lb_update_data = get_transient('licensebox_next_update_check');

    ?>

    <div class="wrap about-wrap vlt-dashboard">

        <h1>Welcome to <?php echo $base_config['theme_name'].' v'.$base_config['theme_version']; ?></h1>
        <div class="about-text">Thanks for purchasing <strong><?php echo $base_config['theme_name']; ?></strong>. If you like our theme/support or just believe you have an amazing product <a href="https://themeforest.net/downloads" target="_blank"><strong>don't forget to rate it 5 stars</strong></a>, that will help us to keep improving and adding more exciting features.</div>
        <div class="wp-badge"></div>

        <div class="epcl-dashboard-buttons">
            <a href="<?php echo $base_config['subscribe_url']; ?>" target="_blank" class="button button-primary">Subscribe!</a>
            <a href="<?php echo $base_config['docs_url']; ?>" target="_blank" class="button button-secondary">Theme Documentation</a>
            <a href="<?php echo $base_config['customizer_url']; ?>" target="" class="button button-secondary">Start Customizing</a>
            <a href="<?php echo $base_config['support_url']; ?>" target="_blank" class="button button-secondary">Get Support</a>
            <a href="https://1.envato.market/ep-portfolio-themes" target="_blank" class="button button-primary">Our ThemeForest Portfolio</a>
        </div>

        <div class="clear"></div>

        <div class="about-text">
            <p class="description">Thank you for choosing <strong><?php echo $base_config['theme_name']; ?>!</strong> Powered by <a href="https://1.envato.market/ep-portfolio-themes" target="_blank">EstudioPatagon</a></p>
        </div>

        <!-- start: .epcl-system -->
        <div class="epcl-system">
            <h3>License Status:
                <?php
                if ( !$lb_verify_res['status'] ) {
                    echo '<mark class="error"><small>Not activated</small></mark>';
                } else {
                    echo '<mark class="green">Valid</mark>';
                }
                ?>                
            </h3>
            
            <table class="system-status-table widefat">
                <tbody>
                    <tr>
                        <td>Automatic Updates</td>
                        <td>
                        <?php
                            if ( !$lb_verify_res['status'] ) {
                                echo '<mark class="error">License is not activated yet.</mark><br><a href="'.admin_url( 'admin.php?page=estudiopatagon-license' ).'">Activate your license here.</a>';
                            } else {
                                echo '<mark class="green">Enabled</mark>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Theme Version</td>
                        <td>
                            <?php
                            if ( isset( $lb_update_data['version']) && version_compare( $theme->version, $lb_update_data['version'], '<' ) ) {
                                echo '<mark class="error">'. $theme->version .'</mark>';
                            } else {
                                echo '<mark class="green">' . $theme->version . '</mark>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Available update</td>
                        <td>
                        <?php
                        if ( !$lb_verify_res['status'] ) {
                            echo '<mark class="error">License is not activated yet.</mark><br><a href="'.admin_url( 'admin.php?page=estudiopatagon-license' ).'">Activate your license here.</a>';
                        } else {
                            if ( !isset( $lb_update_data['version'] ) ) {
                                echo '<mark class="green">Your theme is up to date.</mark>';
                            } else {
                                echo '<mark class="green">' . esc_html($lb_update_data['version'])  . '</mark>';
                            }
                        }
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Changelog</td>
                        <td><a href="http://updates.estudiopatagon.com/changelog/<?php echo EPCL_THEMESLUG; ?>" target="_blank" class="button">View All Version History</a></td>
                    </tr>
                    <?php if( !empty( $lb_update_data['changelog'] ) ): ?>
                        <tr>
                            <td>Release Date</td>
                            <td><?php echo date('F j, Y', strtotime($lb_update_data['release_date']) ); ; ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="changelog">
                                <h4>Changelog:</h4>
                                <?php
                                $changelog_data = strip_tags($lb_update_data['changelog'], '<ol><ul><li><i><b><strong><p><br><a><blockquote>');
                                $changelog_data = str_replace('- Fixed:', '<span class="fix tag">Fixed:</span>', $changelog_data);
                                $changelog_data = str_replace('- Added:', '<span class="add tag">Added:</span>', $changelog_data);
                                $changelog_data = str_replace('* New Feature:', '<span class="add tag">Added:</span>', $changelog_data);
                                $changelog_data = str_replace( array('- Improved:', '- Improved:'), '<span class="improve tag">Improved:</span>', $changelog_data);
                                $changelog_data = str_replace( array('- Speed Improvement:', '- Speed Improvement:'), '<span class="improve tag">Improved:</span>', $changelog_data);
                                ?>
                                <?php echo $changelog_data; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>		
            </table>
            <?php if( defined('EPCL_ITEM_ID') ): ?>
                <p style="margin-bottom:0"><small>
                    You can download your <b>Purchase Code</b> from your <a href="https://themeforest.net/downloads" target="_blank">ThemeForest Panel</a> or you can order another one for a <b>new website:</b><br><br>
                    <a href="https://themeforest.net/checkout/from_item/<?php echo EPCL_ITEM_ID; ?>/?license=regular&aid=estudiopatagon&aso=wp-plugin&aca=<?php echo EPCL_THEMESLUG; ?>-welcome" class="button button-primary" target="_blank">Purchase New License</a>&nbsp;
                    <a href="<?php echo admin_url('admin.php?page=estudiopatagon-products'); ?>" class="button">Check other EstudioPatagon Themes</a>
                </small></p>
            <?php endif; ?>
        </div>
        <!-- end: .epcl-system -->

        <!-- start: .epcl-system -->
        <div class="epcl-system alignright">
            <h3>System Status</h3>
            <table class="system-status-table widefat">
                <tbody>
                    <tr>
                        <td>WordPress Version</td>
                        <td><?php bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td>Language</td>
                        <td><?php echo get_locale() ?></td>
                    </tr>
                    <tr>
                        <td>PHP Version</td>
                        <td>
                            <?php
                            $php_version = phpversion();
                            if ( version_compare( $php_version, '7.0', '<' ) ) {
                                echo '<mark class="error">We recommend using PHP version 7.0 or above for greater performance and security.</mark>';
                            } else {
                                echo '<mark class="green">' . esc_html( $php_version ) . '</mark>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>WP Memory Limit</td>
                        <td>
                            <?php
                            $memory = epcl_let_to_num( WP_MEMORY_LIMIT );
                            if ( $memory < 138435456 ) {
                                echo '<mark class="error">WordPress Value: '. WP_MEMORY_LIMIT .'<br>We recommend setting memory to set at least 128M.</mark><br><a href="'.$base_config['php_max_memory'].'" target="_blank" rel="nofollow">Check how to increase memory limit.</a>';
                            } else {
                                echo '<mark class="green">' . size_format( $memory ) . '</mark>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>PHP Max Execution Time</td>
                        <td>
                            <?php
                            $max_input = ini_get('max_execution_time');
                            if ( $max_input < 300 ) {
                                echo '<mark class="error">Server Value: '.$max_input.'<br>We recommend setting PHP max_execution_time to at least 300.</mark><br><a href="'.$base_config['php_max_execution_time'].'" target="_blank">Check how to increase max execution time.</a>';
                            } else {
                                echo '<mark class="green">' . $max_input . '</mark>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>PHP Max Input Vars: </td>
                        <td>
                            <?php
                            $max_input = ini_get('max_input_vars');
                            if ( $max_input < 1000 ) {
                                echo '<mark class="error">Server value: '. $max_input .'<br>We recommend setting PHP max_input_vars to at least 1000.</mark><br><a href="'.$base_config['php_max_input_vars'].'" target="_blank">Check how to increase max input vars.</a>';
                            } else {
                                echo '<mark class="green">' . $max_input . '</mark>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Server Information</td>
                        <td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
                    </tr>

                </tbody>		
            </table>
            <p style="margin-bottom:0"><small><b>Note:</b> these parameters are just recommendations. If your website is already working as expected, you can <b>safely ignore these warnings.</b></small></p>
        </div>
        <!-- end: .epcl-system -->

    </div>

    <?php
}