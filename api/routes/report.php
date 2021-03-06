<?php

function send_email($to_email, $to_name, $report) {
	$emailto = $to_email;
	$toname = $to_name;
	$emailfrom = 'info@versadrain.com';
	$fromname = 'VersaTrack';
	$subject = 'New Report Submitted For ' . $report['client']['name'];
	$messagebody = 
		'Client ID: ' . $report['client']['id'] . "<br/>".
    'Client Name: ' . $report['client']['name'] . "<br/>".
    'Location: ' . $report['location']['name'] . "<br/>".
    'Address : ' . $report['location']['address'] . "<br/><br/>".
		'Technician Name: ' . $report['employee']['name'] . "<br/><br/>".
		'Date: ' . get_the_date('l, F j, Y', $report['id']) . "<br/>".
		'Time: ' . get_the_date('g:i A', $report['id']) . " EST<br/><br/>".
		'Job Notes: <br/>' . $report['description'] ."<br/>";
	$headers = 
		'Return-Path: ' . $emailfrom . "\r\n" . 
		'From: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" . 
		'X-Priority: 3' . "\r\n" . 
		'X-Mailer: PHP ' . phpversion() .  "\r\n" . 
		'Reply-To: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" .
		'MIME-Version: 1.0' . "\r\n" . 
		'Content-Transfer-Encoding: 8bit' . "\r\n" . 
		'Content-Type: text/html; charset=UTF-8' . "\r\n";
	
	$attachments = array();

	foreach ($report['media_ids'] as $media_id)
		array_push($attachments, get_attached_file($media_id, false));

	return wp_mail($emailto, $subject, $messagebody, $headers, $attachments);
}

/* ---- Route Callbacks ---- */
function vd_get_user_reports( WP_REST_Request $request  ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'report',
		'post_status'      => 'publish',
	);

	$reports = [];

	foreach (get_posts($args) as $post) {
		$employee_id = (int) get_post_meta($post->ID, 'employee_id', true);
		$client_id = (int) get_post_meta($post->ID, 'client_id', true);

    // Don't return reports that don't belong to logged in client
    if($user->post_type === 'client' && $user->ID != $client_id)
      continue;

		$report = get_report_by_id($post->ID);

		array_push($reports, $report);
	}

	return new WP_REST_Response( $reports );
}

function vd_create_report( WP_REST_Request $request ) {
  $user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
  }
  
  if($user->type !== 'admin' && $user->ID !== $request['employee_id']) {
    $response = new WP_REST_Response( array('error' => 'You are not authorized to create this report') );
		$response->set_status(403);
		return $response;
  }

	$postarr = array(
		'post_content' => $request['description'],
		'post_status' => 'publish',
		'post_type' => 'report'
	);

	$post = get_post(wp_insert_post($postarr));
	update_post_meta($post->ID, 'client_id', $request['client_id'] ?: $user->ID);
	update_post_meta($post->ID, 'employee_id', $request['employee_id'] ?: $user->ID);
	update_post_meta($post->ID, 'location_id', $request['location_id']);

	if(count($request['media_ids']) > 0) {
		$media_ids = implode(";", $request['media_ids']);
		update_post_meta($post->ID, 'media_ids', $media_ids);
	}

	else {
    update_post_meta($post->ID, 'media_ids', null);
  }

  $report = get_report_by_id($post->ID);

  $default_email = get_option('vd_default_email');

  $location_email = $report['location']['email'];
  $client_name = $report['client']['name'];
  
  if($request['notify'] && $location_email && $client_name) {
    send_email($report['location']['email'], $report['client']['name'], $report);
  }

  if($default_email) {
    send_email($default_email, 'VersaDrain Techs', $report);
  }

	return new WP_REST_Response( $report );
}

function vd_update_report( WP_REST_Request $request ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	if($user->type != 'admin') {
		$response = new WP_REST_Response( array('error' => 'You must be an admin to access this data') );
		$response->set_status(403);
		return $response;
	}

	$post = get_post($request->get_param('id'));

	if(!$post) {
		$response = new WP_REST_Response( array('error' => 'No report with that ID found') );
		$response->set_status(404);
		return $response;
	}

	wp_update_post(array('ID' => $post->ID, 'post_content' => $request['description']));

	update_post_meta($post->ID, 'client_id', $request['client_id']);
	update_post_meta($post->ID, 'employee_id', $request['employee_id']);
	update_post_meta($post->ID, 'location_id', $request['location_id']);


	if(count($request['media_ids']) > 0) {
		$media_ids = implode(";", $request['media_ids']);
		update_post_meta($post->ID, 'media_ids', $media_ids);
	}

	else 
		update_post_meta($post->ID, 'media_ids', null);

	$report = get_report_by_id($post->ID);

	return new WP_REST_Response( $report );
}

function vd_delete_report( WP_REST_Request $request ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	if($user->type != 'admin') {
		$response = new WP_REST_Response( array('error' => 'You must be an admin to access this data') );
		$response->set_status(403);
		return $response;
	}

	$post = get_post($request->get_param('id'));

	if(!$post) {
		$response = new WP_REST_Response( array('error' => 'No report with that ID found') );
		$response->set_status(404);
		return $response;
	}

	$report = get_report_by_id($post->ID);

	wp_trash_post($post->ID);

	return new WP_REST_Response( $report );
}

/* ---- Register Routes ---- */
add_action( 'rest_api_init', function () {
	register_rest_route( 'vd', '/reports', array(
		array(
			'methods' => 'GET',
			'callback' => 'vd_get_user_reports',
		),
		array(
			'methods' => 'POST',
			'callback' => 'vd_create_report',
		),
	));

	register_rest_route( 'vd', '/reports/(?P<id>\d+)', array(
		array(
			'methods' => 'PATCH',
			'callback' => 'vd_update_report',
		),
		array(
			'methods' => 'DELETE',
			'callback' => 'vd_delete_report',
		),
	));
});

?>