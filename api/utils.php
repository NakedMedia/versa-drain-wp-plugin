<?php

function generate_random_string($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }

  return $randomString;
}

function get_user_from_token( $token ) {
  if(!$token) return null;

  $users = get_posts(
    array(
      'posts_per_page' => -1,
      'post_type' => ['employee', 'client'],
      'post_status' => 'publish',
    )
  );

  foreach ($users as $user) {
    if($token == get_post_meta($user->ID, 'token', true))
      return $user;
  }

  return null;
}

function get_employee_by_id( $employee_id ) {
  return array(
    'id' => $employee_id,
    'name' => get_post($employee_id)->post_title,
    'phone' => get_post_meta($employee_id, 'phone', true),
    'email' => get_post_meta($employee_id, 'email', true),
    'img' => wp_get_attachment_image_src(get_post_thumbnail_id( $employee_id ))[0],
    'type' => get_post_meta($employee_id, 'type', true),
  );
}

function get_client_by_id( $client_id ) {
  return array(
    'id' => $client_id,
    'name' => get_post($client_id)->post_title,
    'img' => wp_get_attachment_image_src(get_post_thumbnail_id( $client_id ))[0],
    'type' => 'client'
  );
}

function get_location_by_id( $location_id ) {
  return array(
    'id' => $location_id,
    'name' => get_post($location_id)->post_title,
    'address' => get_post_meta($location_id, 'address', true),
    'client_id' => get_post_meta($location_id, 'client_id', true),
    'email' => get_post_meta($location_id, 'email', true),
    'phone' => get_post_meta($location_id, 'phone', true)
  );
}

function get_report_by_id( $report_id ) {
  $employee_id = (int) get_post_meta($report_id, 'employee_id', true);
  $client_id = (int) get_post_meta($report_id, 'client_id', true);

  $media_ids = array();
  $media_urls = array();

  if(get_post_meta($report_id, 'media_ids', true) != null) {
    $media_ids = array_map('intval', explode(";", get_post_meta($report_id, 'media_ids', true)));

    foreach($media_ids as $media_id) 
      array_push($media_urls, wp_get_attachment_image_src($media_id, 'large')[0]);
  }

  $post = get_post($report_id);

  return array(
    'id' => $report_id,
    'description' => $post->post_content,
    'date' => $post->post_date,
    'media_ids' => $media_ids,
    'media_urls' => $media_urls,
    'employee' => get_employee_by_id($employee_id),
    'client' => get_client_by_id($client_id),
  );
}

?>