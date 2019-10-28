<?php 

/* ---- Route Callbacks ---- */
function vd_api_media( WP_REST_Request $request ) {
	$user = get_user_from_token($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	$file = $request->get_file_params();

	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

	if (empty($file)) {
		$response = new WP_REST_Response( array('error' => 'No file provided') );
		$response->set_status(403);
		return $response;
	}

	$attachment_id = media_handle_upload( 'file', 0 );
		
	return new WP_REST_Response( array('media_id' => $attachment_id, 'media_url' => wp_get_attachment_image_src($attachment_id, 'large')[0]) );	
}

/* ---- Register Routes ---- */
add_action( 'rest_api_init', function () {
	register_rest_route( 'vd', '/media', array(
		'methods' => 'POST',
		'callback' => 'vd_api_media',
	));
});

?>