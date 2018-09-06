<?php

/*
 *
 * Plugin Name: Versa Drain Portal
 * Description: This plugin creates two new sections in WordPress, a Clients section and an Employees section.
 * Author: Alessandro Vecchi
 *
 */

/* Custom Post Type ------------------- */

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require('jwt_helper.php');

add_action('admin_head', 'vd_admin_styles');

function vd_admin_styles() {
  echo '<link rel="stylesheet" href="'.plugins_url('css/admin.css', __FILE__).'" type="text/css" media="all" />';
}


/* ---- Create Custom Post Types ---- */
function client_create_type() {
	$args = array(
	      	'label' => 'Clients',
	      	'labels' => array(
	      		'add_new_item' => 'Add New Client',
	      		'edit_item' => 'Edit Client',
	      		'search_items' => 'Search Clients',
	      		'not_found' => 'No Clients Found',
	      		'singular_name' => 'Client'
	      	),
	       	'public' => false,
	       	'show_ui' => true,
	       	'capability_type' => 'post',
	       	'show_in_rest' => false,
	       	'hierarchical' => false,
	       	'rewrite' => array('slug' => 'clients'),
			'menu_icon'  => 'dashicons-store',
	        'query_var' => true,
	        'supports' => array(
	            'title',
	            'thumbnail')
	    );
	register_post_type( 'client' , $args );
}

function employee_create_type() {
	$args = array(
	      	'label' => 'Employees',
	      	'labels' => array(
	      		'add_new_item' => 'Add New Employee',
	      		'edit_item' => 'Edit Employee',
	      		'search_items' => 'Search Employees',
	      		'not_found' => 'No Employees Found',
	      		'singular_name' => 'Employee'
	      	),
	       	'public' => false,
	       	'show_ui' => true,
	       	'capability_type' => 'post',
	       	'show_in_rest' => false,
	       	'hierarchical' => false,
	       	'rewrite' => array('slug' => 'employees'),
			'menu_icon'  => 'dashicons-nametag',
	        'query_var' => true,
	        'supports' => array(
	            'title',
	            'thumbnail')
	    );
	register_post_type( 'employee' , $args );
}

function report_create_type() {
	$args = array(
	      	'label' => 'Reports',
	      	'labels' => array(
	      		'add_new_item' => 'Create New Report',
	      		'edit_item' => 'Edit Report',
	      		'search_items' => 'Search Reports',
	      		'not_found' => 'No Reports Found',
	      		'singular_name' => 'Report'
	      	),
	       	'public' => false,
	       	'show_ui' => true,
	       	'capability_type' => 'post',
	       	'show_in_rest' => false,
	       	'hierarchical' => false,
	       	'rewrite' => array('slug' => 'reports'),
			'menu_icon'  => 'dashicons-clipboard',
	        'query_var' => true,
	        'supports' => array(
	            'editor',
	            'thumbnail')
	    );
	register_post_type( 'report' , $args );
}

/* ---- Modify Title Texts ---- */
function wpb_change_title_text( $title ){
     $screen = get_current_screen();
  
     if  ( $screen->post_type == 'client') {
          $title = 'Enter client name';
     }

     if  ( $screen->post_type == 'employee') {
          $title = 'Enter employee name';
     }
  
     return $title;
}

/* ---- Define Admin Page Columns ---- */
function vd_edit_client_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'id' => __('ID'),
		'title' => __( 'Client' ),
		'address' => __('Business Address'),
		'contact_email' => __('Contact Email'),
		'contact_phone' => __('Contact Phone'),
	);

	return $columns;
}

function vd_edit_employee_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'id' => __('ID'),
		'title' => __( 'Employee' ),
		'email' => __('Email'),
		'phone' => __('Phone'),
		'type' => __('Type'),
	);

	return $columns;
}

function vd_edit_report_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'id' => __('ID'),
		'description' => __( 'Description' ),
		'client' => __( 'Client' ),
		'employee' => __('Employee'),
	);

	return $columns;
}

/* ---- Get Column Data ---- */
function vd_manage_client_columns( $column, $post_id ) {
	global $post;

	$custom = get_post_custom($post_id);

	switch( $column ) {

		case 'id' :
			echo $post_id;
			break;

		case 'address':
			echo $custom['address'][0] ?: '--';
			break;

		case 'contact_email':
			echo $custom['contact_email'][0] ?: '--';
			break;

		case 'contact_phone':
			echo $custom['contact_phone'][0] ?: '--';
			break;

		default :
			break;
	}
}

function vd_manage_employee_columns( $column, $post_id ) {
	global $post;

	$custom = get_post_custom($post_id);

	switch( $column ) {

		case 'id' :
			echo $post_id;
			break;

		case 'email':
			echo $custom['email'][0] ?: '--';
			break;

		case 'phone':
			echo $custom['phone'][0] ?: '--';
			break;

		case 'type':
			echo ucfirst($custom['type'][0]);
			break;

		default :
			break;
	}
}

function vd_manage_report_columns( $column, $post_id ) {
	global $post;

	$custom = get_post_custom($post_id);

	switch( $column ) {

		case 'id' :
			echo $post_id;
			break;

		case 'description':
			echo '<a href='.get_edit_post_link($post->ID).'>'.$post->post_content.'</a>';
			break;

		case 'client':
			echo get_post($custom['client_id'][0])->post_title;
			break;

		case 'employee':
			echo get_post($custom['employee_id'][0])->post_title;
			break;

		default :
			break;
	}
}

/* ---- Select Sortable Columns ---- */
function vd_sortable_client_column( $columns ) {
    $columns['id'] = 'id';
 
    return $columns;
}

function vd_sortable_employee_column( $columns ) {
    $columns['id'] = 'id';
 
    return $columns;
}

function vd_sortable_report_column( $columns ) {
    $columns['id'] = 'id';
    $columns['client'] = 'client';
    $columns['employee'] = 'employee';
 
    return $columns;
}

/* ---- Modify Status Messages ---- */
function vd_client_updated_messages( $messages ) {
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	$messages['client'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => 'Client updated.',
		2  => 'Custom field updated.',
		3  => 'Custom field deleted.',
		4  => 'Client updated.',
		5  => 'Error: passwords do not match',
		6  => 'Client created.',
		7  => 'Client saved.',
		8  => 'Client submitted.',
		10 => 'Client draft updated.'
	);

	return $messages;
}

function vd_employee_updated_messages( $messages ) {
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	$messages['employee'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => 'Employee updated.',
		2  => 'Custom field updated.',
		3  => 'Custom field deleted.',
		4  => 'Employee updated.',
		5  => 'Error: passwords do not match',
		6  => 'Employee created.',
		7  => 'Employee saved.',
		8  => 'Employee submitted.',
		10 => 'Employee draft updated.'
	);

	return $messages;
}

function vd_report_updated_messages( $messages ) {
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	$messages['report'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => 'Report updated.',
		2  => 'Custom field updated.',
		3  => 'Custom field deleted.',
		4  => 'Report updated.',
		5  => 'Error: passwords do not match',
		6  => 'Report created.',
		7  => 'Report saved.',
		8  => 'Report submitted.',
		10 => 'Report draft updated.'
	);

	return $messages;
}

/* ---- Call Functions ---- */

// Clients
add_action('init', 'client_create_type');
add_action("admin_init", "client_init");

add_filter( 'manage_edit-client_columns', 'vd_edit_client_columns' );
add_action( 'manage_client_posts_custom_column', 'vd_manage_client_columns', 10, 2 );
add_filter( 'manage_edit-client_sortable_columns', 'vd_sortable_client_column' );
add_filter( 'post_updated_messages', 'vd_client_updated_messages' );


// Employees
add_action('init', 'employee_create_type');
add_action("admin_init", "employee_init");

add_filter( 'manage_edit-employee_columns', 'vd_edit_employee_columns' );
add_action( 'manage_employee_posts_custom_column', 'vd_manage_employee_columns', 10, 2 );
add_filter( 'manage_edit-employee_sortable_columns', 'vd_sortable_employee_column' );
add_filter( 'post_updated_messages', 'vd_employee_updated_messages' );


// Reports
add_action('init', 'report_create_type');
add_action("admin_init", "report_init");

add_filter( 'manage_edit-report_columns', 'vd_edit_report_columns' );
add_action( 'manage_report_posts_custom_column', 'vd_manage_report_columns', 10, 2 );
add_filter( 'manage_edit-report_sortable_columns', 'vd_sortable_report_column' );
add_filter( 'post_updated_messages', 'vd_report_updated_messages' );


add_filter( 'enter_title_here', 'wpb_change_title_text' );
add_action('save_post', 'cpt_save', 20, 2);

/*----- API Route Registration -----*/

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getUserFromToken( $token ) {
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

function getEmployeeById( $employee_id ) {
	return array(
		'id' => $employee_id,
		'name' => get_post($employee_id)->post_title,
		'phone' => get_post_meta($employee_id, 'phone', true),
		'email' => get_post_meta($employee_id, 'email', true),
		'img' => wp_get_attachment_image_src(get_post_thumbnail_id( $employee_id ))[0],
		'type' => get_post_meta($employee_id, 'type', true),
	);
}

function getClientById( $client_id ) {
	return array(
		'id' => $client_id,
		'name' => get_post($client_id)->post_title,
		'contact_name' => get_post_meta($client_id, 'contact_name', true),
		'contact_phone' => get_post_meta($client_id, 'contact_phone', true),
		'contact_email' => get_post_meta($client_id, 'contact_email', true),
		'address' => get_post_meta($client_id, 'address', true),
		'img' => wp_get_attachment_image_src(get_post_thumbnail_id( $client_id ))[0],
		'type' => 'client'
	);
}

function getReportById( $report_id ) {
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
		'employee' => getEmployeeById($employee_id),
		'client' => getClientById($client_id),
	);
}

add_action( 'rest_api_init', function () {
	// CORS
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter( 'rest_pre_serve_request', function( $value ) {
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, vd-token' );
		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PATCH, DELETE' );
		header( 'Access-Control-Allow-Credentials: true' );

		return $value;
	});

	// Routes
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

	register_rest_route( 'vd', '/login', array(
		'methods' => 'POST',
		'callback' => 'vd_api_login',
	));

	register_rest_route( 'vd', '/logout', array(
		'methods' => 'GET',
		'callback' => 'vd_api_logout',
	));

	register_rest_route( 'vd', '/media', array(
		'methods' => 'POST',
		'callback' => 'vd_api_media',
	));

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


/* ---- General Routes ---- */
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

function vd_api_media( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

/* ---- Reports Routes ---- */
function vd_get_user_reports( WP_REST_Request $request  ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

		if($employee_id != $user->ID && $client_id != $user->ID && $user->type != 'admin')
			continue;

		$report = getReportById($post->ID);

		array_push($reports, $report);
	}

	return new WP_REST_Response( $reports );
}

function vd_create_report( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
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

	if(count($request['media_ids']) > 0) {
		$media_ids = implode(";", $request['media_ids']);
		update_post_meta($post->ID, 'media_ids', $media_ids);
	}

	else 
		update_post_meta($post->ID, 'media_ids', null);

	$report = getReportById($post->ID);

	$emailto = $report['client']['contact_email'];
	$toname = $report['client']['name'];
	$emailfrom = 'info@versadrain.com';
	$fromname = 'Versa Drain';
	$subject = 'New Report Submitted For ' . $report['client']['name'];
	$messagebody = 
		'<p>Client ID: ' . $report['client']['id'] . '<br/>'.
		'<p>Client Name: ' . $report['client']['name'] . '<br/>'.
		'Technician Name: ' . $report['employee']['name'] . '<br/>'.
		'Date: ' . get_the_date('l, F j, Y', $report['id']) . '<br/>'.
		'Time: ' . get_the_date('g:i A', $report['id']) . '</p>'.
		'<p>Job Notes: <br/>' . $report['description'] .'</p>';
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

	wp_mail($emailto, $subject, $messagebody, $headers, $attachments);

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'employee',
		'post_status'      => 'publish',
	);

	$admins = [];

	foreach (get_posts($args) as $post) {
		$employee = getEmployeeById($post->ID);
		
		if($employee["type"] == 'admin')
			array_push($admins, $employee);
	}

	foreach ($admins as $admin)
			wp_mail($admin["email"], $subject, $messagebody, $headers, $attachments);

	return new WP_REST_Response( $report );
}

function vd_update_report( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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


	if(count($request['media_ids']) > 0) {
		$media_ids = implode(";", $request['media_ids']);
		update_post_meta($post->ID, 'media_ids', $media_ids);
	}

	else 
		update_post_meta($post->ID, 'media_ids', null);

	$report = getReportById($post->ID);

	return new WP_REST_Response( $report );
}

function vd_delete_report( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

	$report = getReportById($post->ID);

	wp_trash_post($post->ID);

	return new WP_REST_Response( $report );
}

/* ---- Clients Routes ---- */
function vd_get_clients( WP_REST_Request $request  ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'client',
		'post_status'      => 'publish',
	);

	$clients = [];

	foreach (get_posts($args) as $post) {
		$client = getClientById($post->ID);
		array_push($clients, $client);
	}

	return new WP_REST_Response( $clients );
}

function vd_create_client( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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
	update_post_meta($post->ID, 'address', $request['address']);
	update_post_meta($post->ID, 'contact_name', $request['contact_name']);
	update_post_meta($post->ID, 'contact_email', $request['contact_email']);
	update_post_meta($post->ID, 'contact_phone', $request['contact_phone']);
	update_post_meta($post->ID, "password", password_hash($request["password"], PASSWORD_DEFAULT));
	update_post_meta($post->ID, 'token', "NONE");

	if($request['media_id'])
		set_post_thumbnail($post->ID, $request['media_id']);

	$client = getClientById($post->ID);

	return new WP_REST_Response( $client );
}

function vd_update_client( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

	update_post_meta($post->ID, 'address', $request['address']);
	update_post_meta($post->ID, 'contact_name', $request['contact_name']);
	update_post_meta($post->ID, 'contact_email', $request['contact_email']);
	update_post_meta($post->ID, 'contact_phone', $request['contact_phone']);

	if($request["password"])
		update_post_meta($post->ID, "password", password_hash($request["password"], PASSWORD_DEFAULT));

	if($request['media_id'])
		set_post_thumbnail($post->ID, $request['media_id']);

	$client = getClientById($post->ID);

	return new WP_REST_Response( $client );
}

function vd_delete_client( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

	$client = getClientById($post->ID);

	wp_trash_post($post->ID);

	return new WP_REST_Response( $client );
}

/* ---- Employees Routes ---- */
function vd_get_employees( WP_REST_Request $request  ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

	$args = array(
		'posts_per_page'   => -1,
		'post_type'        => 'employee',
		'post_status'      => 'publish',
	);

	$employees = [];

	foreach (get_posts($args) as $post) {
		$employee = getEmployeeById($post->ID);
		array_push($employees, $employee);
	}

	return new WP_REST_Response( $employees );
}

function vd_create_employee( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

	$employee = getEmployeeById($post->ID);

	return new WP_REST_Response( $employee );
}

function vd_update_employee( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

	$employee = getEmployeeById($post->ID);

	return new WP_REST_Response( $employee );
}

function vd_delete_employee( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

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

	$employee = getEmployeeById($post->ID);

	wp_trash_post($post->ID);

	return new WP_REST_Response( $employee );
}

/* ---- User Routes ---- */
function vd_get_me( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	if($user->post_type == 'employee') {
		$user = getEmployeeById($user->ID);
	}

	if($user->post_type == 'client') {
		$user = getClientById($user->ID);
	}


	return new WP_REST_Response( $user );
}

/*------ Metabox Functions --------*/

// Clients
function client_init() {
	global $current_user;

	add_meta_box("client-meta", "Client Info", "client_meta", "client", "normal", "high");
	add_meta_box("password-meta", "Password", "meta_password", "client", "normal", "high");
}

function client_meta() {
	global $post;
    $custom = get_post_custom($post->ID);

    include_once('views/client.php');
}


// Employees
function employee_init() {
	global $current_user;

	add_meta_box("employee-meta", "Employee Info", "employee_meta", "employee", "normal", "high");
	add_meta_box("password-meta", "Password", "meta_password", "employee", "normal", "high");
}

function employee_meta() {
	global $post;
    $custom = get_post_custom($post->ID);

    include_once('views/employee.php');
}


// Reports
function report_init() {
	global $current_user;

	add_meta_box("report-meta", "Report Info", "report_meta", "report", "normal", "high");
}

function report_meta() {
	global $post;
    $custom = get_post_custom($post->ID);

    include_once('views/report.php');
}


function meta_password() {
	global $post;
    $custom = get_post_custom($post->ID);

    include_once('views/password.php');
}

function cpt_save($post_id, $post) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' ) return $post_id;
    if ( $post->post_type != 'employee' && $post->post_type != 'client' && $post->post_type != 'report' ) return $post_id;

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $password_match = $password == '' || $password == $confirm_password;

    if ( ( isset( $_POST['publish'] ) || isset( $_POST['save'] ) ) && $_POST['post_status'] == 'publish' ) {
        if ( !$password_match ) {
            global $wpdb;
            $wpdb->update( $wpdb->posts, array( 'post_status' => 'pending' ), array( 'ID' => $post_id ) );

            add_filter( 'redirect_post_location', create_function( '$location','return add_query_arg("message", "5", $location);' ) );
            return $post_id;
        }
    }

	if(array_key_exists('password', $_POST) && $_POST['password'] != '')
		update_post_meta($post->ID, "password", password_hash($_POST["password"], PASSWORD_DEFAULT));	

	if($post->post_type == 'client') {
		update_post_meta($post->ID, "address", $_POST["address"]);
		update_post_meta($post->ID, "contact_name", $_POST["contact_name"]);
		update_post_meta($post->ID, "contact_email", $_POST["contact_email"]);
		update_post_meta($post->ID, "contact_phone", $_POST["contact_phone"]);
	}

	if($post->post_type == 'employee') {
		update_post_meta($post->ID, "email", $_POST["email"]);
		update_post_meta($post->ID, "phone", $_POST["phone"]);
		update_post_meta($post->ID, "type", $_POST["type"]);
	}

	if($post->post_type == 'report') {
		update_post_meta($post->ID, "client_id", $_POST["client_id"]);
		update_post_meta($post->ID, "employee_id", $_POST["employee_id"]);
	}
}

?>
