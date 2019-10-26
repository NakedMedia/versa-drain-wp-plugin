<?php

/* ---- Route Callbacks ---- */
function vd_api_login( WP_REST_Request $request  ) {
	if(!$request['id'] || !$request['password'])
		return array('error' => 'Please provide a user ID and password');

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => ['employee', 'client'],
		'post_status'      => 'publish',
	);

	foreach (get_posts($args) as $user) {
		if(
			$user->ID == $request['id'] && 
			password_verify($request['password'], get_post_meta($user->ID, 'password', true))
		) {
			$token = JWT::encode(array('id' => $user->ID), generateRandomString(5));

			update_post_meta($user->ID, 'token', $token);
			return new WP_REST_Response( array('token' => $token) );
		}
	}

	return array('error' => 'Invalid login');
}

function vd_api_logout( WP_REST_Request $request  ) {
	$user = getUserFromToken($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	update_post_meta($user->ID, 'token', "NONE");

	return new WP_REST_Response( array('message' => 'Successfully logged out') );
}

/* ---- Register Routes ----*/
add_action( 'rest_api_init', function () {
	register_rest_route( 'vd', '/login', array(
		'methods' => 'POST',
		'callback' => 'vd_api_login',
	));

	register_rest_route( 'vd', '/logout', array(
		'methods' => 'GET',
		'callback' => 'vd_api_logout',
	));
});

?>