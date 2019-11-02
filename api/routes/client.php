<?php

/* ---- Route Callbacks ---- */
function vd_get_clients( WP_REST_Request $request  ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
  }
  
  // Only return own client data if user is a client
  if($user->post_type == 'client') {
    return array( get_client_by_id($user->ID) );
  }

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'client',
		'post_status'      => 'publish',
	);

	$clients = [];

	foreach (get_posts($args) as $post) {
		$client = get_client_by_id($post->ID);
		array_push($clients, $client);
	}

	return new WP_REST_Response( $clients );
}

function vd_create_client( WP_REST_Request $request ) {
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

	$postarr = array(
		'import_id' => $request['id'],
		'post_title' => $request['name'],
		'post_status' => 'publish',
		'post_type' => 'client'
	);

	$post = get_post(wp_insert_post($postarr));
	update_post_meta($post->ID, "password", password_hash($request["password"], PASSWORD_DEFAULT));
	update_post_meta($post->ID, 'token', "NONE");

	if($request['media_id'])
		set_post_thumbnail($post->ID, $request['media_id']);

	$client = get_client_by_id($post->ID);

	return new WP_REST_Response( $client );
}

function vd_update_client( WP_REST_Request $request ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	if($user->ID != $request['id'] && $user->type != 'admin') {
		$response = new WP_REST_Response( array('error' => 'You must be an admin to access this data') );
		$response->set_status(403);
		return $response;
	}

	$post = get_post($request->get_param('id'));

	if(!$post) {
		$response = new WP_REST_Response( array('error' => 'No client with that ID found') );
		$response->set_status(404);
		return $response;
	}

	wp_update_post(array('ID' => $post->ID, 'post_title' => $request['name']));

	if($request["password"])
		update_post_meta($post->ID, "password", password_hash($request["password"], PASSWORD_DEFAULT));

	if($request['media_id'])
		set_post_thumbnail($post->ID, $request['media_id']);

	$client = get_client_by_id($post->ID);

	return new WP_REST_Response( $client );
}

function vd_delete_client( WP_REST_Request $request ) {
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
		$response = new WP_REST_Response( array('error' => 'No client with that ID found') );
		$response->set_status(404);
		return $response;
	}

	$client = get_client_by_id($post->ID);

	wp_trash_post($post->ID);

	return new WP_REST_Response( $client );
}

/* ---- Register Routes ---- */
add_action( 'rest_api_init', function () {
  register_rest_route( 'vd', '/clients', array(
		array(
			'methods' => 'GET',
			'callback' => 'vd_get_clients',
		),
		array(
			'methods' => 'POST',
			'callback' => 'vd_create_client',
		),
	));

	register_rest_route( 'vd', '/clients/(?P<id>\d+)', array(
		array(
			'methods' => 'PATCH',
			'callback' => 'vd_update_client',
		),
		array(
			'methods' => 'DELETE',
			'callback' => 'vd_delete_client',
		),
	));
});

?>