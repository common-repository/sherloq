<?php
/**
 * The main plugin class
 *
 * @since 1.1.0
 */
class SherloQ {

  /**
   * A class property to contain the instance
   *
   * @since 1.1.0
   *
   * @var static null
   * @access public
   */
  public static $instance = false;

  /**
   * Protect the singleton class instance
   *
   * @since 1.1.0
   *
   * @return mixed
   * @access public
   */
  public function __construct() {

    if ( ! self::$instance ) {

      $message = '<code>' . __CLASS__ . '</code>' . ' is a singleton. ';
      $message .= 'It must be instantiated with <code>' . __CLASS__ . '::get_instance();</code>';

      wp_die( $message );
    }
  }

  /**
   * Instantiate the main plugin class
   *
   * @since 1.1.0
   *
   * @return static An instance of the class
   * @access public
   */
  public static function get_instance() {

    if ( ! is_a( self::$instance, __CLASS__ ) ) {

      self::$instance = true;
      self::$instance = new self();
      self::$instance->init();
    }

    return self::$instance;
  }

  /**
   * Initial setup to implement plugin functionality in WordPress
   *
   * @since 1.1.0
   *
   * @return void
   * @access protected
   */
  protected function init() {

    // Public facing plugin functions
    add_action( 'wp_enqueue_scripts', array( 'SherloQ', 'enqueue_assets' ) );
    add_action( 'wpcf7_before_send_mail', array( 'SherloQ_Feed', 'send_feed_data' ) );

    // Admin facing plugin functions
    if ( is_admin() ) {

      register_setting( 'cf7-sherloq', 'sherloq_api' );

      add_action( 'admin_init', array( 'SherloQ_Activation', 'check_update' ) );
      add_action( 'admin_init', array( 'SherloQ_Admin', 'settings' ) );
      add_action( 'admin_init', array( 'SherloQ_Activation', 'redirect') );
      add_action( 'admin_menu', array( 'SherloQ_Admin', 'menu' ) );
      add_action( 'admin_notices', array( 'SherloQ_Admin', 'add_settings_errors' ) );
      add_action( 'admin_notices', array( 'SherloQ_Activation', 'check_for_cf7' ) );
      add_action( 'wp_ajax_sherloq_test_username', array( 'SherloQ_Feed', 'test_api_username' ) );
      add_action( 'plugin_action_links_' . SHERLOQ_PLUGIN_BASENAME, array( 'SherloQ_Admin', 'action_links' ) );
      add_action( 'admin_enqueue_scripts', array( 'SherloQ', 'enqueue_admin_assets' ) );

      add_filter( 'wpcf7_editor_panels', array( 'SherloQ_Admin', 'panels' ) );
      add_filter( 'wpcf7_after_save', array( 'SherloQ_Admin', 'save_form_settings' ) );
    }
  }

  /**
   * Enqueue front-end scripts and styles
   *
   * @since 1.1.0
   *
   * @return void
   * @access public
   */
  public static function enqueue_assets() {

    // The query string parser used on the front-end to capture UTM data
    wp_enqueue_script( 'sherloq-utm', SHERLOQ_PLUGIN_URL . 'assets/js/utm_cookie.js', array(), false, true);
  }

  /**
   * Enqueue admin scripts and styles
   *
   * @since 1.2.0
   *
   * @return void
   * @access public
   */
  public static function enqueue_admin_assets() {

    $cf7_screen = 'admin.php?page=wpcf7';

    // Only load the script if we are on the right edit screen
    if ( ! get_current_screen() ==  $cf7_screen ) {
      return;
    }

    $file = SHERLOQ_PLUGIN_DIR . '/assets/js/sherloq-admin.js';
    $file_url = SHERLOQ_PLUGIN_URL . 'assets/js/sherloq-admin.js';
    $version = filemtime( $file );

    $css = SHERLOQ_PLUGIN_DIR . '/assets/css/sherloq-admin.css';
    $css_url = SHERLOQ_PLUGIN_URL . '/assets/css/sherloq-admin.css';
    $css_version = filemtime( $css );

    // Script to be loaded on the edit form panel for SherloQ
    wp_enqueue_script( 'sherloq-admin-script', $file_url, array('jquery'), $version, true);
    // Styles to be loaded on the edit form panel for SherloQ
    wp_enqueue_style( 'sherloq-admin-style', $css_url, '', $css_version, 'all' );
  }

}
