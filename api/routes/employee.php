<?php

/* ---- Route Callbacks ---- */
function vd_get_employees( WP_REST_Request $request  ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'employee',
		'post_status'      => 'publish',
  );

  if($user->post_type == 'client') return get_client_employees($user->ID);

	$employees = [];

	foreach (get_posts($args) as $post) {
		$employee = get_employee_by_id($post->ID);
		array_push($employees, $employee);
	}

	return new WP_REST_Response( $employees );
}

function vd_create_employee( WP_REST_Request $request ) {
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
		'post_type' => 'employee'
	);

	$post = get_post(wp_insert_post($postarr));
	update_post_meta($post->ID, 'phone', $request['phone']);
	update_post_meta($post->ID, 'email', $request['email']);
	update_post_meta($post->ID, 'type', $request['type']);
	update_post_meta($post->ID, "password", password_hash($request["password"], PASSWORD_DEFAULT));
	update_post_meta($post->ID, 'token', "NONE");

	if($request['media_id'])
		set_post_thumbnail($post->ID, $request['media_id']);

	$employee = get_employee_by_id($post->ID);

	return new WP_REST_Response( $employee );
}

function vd_update_employee( WP_REST_Request $request ) {
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
		$response = new WP_REST_Response( array('error' => 'No employee with that ID found') );
		$response->set_status(404);
		return $response;
	}

	wp_update_post(array('ID' => $post->ID, 'post_title' => $request['name']));

	update_post_meta($post->ID, 'phone', $request['phone']);
	update_post_meta($post->ID, 'email', $request['email']);
	update_post_meta($post->ID, 'type', $request['type']);

	if($request["password"]) {
		if($user->type == 'admin' || password_verify($request['currentPassword'], get_post_meta($user->ID, 'password', true))) {
			update_post_meta($post->ID, "password", password_hash($request["password"], PASSWORD_DEFAULT));
		}
		else {
			return new WP_REST_Response( array('error' => 'Incorrect password') );
		}

		
	}

	if($request['media_id'])
		set_post_thumbnail($post->ID, $request['media_id']);

	$employee = get_employee_by_id($post->ID);

	return new WP_REST_Response( $employee );
}

function vd_delete_employee( WP_REST_Request $request ) {
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
		$response = new WP_REST_Response( array('error' => 'No employee with that ID found') );
		$response->set_status(404);
		return $response;
	}

	$employee = get_employee_by_id($post->ID);

	wp_trash_post($post->ID);

	return new WP_REST_Response( $employee );
}

/* ---- Register Routes ---- */
add_action( 'rest_api_init', function () {
	register_rest_route( 'vd', '/employees', array(
		array(
			'methods' => 'GET',
			'callback' => 'vd_get_employees',
		),
		array(
			'methods' => 'POST',
			'callback' => 'vd_create_employee',
		),
	));

	register_rest_route( 'vd', '/employees/(?P<id>\d+)', array(
		array(
			'methods' => 'PATCH',
			'callback' => 'vd_update_employee',
		),
		array(
			'methods' => 'DELETE',
			'callback' => 'vd_delete_employee',
		),
	));
});

?>