/**
 * Provide functionality to auto-match mappings of required SherloQ fields to
 * available CF7 form fields
 *
 * @since 1.2.0
 */
( function( $ ) {

  $( document ).ready( function(){

    // Disable event handler for unsaved changes. This is a temporary solution
    // that should be replaced as soon as possible with a better one
    $( window ).off( 'beforeunload' );

    // The array of fields to match
    var sherloqFields = [
      'name',
      'email',
      'phone',
      'message'
    ];

    // The message field may have one of a number of known variants
    var messageVariants = [
      'message',
      'question',
      'comments',
      'client_message',
      'case-overview'
    ]

    // Build new associative array of the select elements that correspond to
    // the SherloQ required fields
    var $sherloqFields = [];
    for ( var i = 0; i < sherloqFields.length; i++ ) {
      $sherloqFields[sherloqFields[i]] = $('#sherloq-field-' + sherloqFields[i] );
    }

    /**
     * Match the select option to the field in question
     *
     * @since 1.2.0
     *
     * @param  {array} $select An array of available select elements
     * @param  {string} key The SherloQ field name we want to match
     * @return {undefined} Performs action without a return
     */
    var fieldMatch = function( $select, key ) {

      $parent = $select.parent();
      $options = $select.children();

      for ( var i = 0; i < $options.length; i++ ) {

        var setSelect = function() {
          // Set the value
          $select.val($options[i].value)
          // Let the user know we've done something
          .css({"background-color":"lemonchiffon"});
        }

        // If an option value matches, set it and stop looping
        if ( $options[i].value.match(key) != null ) {
          setSelect();
          break;
        }
      }

    }

    /**
     * Allows a user to click a button and automatically attempt to map
     * the SherloQ fields to the available CF7 fields
     *
     * @since 1.2.0
     *
     * @param  {Object} e The natural click event to be prevented
     * @return {undefined} Performs action without a return
     */
    $('#sherloq-match').on('click', function( e ){

      // Don't submit the form when this button is pressed
      e.preventDefault();

      for ( var key in $sherloqFields ) {

        // If it's a message, try all of the field name variants
        if ( key == 'message' ) {
          for ( var i = 0; i < messageVariants.length; i++ ) {

            fieldMatch( $sherloqFields[key], messageVariants[i] );
          }
        }
        // Otherwise just match the key
        fieldMatch( $sherloqFields[key], key );
      }
    });


    /**
     * Check to see if the username provided matches the domain to determine
     * whether or not the API will accept the lead feed
     *
     * @since 1.2.1
     *
     * @param  {Object} e The natural click event to be prevented
     * @return {undefined} Performs action without a return
     */
    $('#sherloq-api-test').on('click', function( e ){

      // Don't submit the form when this button is pressed
      e.preventDefault();

      if ( $('.sherloq-result').length ) {
        $('.sherloq-result').remove();
        $('#sherloq-api-test-result').html('');
      }

      // A new DOM element for the WordPress progress spinner
      var $spinner = $('<span/>',{
        id: 'sherloq-spinner',
        class: 'spinner is-active',
        style: 'float:none;width:auto;height:auto;padding:20px 0 0 20px;background-position:0;'
      });

      // A new DOM element for a green check mark to indicate success
      var $check = $('<span/>',{
        id: 'sherloq-check',
        class: 'dashicons dashicons-yes sherloq-result',
        style: 'font-size:28px;padding-left:6px;color:#46b450'
      });

      // A new DOM element for a red X to indicate failure
      var $fail = $('<span/>',{
        id: 'sherloq-fail',
        class: 'dashicons dashicons-no-alt sherloq-result',
        style: 'font-size:28px;padding-left:6px;color:#dc3232;cursor:pointer;'
      });

      // A new DOM element for debugging our output
      var $pre = $('<pre/>',{
        id: 'sherloq-debug',
        style: 'background-color:#444444;padding:1em;color:#7df171;cursor:pointer;'
      });

      // First add the spinner
      $(this).after( $spinner );

      // Then set the input value to check for validity
      var sherloq_username = $('input[name*=sherloq_api_username]').val();
      var sherloq_key = $('input[name*=sherloq_api_key]').val();

      // Now set up the request
      var request = {
        'action': 'sherloq_test_username',
        'sherloq_data': {
          'username': sherloq_username,
          'site': window.location.hostname,
          'key': sherloq_key
        }
      }

      // And post it to admin-ajax.php
      $.post( ajaxurl, request, function( response ){

        responseObject = JSON.parse( response );

        // Lose the spinner, we're done
        $('#sherloq-spinner').remove();

        // Check if the response was okay and respond accordingly
        if ( responseObject.response.code == 200 ) {
          $('#sherloq-api-test').after( $check );
        } else {
          $('#sherloq-api-test').after( $fail );
        }

        // Place the results of the test in a div, but hide them for now
        $('#sherloq-api-test-result').hide().html( $pre.html( response ) );
        console.log( responseObject.response.code );

        // If you click the result indicator icon, show the full response
        $('.sherloq-result').on('click', function(){
          $('#sherloq-api-test-result').slideToggle();
        });

      });
    });


  });

})(jQuery);
