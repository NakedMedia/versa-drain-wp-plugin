<?php

/* ---- Route Callbacks ---- */
function vd_get_me( WP_REST_Request $request ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	if($user->post_type == 'employee') {
		$user = get_employee_by_id($user->ID);
	}

	if($user->post_type == 'client') {
		$user = get_client_by_id($user->ID);
	}


	return new WP_REST_Response( $user );
}

/* ---- Register Routes ---- */
add_action( 'rest_api_init', function () {
	register_rest_route( 'vd', '/me', array(
		'methods' => 'GET',
		'callback' => 'vd_get_me',
	));

	register_rest_route( 'vd', '/password', array(
		'methods' => 'POST',
		'callback' => 'vd_change_password',
	));

	register_rest_route( 'vd', '/profile-picture', array(
		'methods' => 'POST',
		'callback' => 'vd_change_profile_picture',
	));
});

?>