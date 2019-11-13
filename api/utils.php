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
  $client_id = (int) get_post_meta($location_id, 'client_id', true);

  return array(
    'id' => $location_id,
    'name' => get_post($location_id)->post_title,
    'address' => get_post_meta($location_id, 'address', true),
    'client' => get_client_by_id($client_id),
    'email' => get_post_meta($location_id, 'email', true),
    'phone' => get_post_meta($location_id, 'phone', true),
    'contact_name' => get_post_meta($location_id, 'contact_name', true),
  );
}

function get_report_by_id( $report_id ) {
  $employee_id = (int) get_post_meta($report_id, 'employee_id', true);
  $client_id = (int) get_post_meta($report_id, 'client_id', true);
  $location_id = (int) get_post_meta($report_id, 'location_id', true);

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
    'location' => get_location_by_id($location_id)
  );
}

function get_client_employees( $client_id ) {
  $employee_map = array();

  $args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'report',
		'post_status'      => 'publish',
	);

	foreach (get_posts($args) as $post) {
		$report_client_id = (int) get_post_meta($post->ID, 'client_id', true);

    // Don't return reports that don't belong the client
    if($client_id != $report_client_id)
      continue;

		$report = get_report_by_id($post->ID);

		$employee_map[$report['employee']['id']] = $report['employee'];
  }
  
  $employees = [];

  foreach ($employee_map as $key => $val) {
    array_push($employees, $val);
  }

  return $employees;
}

?>