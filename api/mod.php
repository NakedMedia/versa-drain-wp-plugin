<?php

/* ---- RESTFul API module ---- */
add_action( 'rest_api_init', function () {
  // Enable COORS
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter( 'rest_pre_serve_request', function( $value ) {
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, vd-token' );
		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PATCH, DELETE' );
		header( 'Access-Control-Allow-Credentials: true' );

		return $value;
	});
});

require 'jwt_helper.php';
require 'utils.php';

require 'routes/auth.php';
require 'routes/client.php';
require 'routes/employee.php';
require 'routes/media.php';
require 'routes/report.php';
require 'routes/user.php';

?>