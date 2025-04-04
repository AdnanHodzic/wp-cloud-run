<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc59d002476a452800baaf79c430753cb
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $prefixesPsr0 = array (
        'C' => 
        array (
            'ChromePhp' => 
            array (
                0 => __DIR__ . '/..' . '/ccampbell/chromephp',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'UDX\\Settings' => __DIR__ . '/../..' . '/lib/includes/class-settings.php',
        'UDX\\Utility' => __DIR__ . '/../..' . '/lib/includes/class-utility.php',
        'UsabilityDynamics\\UD_API\\API' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-api.php',
        'UsabilityDynamics\\UD_API\\Admin' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-admin.php',
        'UsabilityDynamics\\UD_API\\Bootstrap' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-bootstrap.php',
        'UsabilityDynamics\\UD_API\\Licenses_Table' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-licenses-table.php',
        'UsabilityDynamics\\UD_API\\Manager' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-manager.php',
        'UsabilityDynamics\\UD_API\\More_Products_Table' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-more-products-table.php',
        'UsabilityDynamics\\UD_API\\Scaffold' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-scaffold.php',
        'UsabilityDynamics\\UD_API\\UI' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-ui.php',
        'UsabilityDynamics\\UD_API\\Update_Checker' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-update-checker.php',
        'UsabilityDynamics\\UD_API\\Utility' => __DIR__ . '/..' . '/udx/lib-ud-api-client/lib/classes/class-utility.php',
        'UsabilityDynamics\\WP\\Bootstrap' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-bootstrap.php',
        'UsabilityDynamics\\WP\\Bootstrap_Plugin' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-bootstrap-plugin.php',
        'UsabilityDynamics\\WP\\Bootstrap_Theme' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-bootstrap-theme.php',
        'UsabilityDynamics\\WP\\Dashboard' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-dashboard.php',
        'UsabilityDynamics\\WP\\Errors' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-errors.php',
        'UsabilityDynamics\\WP\\Scaffold' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-scaffold.php',
        'UsabilityDynamics\\WP\\TGMPA_List_Table' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-tgm-list-table.php',
        'UsabilityDynamics\\WP\\TGM_Bulk_Installer' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-tgm-bulk-installer.php',
        'UsabilityDynamics\\WP\\TGM_Bulk_Installer_Skin' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-tgm-bulk-installer.php',
        'UsabilityDynamics\\WP\\TGM_Plugin_Activation' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-tgm-plugin-activation.php',
        'UsabilityDynamics\\WP\\Utility' => __DIR__ . '/..' . '/udx/lib-wp-bootstrap/lib/classes/class-utility.php',
        'wpCloud\\StatelessMedia\\API' => __DIR__ . '/../..' . '/lib/classes/class-api.php',
        'wpCloud\\StatelessMedia\\Addons' => __DIR__ . '/../..' . '/lib/classes/class-addons.php',
        'wpCloud\\StatelessMedia\\Ajax' => __DIR__ . '/../..' . '/lib/classes/class-ajax.php',
        'wpCloud\\StatelessMedia\\AppEngine' => __DIR__ . '/../..' . '/lib/classes/class-google-app-engine.php',
        'wpCloud\\StatelessMedia\\Batch\\BatchTask' => __DIR__ . '/../..' . '/lib/classes/batch/class-batch-task.php',
        'wpCloud\\StatelessMedia\\Batch\\BatchTaskManager' => __DIR__ . '/../..' . '/lib/classes/batch/class-batch-task-manager.php',
        'wpCloud\\StatelessMedia\\Batch\\IBatchTask' => __DIR__ . '/../..' . '/lib/classes/batch/interface-batch.php',
        'wpCloud\\StatelessMedia\\Batch\\Migration' => __DIR__ . '/../..' . '/lib/classes/batch/class-migration.php',
        'wpCloud\\StatelessMedia\\Bootstrap' => __DIR__ . '/../..' . '/lib/classes/class-bootstrap.php',
        'wpCloud\\StatelessMedia\\Compatibility' => __DIR__ . '/../..' . '/lib/classes/class-compatibility.php',
        'wpCloud\\StatelessMedia\\CompatibilityWooExtraProductOptions' => __DIR__ . '/../..' . '/lib/classes/compatibility/woo-extra-product-options.php',
        'wpCloud\\StatelessMedia\\DB' => __DIR__ . '/../..' . '/lib/classes/class-db.php',
        'wpCloud\\StatelessMedia\\DynamicImageSupport' => __DIR__ . '/../..' . '/lib/classes/class-dynamic-image-support.php',
        'wpCloud\\StatelessMedia\\EWWW' => __DIR__ . '/../..' . '/lib/classes/compatibility/ewww.php',
        'wpCloud\\StatelessMedia\\Errors' => __DIR__ . '/../..' . '/lib/classes/class-errors.php',
        'wpCloud\\StatelessMedia\\FatalException' => __DIR__ . '/../..' . '/lib/classes/exception-fatal.php',
        'wpCloud\\StatelessMedia\\GS_Client' => __DIR__ . '/../..' . '/lib/classes/class-gs-client.php',
        'wpCloud\\StatelessMedia\\Helper' => __DIR__ . '/../..' . '/lib/classes/class-helper.php',
        'wpCloud\\StatelessMedia\\Imagify' => __DIR__ . '/../..' . '/lib/classes/compatibility/imagify.php',
        'wpCloud\\StatelessMedia\\LearnDash' => __DIR__ . '/../..' . '/lib/classes/compatibility/learn-dash.php',
        'wpCloud\\StatelessMedia\\Logger' => __DIR__ . '/../..' . '/lib/classes/class-logger.php',
        'wpCloud\\StatelessMedia\\Migrator' => __DIR__ . '/../..' . '/lib/classes/class-migrator.php',
        'wpCloud\\StatelessMedia\\Module' => __DIR__ . '/../..' . '/lib/classes/class-module.php',
        'wpCloud\\StatelessMedia\\Settings' => __DIR__ . '/../..' . '/lib/classes/class-settings.php',
        'wpCloud\\StatelessMedia\\ShortPixel' => __DIR__ . '/../..' . '/lib/classes/compatibility/shortpixel.php',
        'wpCloud\\StatelessMedia\\SimpleLocalAvatars' => __DIR__ . '/../..' . '/lib/classes/compatibility/simple-local-avatars.php',
        'wpCloud\\StatelessMedia\\Singleton' => __DIR__ . '/../..' . '/lib/classes/trait-singleton.php',
        'wpCloud\\StatelessMedia\\Status' => __DIR__ . '/../..' . '/lib/classes/class-status.php',
        'wpCloud\\StatelessMedia\\Status\\GoogleCloudInfo' => __DIR__ . '/../..' . '/lib/classes/status/class-info-google_cloud.php',
        'wpCloud\\StatelessMedia\\Status\\Info' => __DIR__ . '/../..' . '/lib/classes/status/class-info.php',
        'wpCloud\\StatelessMedia\\Status\\Migrations' => __DIR__ . '/../..' . '/lib/classes/status/class-migrations.php',
        'wpCloud\\StatelessMedia\\Status\\StatelessInfo' => __DIR__ . '/../..' . '/lib/classes/status/class-info-stateless.php',
        'wpCloud\\StatelessMedia\\StreamWrapper' => __DIR__ . '/../..' . '/lib/classes/class-gs-stream-wrapper.php',
        'wpCloud\\StatelessMedia\\SyncNonMedia' => __DIR__ . '/../..' . '/lib/classes/class-sync-non-media.php',
        'wpCloud\\StatelessMedia\\Sync\\BackgroundSync' => __DIR__ . '/../..' . '/lib/classes/sync/class-background-sync.php',
        'wpCloud\\StatelessMedia\\Sync\\FileSync' => __DIR__ . '/../..' . '/lib/classes/sync/class-file-sync.php',
        'wpCloud\\StatelessMedia\\Sync\\HelperWindow' => __DIR__ . '/../..' . '/lib/classes/sync/class-helper-window.php',
        'wpCloud\\StatelessMedia\\Sync\\ISync' => __DIR__ . '/../..' . '/lib/classes/sync/interface-sync.php',
        'wpCloud\\StatelessMedia\\Sync\\ImageSync' => __DIR__ . '/../..' . '/lib/classes/sync/class-image-sync.php',
        'wpCloud\\StatelessMedia\\Sync\\LibrarySync' => __DIR__ . '/../..' . '/lib/classes/sync/class-library-sync.php',
        'wpCloud\\StatelessMedia\\Sync\\NonLibrarySync' => __DIR__ . '/../..' . '/lib/classes/sync/class-non-library-sync.php',
        'wpCloud\\StatelessMedia\\TheEventsCalendar' => __DIR__ . '/../..' . '/lib/classes/compatibility/the-events-calendar.php',
        'wpCloud\\StatelessMedia\\UnprocessableException' => __DIR__ . '/../..' . '/lib/classes/exception-unprocessable.php',
        'wpCloud\\StatelessMedia\\Upgrader' => __DIR__ . '/../..' . '/lib/classes/class-upgrader.php',
        'wpCloud\\StatelessMedia\\Utility' => __DIR__ . '/../..' . '/lib/classes/class-utility.php',
        'wpCloud\\StatelessMedia\\WPBakeryPageBuilder' => __DIR__ . '/../..' . '/lib/classes/compatibility/wpbakery-page-builder.php',
        'wpCloud\\StatelessMedia\\WPSmush' => __DIR__ . '/../..' . '/lib/classes/compatibility/wp-smush.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc59d002476a452800baaf79c430753cb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc59d002476a452800baaf79c430753cb::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitc59d002476a452800baaf79c430753cb::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitc59d002476a452800baaf79c430753cb::$classMap;

        }, null, ClassLoader::class);
    }
}
