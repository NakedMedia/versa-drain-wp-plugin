<?php 

/**
 * @param $postId: ID of post to get custom fields
 * @param $fields: String array containing list of fields to grab
 */
function get_custom_fields($postId, $fields) {
  $custom = get_post_custom($postId);

  $custom_fields = array();

  foreach ($fields as $field) {
    $custom_fields[$field] = isset($custom[$field]) ? $custom[$field][0] : null; 
  }

  return $custom_fields;
}

/**
 * @param $postId: ID of post to get custom fields
 * @param $fields: Key value pair of custom field keys to their new values
 */
function set_custom_fields($postId, $fields) {
  foreach ($fields as $key => $value) {
    update_post_meta($postId, $key, $value);
  }
}

?>