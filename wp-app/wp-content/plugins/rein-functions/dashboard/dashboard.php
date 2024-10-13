<?php
if(!defined('ABSPATH')){ exit; }

function epcl_license_page(){

    $lbapi = new LicenseBoxAPI(); 

    $license_file = get_option( EPCL_THEMESLUG . '_license_key_file');
    $license_key = get_option( EPCL_THEMESLUG . '_license_key');
    $license_user = get_option( EPCL_THEMESLUG . '_license_user');

    if( !$lbapi->check_local_license_exist() && $license_file !== '' ){
        $lb_verify_res = $lbapi->verify_license(false, $license_key, $license_user); 
        if( $lb_verify_res['status'] ){
            $lbapi->create_license( false, false, $license_file );  
        }  
    }else{
        $lb_verify_res = $lbapi->verify_license();
    }

    $changelog = false; 

    $lb_activate_res = null;
    $lb_deactivate_res = null;
    $lb_update_data  = null;
    $lb_check_connection = null;
    $connection_class = 'notice-error';
    
    if( !empty($_POST['client_name']) && !empty($_POST['license_code']) && !empty($_POST['license_email']) ){
        check_admin_referer('lb_update_license', 'lb_update_license_sec');
        $lb_activate_res = $lbapi->activate_license(
            strip_tags(trim($_POST['license_code'])), 
            strip_tags(trim($_POST['client_name'])),
            true,
            strip_tags(trim($_POST['license_email']))
        );
        $lb_verify_res = $lbapi->verify_license();
    }

    if( !empty($_POST['lb_deactivate']) ){
        check_admin_referer('lb_deactivate_license', 'lb_deactivate_license_sec');
        $lb_deactivate_res = $lbapi->deactivate_license();
        $lb_verify_res = $lbapi->verify_license();
        if( isset($_SESSION['17b7528d98068e4'])){
            unset($_SESSION['17b7528d98068e4']);
        } 
        delete_option( EPCL_THEMESLUG . '_license_key_file' );
        delete_option( EPCL_THEMESLUG . '_license_key_status' );
        delete_option( EPCL_THEMESLUG . '_license_key' );
        delete_option( EPCL_THEMESLUG . '_license_email' );
        delete_option( EPCL_THEMESLUG . '_license_user' );
    }

    if( !empty($_POST['lb_check_updates']) ){
        check_admin_referer('lb_check_updates', 'lb_check_updates_sec');
        if( !$lbapi->check_local_license_exist() ){
            $lb_verify_res = $lbapi->verify_license(false, $license_key, $license_user);
            if( $lb_verify_res['status'] ){
                $lb_update_data = $lbapi->check_update(); 
            }        
        }else{
            $lb_verify_res = $lbapi->verify_license();
            $lb_update_data = $lbapi->check_update(); 
        }
        set_transient('licensebox_next_update_check', $lb_update_data, 24*HOUR_IN_SECONDS);
        set_site_transient('update_themes', null);
    }

    if( !empty($_POST['lb_check_connection']) ){
        check_admin_referer('lb_check_connection', 'lb_check_connection_sec');
        $lb_check_connection = $lbapi->check_connection();
        if( $lb_check_connection['status'] == true ){
            $connection_class = 'notice-success';
        }
    }

    if( $lb_verify_res['status'] ){
        if(false === ($lb_update_res = get_transient('licensebox_next_update_check'))){
            $lb_update_res = $lbapi->check_update();
            set_transient('licensebox_next_update_check', $lb_update_res, 24*HOUR_IN_SECONDS);
        }   
        $lb_update_data = get_transient('licensebox_next_update_check');
        if( isset($_GET['changelog']) ){
            $changelog = true;
        }
    }

    ?>

    <div class="wrap">
        <h1>License - Settings</h1>
        <?php if($lb_verify_res['status']){ ?>
            <div class="notice notice-info">
                <p>Activated! Your license is valid.</p>
            </div>
        <?php }else{ ?>
            <div class="notice notice-error">
                <p><?php echo (!empty($lb_activate_res['message']))?$lb_activate_res['message']:'No license has been provided yet or the provided license is invalid.' ?></p>
                <!-- <p><b>Error:</b> <?php echo $lb_verify_res['message']; ?></p> -->
            </div>
        <?php }?>
        <?php if( !$lb_verify_res['status'] ): ?>
            <form action="" method="post">
                <?php wp_nonce_field('lb_update_license', 'lb_update_license_sec'); ?>
                <table class="epcl-form-wrapper form-table">
                    <tr>
                        <th>Envato Username</th>
                        <td>
                            <input type="text" name="client_name" size="50" placeholder="<?php
                            if($lb_verify_res['status']){
                                echo 'Enter your Envato username here to update';
                            }else{
                                echo 'Enter your Envato username here';
                            } ?>" required>
                            <p class="description">e.g. <b>estudiopatagon</b> <a href="http://prntscr.com/qk7llp" target="_blank">Check example</a></p>
                        </td>
                    </tr>
                    <tr>
                        <th>Envato Purchase Code</th>
                        <td>
                            <input type="text" name="license_code" size="50" placeholder="<?php
                            if($lb_verify_res['status']){
                                echo 'Enter the license code here to update';
                            }else{
                                echo 'Enter the license code here';
                            } ?>" required>
                            <p class="description"><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank">How to get your purchase code</a></p>
                        </td>
                    </tr>
                    <tr>
                        <th>Your Email</th>
                        <td>
                            <input type="email" name="license_email" size="50" placeholder="Enter your email" required>
                            <p class="description">We will send update news of this product by this<br>email address, don't worry, we hate spam.</p>
                        </td>

                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" value="Activate" class="button button-primary">
                </p>
            </form>

            <div class="epcl-check-updates">
                <h2 class="title">Server Connection</h2>
                <form action="<?php echo admin_url('admin.php?page=estudiopatagon-license&debug=1'); ?>" method="post">
                    <?php wp_nonce_field('lb_check_connection', 'lb_check_connection_sec'); ?>
                    <input type="hidden" name="lb_check_connection" value="yes">
                    <input type="submit" value="Check Connection" class="button button-primary">
                </form>
                <div class="clear"></div>
                <?php if(!empty($lb_check_connection)): ?>
                    <div class="notice <?php echo esc_attr($connection_class); ?>">
                        <p><?php echo $lb_check_connection['message']; ?></p>
                    </div>
                <?php endif; ?>
                <div class="clear"></div>
            </div>

        <?php endif; ?>
        <p class="description">If you have any kind of problem <b>activating/deactivating</b> the license, you can contact us on our <a href="https://estudiopatagon.ticksy.com/" target="_blank">Support System.</a></p>
        <?php if( $lb_verify_res['status'] ): ?>
            <h2 class="title">Deactivate License</h2>
            <p style="max-width: 450px;">
                If you wish to use this license for activating plugin on a different server, you can first release your license from this server by deactivating it below.
            </p>
            <?php if( empty($lb_deactivate_res) ): ?>
                <form action="" method="post">
                    <?php wp_nonce_field('lb_deactivate_license', 'lb_deactivate_license_sec'); ?>
                    <input type="hidden" name="lb_deactivate" value="yes">
                    <input type="submit" value="Deactivate" class="button">
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <?php if( $lb_verify_res['status'] ): ?>
            <div class="epcl-check-updates">
                <h2 class="title">Theme Updates</h2>
                <?php if(empty($_POST['update_id'])): ?>
                    <form action="" method="post">
                        <?php wp_nonce_field('lb_check_updates', 'lb_check_updates_sec'); ?>
                        <input type="hidden" name="lb_check_updates" value="yes">
                        <input type="submit" value="Check for Updates" class="button button-primary">
                        <a href="http://updates.estudiopatagon.com/changelog/<?php echo EPCL_THEMESLUG; ?>" target="_blank" class="button">View All Version History</a>
                    </form>
                <?php endif; ?>
                <div class="clear"></div>
            </div>
            <?php if(empty($_POST['update_id'])): ?>
                <p>
                    <strong><?php echo esc_html($lb_update_data['message']); ?></strong>
                </p>
            <?php endif; ?>
            <?php if($lb_update_data['status']): ?>

                <?php if(!empty($_POST['update_id'])){
                    check_admin_referer('lb_update_download', 'lb_update_download_sec');
                    $lbapi->install_update(
                        strip_tags(trim($_POST['update_id'])),
                        strip_tags(trim($_POST['has_sql'])),
                        strip_tags(trim($_POST['version']))
                    );
                    if (false !== get_transient('licensebox_next_update_check')) {
                        delete_transient('licensebox_next_update_check');
                    }
                ?>
                <?php }else{ ?>
                    <form action="" method="POST">
                        <?php wp_nonce_field('lb_update_download', 'lb_update_download_sec'); ?>
                        <input type="hidden" value="<?php echo esc_attr($lb_update_data['update_id']); ?>" name="update_id">
                        <input type="hidden" value="<?php echo esc_attr($lb_update_data['has_sql']); ?>" name="has_sql">
                        <input type="hidden" value="<?php echo esc_attr($lb_update_data['version']); ?>" name="version">
                        <div style="padding: 10px 0 20px;">
                            <input type="submit" value="Download and Install Update" class="button button-secondary">
                        </div>
                    </form>
                    <div class="epcl-changelog" style="max-width: 700px;">
                        <h3>v<?php echo esc_attr($lb_update_data['version']); ?> - <?php echo esc_attr(date('F j, Y', strtotime($lb_update_data['release_date']))); ?></h3>
                        <?php
                        $changelog_data = strip_tags($lb_update_data['changelog'], '<ol><ul><li><i><b><strong><p><br><a><blockquote>');
                        $changelog_data = str_replace('- Fixed:', '<span class="fix tag">Fixed</span>', $changelog_data);
                        $changelog_data = str_replace('- Added:', '<span class="add tag">Added</span>', $changelog_data);
                        $changelog_data = str_replace('* New Feature:', '<span class="add tag">Added</span>', $changelog_data);
                        $changelog_data = str_replace( array('- Improved:', '- Improved:'), '<span class="improve tag">Improved</span>', $changelog_data);

                        $changelog_data = str_replace('- Removed:', '<span class="removed tag">Removed</span>', $changelog_data);
                        $changelog_data = str_replace( array('- Speed Improvement:', '- Speed Improvement:'), '<span class="improve tag">Improved</span>', $changelog_data);
                        ?>
                        <?php echo $changelog_data; ?>
                    </div>
                <?php } ?>
            <?php endif; //status ?>
        <?php endif; ?>
    </div>

<?php }    