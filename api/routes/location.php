<?php

/* ---- Route Callbacks ---- */
function vd_get_locations( WP_REST_Request $request  ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'location',
		'post_status'      => 'publish',
	);

	$locations = [];

	foreach (get_posts($args) as $post) {
		$location = get_location_by_id($post->ID);
		array_push($locations, $location);
	}

	return new WP_REST_Response( $locations );
}

function vd_create_location( WP_REST_Request $request ) {
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
		'post_type' => 'location'
	);

	$post = get_post(wp_insert_post($postarr));
	update_post_meta($post->ID, 'address', $request['address']);
  update_post_meta($post->ID, 'client_id', $request['client_id']);
  update_post_meta($post->ID, 'email', $request['email']);
  update_post_meta($post->ID, 'phone', $request['phone']);

	$location = get_location_by_id($post->ID);

	return new WP_REST_Response( $location );
}

function vd_update_location( WP_REST_Request $request ) {
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
		$response = new WP_REST_Response( array('error' => 'No location with that ID found') );
		$response->set_status(404);
		return $response;
	}

	wp_update_post(array('ID' => $post->ID, 'post_title' => $request['name']));

	update_post_meta($post->ID, 'address', $request['address']);
	update_post_meta($post->ID, 'client_id', $request['client_id']);
  update_post_meta($post->ID, 'email', $request['email']);
  update_post_meta($post->ID, 'phone', $request['phone']);

	$location = get_location_by_id($post->ID);

	return new WP_REST_Response( $location );
}

function vd_delete_location( WP_REST_Request $request ) {
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
		$response = new WP_REST_Response( array('error' => 'No location with that ID found') );
		$response->set_status(404);
		return $response;
	}

	$location = get_location_by_id($post->ID);

	wp_trash_post($post->ID);

	return new WP_REST_Response( $location );
}

/* ---- Register Routes ---- */
add_action( 'rest_api_init', function () {
  register_rest_route( 'vd', '/locations', array(
		array(
			'methods' => 'GET',
			'callback' => 'vd_get_locations',
		),
		array(
			'methods' => 'POST',
			'callback' => 'vd_create_location',
		),
	));

	register_rest_route( 'vd', '/locations/(?P<id>\d+)', array(
		array(
			'methods' => 'PATCH',
			'callback' => 'vd_update_location',
		),
		array(
			'methods' => 'DELETE',
			'callback' => 'vd_delete_location',
		),
	));
});

?>