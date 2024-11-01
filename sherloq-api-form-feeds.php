<?php
/**
 * Plugin Name: SherloQ API Form Feeds
 * Plugin URI: http://www.bigvoodoo.com
 * Description: Allows you to send the contents of submissions from selected forms in Contact Form 7 to the SherloQ API
 * Version: 1.8.4
 * Author: Big Voodoo Interactive
 * Author URI: https://www.bigvoodoo.com
 * License: GPLv2
 * Text Domain: sherloq-api-form-feeds
 * Domain Path: /languages
 *
 * @link https://www.bigvoodoo.com
 *
 * @author Aleda Jonquil <aleda@bigvoodoo.com>
 * @author Christina Gleason <tina@bigvoodoo.com>
 * @author Matthew Wimmer <matthew@bigvoodoo.com>
 */

/**
 * Define plugin constants
 *
 * @since 1.1.0
 */
define( 'SHERLOQ_VERSION', '1.8.4' );
define( 'SHERLOQ_PLUGIN', __FILE__ );
define( 'SHERLOQ_PLUGIN_BASENAME', plugin_basename( SHERLOQ_PLUGIN ) );
define( 'SHERLOQ_PLUGIN_NAME', trim( dirname( SHERLOQ_PLUGIN_BASENAME ), '/' ) );
define( 'SHERLOQ_PLUGIN_DIR', untrailingslashit( dirname( SHERLOQ_PLUGIN ) ) );
define( 'SHERLOQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Require plugin classes
 *
 * @since 1.1.0
 */
require_once SHERLOQ_PLUGIN_DIR . '/includes/class-sherloq.php';
require_once SHERLOQ_PLUGIN_DIR . '/includes/class-sherloq-feed.php';
require_once SHERLOQ_PLUGIN_DIR . '/includes/class-sherloq-admin.php';
require_once SHERLOQ_PLUGIN_DIR . '/includes/class-sherloq-activation.php';

/**
 * Initialize the plugin with a fresh instance of the SherloQ class
 *
 * @since 1.1.0
 * @return class SherloQ
 */
function sherloq_api_form_feeds_init() {
	SherloQ::get_instance();
}

add_action( 'plugins_loaded', 'sherloq_api_form_feeds_init', 10 );

/**
 * Hooks the code that runs when the plugin is activated
 *
 */
register_activation_hook( __FILE__, array( 'SherloQ_Activation', 'activate' ) );

/**
 * Hooks the code that runs when the plugin is deactivated
 *
 */
register_deactivation_hook( __FILE__, array( 'SherloQ_Activation', 'deactivate' ) );
