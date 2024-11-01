<?php
/**
 * The class containing the plugin's WordPress admin area functionality
 *
 * @since 1.1.0
 */
class SherloQ_Admin {

  /**
   * Add a link to the settings page on the plugin screen
   *
   * @since 1.2.0
   *
   * @param array $links The default links array
   * @return array The $links array with our link added
   */
  public static function action_links( $links ) {

    $url = get_admin_url() . '/admin.php?page=cf7-sherloq';
    $link = '<a href="' . $url . '">Settings</a>';

    array_unshift( $links, $link );
    return $links;
  }

  /**
   * Add a submenu page for the general plugin settings
   *
   * @since 1.1.0
   *
   * @return void
   * @access public
   */
  public static function menu() {
    add_submenu_page(
      'wpcf7',
      'SherloQ API Feeds',
      'SherloQ API Feeds',
      'manage_options',
      'cf7-sherloq',
      array( 'SherloQ_Admin', 'render_admin' )
    );
  }

  /**
   * Register settings, including sections and fields.
   *
   * @since 1.1.0
   *
   * @return void
   * @access public
   */
  public static function settings() {

    add_settings_section(
      'section_api_creds',
      'SherloQ API Credentials',
      array( 'SherloQ_Admin', 'render_api_section' ),
      'cf7-sherloq'
    );

    add_settings_field(
      'sherloq_username',
      'API Username',
      array( 'SherloQ_Admin', 'render_api_username' ),
      'cf7-sherloq',
      'section_api_creds'
    );

    add_settings_field(
      'sherloq_key',
      'API Key',
      array( 'SherloQ_Admin', 'render_api_key' ),
      'cf7-sherloq',
      'section_api_creds'
    );

    add_settings_section(
      'section_choose_forms',
      'Select Forms to Feed',
      array( 'SherloQ_Admin', 'render_choices' ),
      'cf7-sherloq'
    );

    // Get the available forms
    $forms = self::available_forms();

    // Add a settings field (checkbox) for each available form
    foreach ( $forms as $form ) {
      add_settings_field(
        $form['name'],
        $form['title'],
        array( 'SherloQ_Admin', 'render_choice' ),
        'cf7-sherloq',
        'section_choose_forms',
        $form
      );
    }
  }

  public static function add_settings_errors() {
    settings_errors();
  }

  /**
   * Determines the CF7 forms available to be fed into the API
   *
   * @since 1.1.0
   *
   * @return string The HTML to display on the settings screen
   * @access public
   */
  public static function available_forms() {

    // Query the custom post type used for CF7
  	$forms_query = new WP_Query( array(
      'post_type' => 'wpcf7_contact_form',
      'no_found_rows' => true,
      'posts_per_page' => 20
    ));

    // Extract the posts
    $form_posts = $forms_query->posts;

  	$forms = array();

    // Build an array of the information needed to make the settings fields
    foreach ( $form_posts as $post ) {
      $forms[] = array(
        'id' => $post->ID,
        'name' => $post->post_name,
        'title' => $post->post_title
      );
    }

    return $forms;
  }

  /**
   * Adds an additional settings panel to form pages set to feed to the API
   *
   * @since 1.1.0
   *
   * @param array $panels The existing tabs in the CF7 form editor
   *
   * @return array The modified array of editor tabs
   * @access public
   */
  public static function panels( $panels ) {

    // Get the plugin options array
    $options = get_option('sherloq_api');

    // If this panel is to be included, its value in our options will be this
    $current_option = 'cf7_form_' . wp_filter_nohtml_kses( $_GET['post'] );

    // Create the array of arguments with callback that the CF7 panels hook wants
    $tab = array(
      'sherloq-feed' => array(
        'title' => 'SherloQ Feed',
        'callback' => array( 'SherloQ_Admin', 'render_panel' )
      )
    );

    // If the 'sherloq_api' option for the current form is set, merge in our tab
    if ( isset( $options[$current_option] ) ) {
      $panels = array_merge( $panels, $tab );
    }

    return $panels;
  }

  /**
   * Render the panel for forms where it has been selected
   *
   * @since 1.1.0
   *
   * @param object $form The current CF7 form post
   *
   * @return string The HTML to display on a feed settings tab
   * @access public
   */
  public static function render_panel( $form ) {

    $defaults = array();

    $form_settings = get_option( 'sherloq_api_form_' . $form->id(), $defaults );

    $fields = wpcf7_scan_form_tags();

    foreach ( $fields as $field ) {
      if ( $field->type != 'submit') {
        $available[] = array(
          'name' => $field->name,
          'type' => $field->basetype
        );
      }
    }

    // set the multiple_fields option to every field
    $available[] = array(
      'name' => 'multiple_fields',
      'type' => 'multiple_fields'
    );

    // set the initial field values with no assignment
    $available[] = array(
      'name' => 'no_assignment',
      'type' => 'no_assignment'
    );

    $model = SherloQ_Feed::model();

    wp_nonce_field( 'save_form_settings', 'sherloq_feed_nonce' );
    include SHERLOQ_PLUGIN_DIR . '/views/panel.php';
  }

  /**
   * Save the settings for a specific form
   *
   * @since 1.1.0
   *
   * @param object $form The current CF7 form post
   *
   * @return void
   * @access public
   */
  public static function save_form_settings( $form ) {

    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die('You do not have sufficient permissions to save these options.');
    }

    if ( empty( $_POST ) || ! isset( $_POST ) || ! isset( $_POST['sherloq-feed'] ) ) {
      return;
    }

    if ( ! wp_verify_nonce( $_POST['sherloq_feed_nonce'], 'save_form_settings' ) ) {

    }
    update_option( 'sherloq_api_form_' . $form->id(), $_POST['sherloq-feed'] );
  }

  /**
   * Render the main admin settings screen for the plugin
   *
   * @since 1.1.0
   *
   * @return string The HTML to display on the settings screen
   * @access public
   */
  public static function render_admin() {
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }
    ?>

    <div class="wrap sherloq-admin-settings">
      <div id="icon-themes" class="icon32"></div>
      <h1>Feed CF7 Submissions to SherloQ Leads API</h1>

      <?php
        if( isset( $_GET[ 'tab' ] ) ) {
          $active_tab = sanitize_text_field( $_GET[ 'tab' ] );
        } else {
          $active_tab = 'api_settings';
        }// end if
      ?>

      <h2 class="nav-tab-wrapper">
        <a href="?page=cf7-sherloq&tab=api_settings" class="nav-tab <?php echo $active_tab == 'api_settings' ? 'nav-tab-active' : ''; ?>">API Settings</a>
        <a href="?page=cf7-sherloq&tab=log_table" class="nav-tab <?php echo $active_tab == 'log_table' ? 'nav-tab-active' : ''; ?>">API Log Table</a>
      </h2>

      <form method="post" action="options.php">

        <?php
        if( $active_tab == 'api_settings' ) {
          settings_fields( 'cf7-sherloq' );
          do_settings_sections( 'cf7-sherloq' );
          submit_button( 'Save Settings' );
        } else {
          ?>
          <h3>API Connection Logs</h3>
          <?php
          echo self::render_api_logs();
        } // end if/else
        ?>

      </form>
    </div>
<?php
  }

  /**
   * Render the content for the API logs tab
   *
   * @since 1.5.0
   *
   * @return string The HTML to display on the settings screen
   * @access public
   */
  private static function render_api_logs() {
    global $wpdb;

    $table_name = $wpdb->prefix . "sherloq_log";

    $logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY time DESC" );

    if(!empty($logs)) {
      $display = '<table>
        <tr>
          <th>Time</th>
          <th>Form ID</th>
          <th>Request</th>
          <th>Response</th>
        </tr>';

      foreach ($logs as $log){
        $request = json_decode($log->request);
        $response = json_decode($log->response);

        $display .= '<tr>
            <td class="time">' . $log->time . '</td>
            <td class="form">' . $log->form_id . '</td>
            <td class="logging"><pre>' . json_encode($request, JSON_PRETTY_PRINT) . '</pre></td>
            <td class="logging"><pre>' . json_encode($response, JSON_PRETTY_PRINT) . '</pre></td>
        </tr>';
      }

      $display .= '</table>';
    } else {
      $display = '<h4>There are currently no logs in the table.</h4>';
    }

    return $display;
  }

  /**
   * Render the settings screen section for supplying a valid API URL
   *
   * @since 1.1.0
   *
   * @return string The HTML to display in the API section
   * @access public
   */
  public static function render_api_section() {
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }
    ?>
<p>Please provide a valid username for accessing the SherloQ Leads API.</p>
    <?php
  }

  /**
   * Render the form field to input the username for the SherloQ API
   *
   * @since 1.1.0
   *
   * @return string The HTML to display the API username form field
   * @access public
   */
  public static function render_api_username() {
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }
    $options = get_option('sherloq_api');
    $value = '';
    if ( isset( $options['sherloq_api_username'] ) ) {
      $value = $options['sherloq_api_username'];
    }
    ?>
<input class="regular-text" type="text" name="sherloq_api[sherloq_api_username]" value="<?php echo $value; ?>">&nbsp;
    <?php
  }

  /**
   * Render the form field to input the key for the SherloQ API
   *
   * @since 1.3.0
   *
   * @return string The HTML to display the API key form field
   * @access public
   */
  public static function render_api_key() {
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }
    $options = get_option('sherloq_api');
    $value = '';
    if ( isset( $options['sherloq_api_key'] ) ) {
      $value = $options['sherloq_api_key'];
    }
    ?>
<input class="regular-text" type="text" name="sherloq_api[sherloq_api_key]" value="<?php echo $value; ?>">&nbsp;
<button id="sherloq-api-test" class="button-secondary">Test Credentials</button>
<div id="sherloq-api-test-result"></div>
    <?php
  }

  /**
   * Render the settings screen section for choosing which forms to feed
   *
   * @since 1.1.0
   *
   * @return string The HTML to display the section
   * @access public
   */
  public static function render_choices() {
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }
    ?>
<p>After selecting the forms you would like to feed to the Sherloq Leads API, you will need to configure field settings for each selected form.</p>
    <?php
  }

  /**
   * Render the form fields for selecting which forms to feed
   *
   * @since 1.1.0
   * @return string The HTML to display the choices
   * @access public
   */
  public static function render_choice( $form ) {
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }
    $options = get_option('sherloq_api');
    $active_form = 'cf7_form_' . $form['id'];
    ?>
<input type="checkbox" name="sherloq_api[<?php echo $active_form; ?>]" value="1" <?php checked( isset( $options[$active_form]) ); ?>>
    <?php
  }

}
