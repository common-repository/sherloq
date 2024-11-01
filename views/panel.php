<?php
/**
 * The partial file to render the settings panel for the tab
 * added to the individual form settings for the API feed
 *
 * @since 1.0.0
 */
?>
<h2>Map Available Form Fields to API</h2>
<p>Please map the fields from your form to the email lead end points.</p>
<p><strong>Note:</strong> If you add to or change the available fields, you will need to update these mappings.</p>
<div id="sherloq-form-panel" data-sherloq-set="<?php echo isset( $form_settings['name'] ) ? 'true' : 'false'; ?>">
  <table class="form-table">
    <tbody>
      <?php foreach ( $model as $item ): ?>
        <tr>
          <th scope="row">
            <label for="sherloq-feed-<?php echo $item['name']; ?>"><?php echo $item['title']; ?></label>
          </th>
          <td>
            <select id="sherloq-field-<?php echo $item['name']; ?>" name="sherloq-feed[<?php echo $item['name']; ?>]">
              <?php foreach ( $available as $field ): ?>
                <?php if ( !empty( $form_settings[ $item['name'] ] ) && $form_settings[ $item['name'] ] == $field['name'] ) {
                  $selected = true;
                } else {
                  $selected = false;
                } 
                
                $option_content =  $field['name'] . ' (' . $field['type'] . ')';
                ?>
                <option value="<?php echo $field['name']; ?>"<?php if( $selected == true ) { echo 'selected="selected"'; } ?>><?php echo $option_content; ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
      <?php endforeach; ?>
        <tr>
          <td colspan="2">
            <p>The SherloQ Feed plugin can attempt to match the fields automatically. Be sure to check the that the matches are correct before saving.</p>
            <br>
            <button id="sherloq-match" class="button-secondary">Match Fields</button>
          </td>
        </tr>
    </tbody>
  </table>
</div>
