<?php

/**
 * The class to gather and prepare data to be sent to the SherloQ Leads API
 *
 * @since 1.1.0
 */
class SherloQ_Feed {

  /**
   * The API url to send form data from CF7 submissions
   *
   * @since 1.2.0
   *
   * @var string $api_url
   * @access protected
   */
  protected static $api_url = 'https://2w0s6jr7og.execute-api.us-east-1.amazonaws.com/live/leads/bvi-form/new';
  /**
   * The url for testing the username
   *
   * @since 1.2.1
   *
   * @var string @api_test_url
   * @access protected
   */
  protected static $api_test_url = 'https://2w0s6jr7og.execute-api.us-east-1.amazonaws.com/live/leads/bvi-form/verify';

  /**
   * Returns an array of the basic data points we will be collecting
   * so they can have CF7 form fields assigned to them
   *
   * If additional data points to be collected from user input, they
   * may be appended here to the $fields array
   *
   * @since 1.1.0
   *
   * @return array An array of data point names and titles for
   * @access public
   */
  public static function model() {
    $fields = array(
      'name',
      'last-name',
      'email',
      'phone',
      'message'
    );
    $model = array();
    foreach ( $fields as $item ) {
      $model[] = array(
        'name' => $item,
        'title' => ucfirst( $item )
      );
    }
    return $model;
  }

  /**
   * Get an instance of the form submission and extract the posted data
   *
   * @since 1.1.0
   *
   * @return array The data collected from the submitted form
   * @access protected
   */
  protected static function get_form_data() {

    $submission = WPCF7_Submission::get_instance();

    $contact_form = WPCF7_ContactForm::get_current();
    $contact_form_id = $contact_form->id;

    // Convert the format of the timestamp from the submission
    $timestamp = time();
    $convert = new DateTime();
    $convert->setTimestamp( $timestamp );
    $formatted = $convert->format('Y-m-d\TH:i:s.000\Z');

    $data = array(
      'form_id' => $contact_form_id,
      'posted' => $submission->get_posted_data(),
      'meta' => array(
        'remote_ip' => $submission->get_meta( 'remote_ip' ),
        'user_agent' => $submission->get_meta( 'user_agent' ),
        'timestamp' => $formatted
      )
    );

    return $data;
  }

  /**
   * Grab the UTM data that has been captured on the front end and prepare it
   * to be submitted with the final request body
   *
   * @since 1.1.0
   *
   * @return array An array of the UTM query string parameters that have been captured
   * @access protected
   */
  protected static function get_utm_data() {

    // The contents of HTTP Cookies global variable
    $cookies = $_COOKIE;

    // The available values from our UTM cookies
    $utms = array(
      'hostname',
      'referrer',
      'gclid',
      'utm_source',
      'utm_medium',
      'utm_campaign',
      'utm_term',
      'utm_content'
    );

    // The prefix that namespaces the cookies
    $prefix = '_sherloq_';

    // The cookie values to be combined with the final request body
    $data = array();

    // If one of our prefixed cookies exists, add it to the array
    foreach ( $utms as $utm ) {
      $current = $prefix . $utm;
      if ( isset( $cookies[$current] ) ) {
        $data[$utm] = $cookies[$current];
      }
    }

    return $data;
  }

  /**
   * Prepare the final array of data to feed to the API when a user has
   * completed a form
   *
   * @since 1.1.0
   *
   * @return array The combined form data, options data, and UTM data
   * @access protected
   */
  protected static function prepare_feed_data() {

    $options = get_option('sherloq_api');

    // Set the username to blank if it's not in settings
    $username = '';
    if ( isset( $options['sherloq_api_username'] ) ) {
      $username = $options['sherloq_api_username'];
    }

    // The page where the user completed the form
    $form_page = $_SERVER['HTTP_REFERER'];

    // Get the form data from the user submission
    $submission = self::get_form_data();
    $form_id = $submission['form_id'];
    $form_data = $submission['posted'];
    $form_meta = $submission['meta'];

    // Build the inital array before adding the form values
    $data = array(
      'username' => $username,
      'site' => $_SERVER['HTTP_HOST'],
      'name' => '',
			'email' => '',
			'phone' => '',
      'message' => '',
      'additional_fields' => array(),
      'form_page' => $form_page,
      'timestamp' => $form_meta['timestamp'],
      'remote_ip' => $form_meta['remote_ip'],
      'user_agent' => $form_meta['user_agent']
    );

    // Get the options set for the current form
    $fields = get_option( 'sherloq_api_form_' . $form_id );

    // Assign the appropriate field data to the appropriate keys
    foreach ( $fields as $key => $value ) {
      $data[$key] = $form_data[$value];

      if($key == 'last-name') {
        $data['name'] = $data['name'] . ' ' . $form_data[$value];
      }
    }

    $multi_fields = '';
    // find any additional fields that aren't part of the default mapping and dump them to additional_fields
    foreach ( $form_data as $field_key => $field_data ) {
      if( in_array( 'multiple_fields', $fields ) && !in_array($field_key, $fields ) && $field_key != 'last-name' ) {
        $multi_fields .= $field_key . ',';
      }

      if( !in_array($field_key, $fields ) &&
          strpos($field_key, '_wpcf7') === false &&
          strpos($field_key, 'recaptcha') === false &&
          strpos($field_key, 'wpzerospam') === false &&
          strpos($field_key, 'last-name') === false ) {
        $data['additional_fields'][$field_key] = $field_data;
      }
    }

    if( !empty( $multi_fields ) ) {
      $data['message'] = 'multiple_fields:' . rtrim( $multi_fields, "," );
    }

    // Now get the UTM data
    $utm_data = self::get_utm_data();

    $full_data = array_merge( $data, $utm_data );

    // Stir and serve
    return $full_data;
  }

  /**
   * Test the provided username to make sure it's valid
   *
   * @since 1.2.1
   *
   * @return string The JSON encoded response to the remote post
   * @access public
   */
  public static function test_api_username() {

    // The data from the ajax request
    $data = $_POST['sherloq_data'];

    $body = array(
      'username' => $data['username'],
      'site' => $data['site']
    );

    $body = json_encode( $body );

    // Create the request and capture the response in a variable
    $response = wp_safe_remote_post( self::$api_test_url, array(
      'headers' => array(
        'content' => 'application/json',
        'x-api-key' => $data['key']
      ),
      'body' => $body
    ));

    // Capture the server response, whether error or success
    $log_response = '';

    if ( is_wp_error( $response ) ) {
      $log_response = $response->get_error_message();
    } else {
      $log_response = $response;
    }

    // Create an array of data and insert into the log table
    global $wpdb;
    $table_name = $wpdb->prefix . 'sherloq_log';

    $log = array(
      'id' => NULL,
      'time' => current_time( 'mysql' ),
      'form_id' => 999999,
      'request' => $body,
      'response' => json_encode($log_response)
    );

    $logged = $wpdb->insert( $table_name, $log );

    // JSON encode the response, and we're done
    echo json_encode( $response, JSON_PRETTY_PRINT );
    wp_die();
  }

  /**
   * Package the collected data and other details into a request to be
   * posted via wp_safe_remote_post()
   *
   * @since 1.1.0
   *
   * @param object $form The current CF7 form post
   *
   * @return array The combined form data, options data, and UTM data
   * @access public
   */
  public static function send_feed_data( $form ) {

    SherloQ_Activation::check_update();

    $options = get_option('sherloq_api');

    $id = $form->id();

    // If there are options set for the form being submitted, proceed
    if ( get_option( 'sherloq_api_form_' . $id ) ) {

      $url = self::$api_url;

      // JSON encode the request body per the content header below
      $request_body = json_encode( self::prepare_feed_data() );

      // Set the username to blank if it's not in settings
      $api_key = '';
      if ( isset( $options['sherloq_api_key'] ) ) {
        $api_key = $options['sherloq_api_key'];
      }

      // Assemble the required request content
      $request = array(
        'headers' => array(
          'content' => 'application/json',
          'x-api-key' => $api_key
        ),
        'body' => $request_body
      );

      // Post the lead data to the API
      $response = wp_safe_remote_post( $url, $request );

      // Capture the server response, whether error or success
      $log_response = '';

      if ( is_wp_error( $response ) ) {
        $log_response = $response->get_error_message();

        $to = 'programmers@bigvoodoo.com';
        $subject = 'Sherloq Plugin Has Detected an API Issue on' . get_site_url();
        $body = 'The following error was logged from the Sherloq API when sending a form submission through the Sherloq Plugin on ' . get_site_url() . ' at ' . current_time( 'mysql' ) . ':<br><br><pre>' . json_encode( $log_response ) . '</pre><br><br>The following request was attempted to be sent.<br><br><pre>' . $request_body . '</pre><br><br>Please look into this as it may be causing the forms to not send successfully over to the Sherloq API.<br>Sincerely,<br>Sherloq Plugin Error Email Messenger';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail( $to, $subject, $body, $headers );
      } else {
        $log_response = $response;
      }

      // Create an array of data and insert into the log table
      global $wpdb;
      $table_name = $wpdb->prefix . 'sherloq_log';

      $log = array(
        'id' => NULL,
        'time' => current_time( 'mysql' ),
        'form_id' => $id,
        'request' => $request_body,
        'response' => json_encode($log_response)
      );

      $logged = $wpdb->insert( $table_name, $log );
    }
  }
}
