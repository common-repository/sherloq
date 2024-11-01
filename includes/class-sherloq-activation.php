<?php
/**
 * The class with functionality to be performed on activation and deactivation
 *
 * @since 1.1.0
 */
class SherloQ_Activation {

  /**
   * The version of the database table for the api log
   *
   * @since 1.2.2
   *
   * @var String $db_version
   */
  public static $db_version = '1.0';

  /**
   * Check for updates to the plugin and perform upgrade operations
   *
   * @since 1.2.2
   *
   * @return void
   * @access public
   */
  public static function check_update() {

    // Add the plugin version to its own special option
    if ( ! get_option( 'sherloq_api_version' ) ) {
      add_option( 'sherloq_api_version', SHERLOQ_VERSION );
    } else {
      update_option( 'sherloq_api_version', SHERLOQ_VERSION );
    }

    // Things to do when the version changes
    if ( get_option( 'sherloq_api_version' ) && get_option( 'sherloq_api_version' ) !=  SHERLOQ_VERSION ) {
      // Upgrade operations
    }

    // If the log table doesn't exist yet, create it
    if ( ! get_option( 'sherloq_db_version' ) ) {
      self::create_log_table();
    }

  }

  /**
   * Operations to be performed when the plugin is activated
   *
   * @since 1.1.0
   *
   * @return void
   * @access public
   */
  public static function activate() {

    add_option( 'sherloq_api' );
    add_option( 'sherloq_api_activation', true );
    add_option( 'sherloq_api_version', SHERLOQ_VERSION );
    self::create_log_table();
  }

  /**
   * Redirect to the plugin settings page
   *
   * @since 1.2.0
   *
   * @param  string $plugin Path to the main plugin file
   * @return void Redirects to plugin settings page
   */
  public static function redirect() {

    $url = get_admin_url() . '/admin.php?page=cf7-sherloq';

    if ( get_option(  'sherloq_api_activation', false ) ) {

      delete_option(  'sherloq_api_activation' );
      exit( wp_redirect( $url ) );
    }
  }

  /**
   * Check for the installation / activation status of Contact Form 7
   *
   * @since 1.2.0
   *
   * @return string An error message to be added to the WordPress admin notices
   * @access public
   */
  public static function check_for_cf7() {

    $inactive_plugins = get_admin_url() . '/plugins.php?plugin_status=inactive';
    $cf7 = WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php';

    if ( ! file_exists( $cf7 ) ) {
      ?>
<div id="message" class="notice notice-error is-dismissable" data-dismissable>
  <p>The Contact Form 7 plugin must be installed for SherloQ API Feeds to work.</p>
</div>
      <?php
    } elseif ( ! class_exists( 'WPCF7' ) ) {
      ?>
<div id="message" class="notice notice-warning is-dismissable" data-dismissable>
  <p>The Contact Form 7 plugin must be activated for SherloQ API Feeds to work. <a href="<?php echo $inactive_plugins; ?>">Activate it now.</a></p>
</div>
      <?php
    }
  }

  /**
   * Create a table in the WordPress database to log requests and responses
   *
   * @todo Create database upgrade
   *
   * @since 1.2.2
   *
   * @return void
   * @access public
   */
  public static function create_log_table() {

    // Call the global WordPress database object
    global $wpdb;

    // Get the charset setting from the object
    $charset_collate = $wpdb->get_charset_collate();

    // Name the custom table
    $table_name = $wpdb->prefix . 'sherloq_log';

    // Create the table structure
    $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            form_id bigint(20) NOT NULL,
            request LONGTEXT NOT NULL,
            response LONGTEXT NOT NULL,
            UNIQUE KEY id (id)
          ) $charset_collate;";

    // This core file has the dbDelta function
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Version the database in case we need to change the structure later
    add_option( 'sherloq_db_version', self::$db_version );
  }


  /**
   * Operations to be performed when the plugin is deactivated
   *
   * @todo Add warning before deactivation
   *
   * @since 1.1.0
   *
   * @return void
   * @access public
   */
  public static function deactivate() {

    $forms = SherloQ_Admin::available_forms();

    // If there's an option set for an available form, delete it
    foreach ( $forms as $form ) {
      $form_option = 'sherloq_api_form_' . $form['id'];
      if ( get_option( $form_option ) ) {
        delete_option( $form_option );
      }
    }

    // Unregister the setting and delete the option
    unregister_setting( 'cf7-sherloq', 'sherloq_api' );
    delete_option( 'sherloq_api' );

    // Drop the log table
    global $wpdb;
    $table_name = $wpdb->prefix . 'sherloq_log';
    $sql = "DROP TABLE $table_name;";
    $wpdb->query( $sql );

    // Delete the log version option
    delete_option( 'sherloq_db_version', $db_version );
  }

}
