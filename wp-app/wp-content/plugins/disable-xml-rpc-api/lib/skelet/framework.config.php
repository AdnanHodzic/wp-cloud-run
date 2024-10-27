<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.


//
// Skelet Framework ver 2.2.2
//



require_once plugin_dir_path( __FILE__ ) .'classes/setup.class.php';


/*** Skelet Options Cofiguration ***/

if( class_exists( 'SKELET' ) ) {

  //
  // Set a unique slug-like ID
  $prefix = 'dsxmlrpc-settings';

  //
  // Create options
  
  SKELET::createOptions( $prefix, array(
    'framework_title'  => 'XML-RPC Settings',
    'menu_title'       => 'XML-RPC Security',
    'menu_slug'        => 'Security Settings',
    'menu_type'        => 'menu',
    'menu_icon'        => 'dashicons-shield-alt',
    'theme'            => 'dark',
    'nav'              => 'normal',
    'menu_capability'  => 'edit_users',
    'show_reset_section'  => false,
    'show_search'      => true,
    'show_bar_menu'  => false,
    'ajax_save'  => false,
	'footer_text'=> '<span style="color:gainsboro;">Please <a target="_blank" href="https://wordpress.org/support/plugin/disable-xml-rpc-api/reviews/#new-post">rate us</a> in wordprees repository</span>',

	
	
  ) );

  //
  // Create a top-tab
  SKELET::createSection( $prefix, array(
    'id'    => 'general_setting', // Set a unique slug-like ID
	'icon'  => 'fa fa-cogs',
    'title' => esc_html__('XML-RPC Settings','SKELET'),
  ) );




  //
  // Create a sub-tab
  SKELET::createSection( $prefix, array(
    'parent' => 'general_setting', // The slug id of the parent section
    'icon'  => 'fa fa-plug',
    'title'  => 'Secure XMP-RPC',
    'fields' => array(
	// A Submessage
	array(
	  'type'    => 'submessage',
	  'style'   => 'info',
	  'content' => 'What is XML-RPC ?',
	),
	// A Content Field Example
	array(
	  'type'    => 'content',
	  'content' => 'XML-RPC, or XML Remote Procedure Call is a protocol which uses XML to encode its calls and HTTP as a transport mechanism.
Beginning in WordPress 3.5, XML-RPC is enabled by default. Additionally, the option to disable/enable XML-RPC was removed. For various reasons, site owners may wish to disable this functionality. This plugin provides an easy way to do so.',
	),
		// A Submessage
	array(
	  'type'    => 'submessage',
	  'style'   => 'danger',
	  'content' => 'Why you should disable XML-RPC ?',
	),
		// A Content Field Example
	array(
	  'type'    => 'content',
	  'content' => '<ul>
<li>Brute force attacks:<br>
Attackers try to login to WordPress using xmlrpc.php with as many username/password combinations as they can enter. A method within xmlrpc.php allows the attacker to use a single command (system.multicall) to guess hundreds of passwords. Daniel Cid at Sucuri described it well in October 2015: “With only 3 or 4 HTTP requests, the attackers could try thousands of passwords, bypassing security tools that are designed to look and block brute force attempts.”</li>
<li>Denial of Service Attacks via Pingback:<br>
Back in 2013, attackers sent Pingback requests through xmlrpc.php of approximately 2500 WordPress sites to “herd (these sites) into a voluntary botnet,” according to Gur Schatz at Incapsula. “This gives any attacker a virtually limitless set of IP addresses to Distribute a Denial of Service attack across a network of over 100 million WordPress sites, without having to compromise them.”</li>
</ul>',
	  ),
	/* Disable Access to xmlrpc file */
			  array(
	  'id'      => 'dsxmlrpc-switcher',
	  'type'    => 'switcher',
	  'title'   => 'XML-RPC Api Master switch',
	  'desc'   => '(Recommended) Switch OFF : Disable access to xmlrpc.php file but will <strong>allow</strong> selected options below. <br> Switch ON : Enable access to xmlrpc.php file but will <strong>disallow</strong> selected options below.',
	  'default' => false,
	  
	),
		/* Change xml-rpc slug */
	  array(
	  'id'      => 'xmlrpc-slug',
	  'type'    => 'text',
	  'title'   => 'Change XML-RPC slug',
	  'help'    => 'Leave empty if you dont want to change it',
	  'desc'    => 'If you dont want to disable XML-RPC entirely you can change the xmlrpc.php slug to whatever you want to prevent automated attacks',
	  'dependency' => array( 'dsxmlrpc-switcher', '==', 'true' ),
	  'placeholder' => 'Example: mobile-api'
	),
			  array(
	  'id'      => 'jetpack-switcher',
	  'type'    => 'switcher',
	  'title'   => 'Enable xml-rpc for Jetpack',
	  'label'   => 'This switch will add Jetpack servers IP range to the whitelist',
	  'default' => false,
	  'dependency' => array( 'dsxmlrpc-switcher', '==', 'false' ),
	  
	),
	/* Disable Methods */
	array(
	  'id'         => 'disabled-methods',
	  'type'       => 'checkbox',
	  'title'      => 'Disable Methods',
	  'desc'      => 'filter only selected methods from xml-rpc',
	//  'inline'     => true,
	  'dependency' => array( 'dsxmlrpc-switcher', '==', 'true' ),
	  
	  'options'    => array(
		'pingback.ping'          => 'pingback.ping',
		'mt.getTrackbackPings'   => 'mt.getTrackbackPings',
		'pingback.extensions.getPingbacks'       => 'pingback.extensions.getPingbacks',
        'x-pingback'             => 'x-pingback header',
        'mt.publishPost'             => 'mt.publishPost',
        'mt.supportedTextFilters'             => 'mt.supportedTextFilters',
        'mt.supportedMethods'             => 'mt.supportedMethods',
	  ),
	  'default'    => array( 'pingback.ping', 'x-pingback' , 'mt.getTrackbackPings' , 'pingback.extensions.getPingbacks' )
	),
	array(
	  'id'      => 'White-list-IPs',
	  'type'    => 'textarea',
	  'title'   => '<p style="color:#4fb845;font-weight:bold;">White list IPs</P> Allow access to xml-rpc api',
	  'desc'    => 'These IPs will be allowed access to xml-rpc file. separate each IP with comma , ',
	  'help'    => 'You can add IPv4 and IPv6 ips',
	  'placeholder'    => 'example IP adding : 127.0.0.1,127.0.0.2',
	  'dependency' => array( 'dsxmlrpc-switcher', '==', 'false' ),
	),
	array(
	  'id'      => 'Black-list-IPs',
	  'type'    => 'textarea',
	  'title'   => '<p style="color:#dc3545;font-weight:bold;">Black list IPs</P> Block access to xml-rpc api',
	  'desc'    => 'These IPs will be NOT allowed access to xml-rpc file. separate each IP with comma , ',
	  'help'    => 'You can add IPv4 and IPv6 ips',
	  'placeholder'    => 'example IP adding : 127.0.0.1,127.0.0.2',
	  'dependency' => array( 'dsxmlrpc-switcher', '==', 'true' ),
	),
	  

    )
  ) );

  
     //
  /// sub menu 
  SKELET::createSection( $prefix, array(
  //  'parent' => 'general_setting', // The slug id of the parent section
    'icon'  => 'fas fa-shield-alt',
    'title'  => 'Extra Security',
    'fields' => array(
	array(
	  'id'      => 'json-rest-api',
	  'type'    => 'switcher',
	  'title'   => 'Disable JSON REST API',
	  'desc'    => 'Disable the JSON REST API for not logged in users',
	  'default' => false,
	),
	array(
	  'id'      => 'htaccess protection',
	  'type'    => 'switcher',
	  'title'   => 'Disable writing in htaccess file',
	  'desc'    => 'Protect your website by changing htaccess file permission to read-only (0444)',
	  'default' => false,
	),
	array(
	  'id'      => 'remove-wp-ver',
	  'type'    => 'switcher',
	  'title'   => 'Hide WordPress Version',
	  'desc'    => 'Remove WordPress version for security reasons',
	  'default' => false,
	),
	array(
	  'id'      => 'disable-code-editor',
	  'type'    => 'switcher',
	  'title'   => 'Disable built-in WordPress file editor',
	  'desc'    => 'Disable WordPress file editor for security reasons',
	  'default' => false,
	  'help'    => 'If you want to edit your themes and plugins codes you need to switch off this option!'
	),
	array(
	  'id'      => 'disable-wlw',
	  'type'    => 'switcher',
	  'title'   => 'Disable wlw manifest',
	  'desc'    => 'WLW (Windows Live Writer Manifest) is a deprecated windows software no point in keeping it on your website',
	  'default' => true,
	),
	
	)
  ) );
  
  

  //
  /// top-tab 
  SKELET::createSection( $prefix, array(
    'id' => 'Wordpress Permormance', 
    'icon'  => 'fas fa-tachometer-alt',
    'title'  => 'Speed Up WordPress',
    'fields' => array(
			  array(
	  'id'      => 'slow-heartbeat',
	  'type'    => 'switcher',
	  'title'   => 'Heartbeat Slowdown',
	  'desc'    => 'Heartbeat can use a lot of resources and slow down you website',
	  'default' => true,
	),			  array(
	  'id'      => 'hotlink-fix',
	  'type'    => 'switcher',
	  'title'   => 'Hotlink Fix',
	  'desc'    => 'Disable Hotlinking and leaching of your Content, prevent other websites to use your images and other files on their site (works only with .htaccess)',
	  'help'    => 'disable hotlinking of images with forbidden',
	  'default' => false,
	),
	array(
	  'id'      => 'remove-emojis',
	  'type'    => 'switcher',
	  'title'   => 'Remove built in emojis',
	  'desc'    => 'This option will disbale default wordpress emojis',
	  'default' => true,
	  
	),
	array(
	  'id'      => 'remove-rss',
	  'type'    => 'switcher',
	  'title'   => 'Remove RSS and RSD',
	  'desc'    => 'This option will disable RSS and RSD on your website',
	  'default' => false,
	  
	),
	array(
	  'id'      => 'disable-oembed',
	  'type'    => 'switcher',
	  'title'   => 'Disable oEbmed',
	  'desc'    => 'Disable oEmbed media in your website',
	  'default' => false,
	  
	),

	
	)
  ) );
  
  //
  // Create a top-tab
  SKELET::createSection( $prefix, array(
    'id'    => 'Backup/Restore', // Set a unique slug-like ID
	'icon'      => 'fa fa-database',
    'title' => 'Backup/Restore Settings',
    'fields' => array(
		array(
	  'type'    => 'subheading',
	  'content' => 'You can backup the security settings here and restore it in somewhere else ...',
		),
		 array(
	     'type' => 'backup',
	      ),


		)

  ) );



}

