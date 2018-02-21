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

function getUserFromToken( $token ) {
	$users = get_posts(
		array(
			'posts_per_page' => 1,
			'post_type' => ['employee', 'client'],
			'post_status' => 'publish',
			'meta_key' => 'token',
			'meta_value' => $token,
		)
	);

	return $users[0];
}

function getEmployeeById( $employee_id ) {
	return array(
		'id' => $employee_id,
		'name' => get_post($employee_id)->post_title,
		'phone' => get_post_meta($employee_id, 'phone', true),
		'email' => get_post_meta($employee_id, 'email', true),
		'img' => wp_get_attachment_image_src(get_post_thumbnail_id( $employee_id ))[0],
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
	);
}

add_action( 'rest_api_init', function () {
	// CORS
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter( 'rest_pre_serve_request', function( $value ) {
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, vd-token' );
		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
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

	register_rest_route( 'vd', '/login', array(
		'methods' => 'POST',
		'callback' => 'vd_api_login',
	));

	register_rest_route( 'vd', '/media', array(
		'methods' => 'POST',
		'callback' => 'vd_api_media',
	));

	register_rest_route( 'vd', '/me', array(
		'methods' => 'GET',
		'callback' => 'vd_get_me',
	));
});


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
			$token = JWT::encode(array('id' => $user->ID), 'secret_server_key');

			update_post_meta($user->ID, 'token', $token);
			return new WP_REST_Response( array('token' => $token) );
		}
	}

	return array('error' => 'Invalid login');
}

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

		if($employee_id != $user->ID && $client_id != $user->ID)
			continue;

		$report = array(
			'id' => $post->ID,
			'description' => $post->post_content,
			'img' => wp_get_attachment_image_src(get_post_thumbnail_id( $post->ID ))[0],
			'employee' => getEmployeeById($employee_id),
			'client' => getClientById($client_id),
		);

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

	if($request['media_id'])
		set_post_thumbnail($post->ID, $request['media_id']);

	$employee_id = (int) get_post_meta($post->ID, 'employee_id', true);
	$client_id = (int) get_post_meta($post->ID, 'client_id', true);

	$report = array(
		'id' => $post->ID,
		'description' => $post->post_content,
		'img' => wp_get_attachment_image_src(get_post_thumbnail_id( $post->ID ))[0],
		'employee' => array(
			'id' => $employee_id,
			'name' => get_post($employee_id)->post_title,
		),
		'client' => array(
			'id' => $client_id,
			'name' => get_post($client_id)->post_title,
		),
	);

	return new WP_REST_Response( $report );
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
	    
    return new WP_REST_Response( array('attachment_id' => $attachment_id) );	
}

function vd_get_me( WP_REST_Request $request ) {
	$user = getUserFromToken($request->get_header('vd-token'));

	if(!$user) {
		$response = new WP_REST_Response( array('error' => 'Please login') );
		$response->set_status(403);
		return $response;
	}

	if($user->post_type == 'employee') {
		$user = array(
			'id' => $user->ID,
			'name' => $user->post_title,
			'phone' => get_post_meta($user->ID, 'phone', true),
			'email' => get_post_meta($user->ID, 'email', true),
		);
	}

	if($user->post_type == 'client') {
		$user = array(
			'id' => $user->ID,
			'name' => $user->post_title,
			'contact_name' => get_post_meta($user->ID, 'contact_name', true),
			'contact_email' => get_post_meta($user->ID, 'contact_email', true),
			'contact_phone' => get_post_meta($user->ID, 'contact_phone', true),
			'address' => get_post_meta($user->ID, 'address', true),
		);
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
	}

	if($post->post_type == 'report') {
		update_post_meta($post->ID, "client_id", $_POST["client_id"]);
		update_post_meta($post->ID, "employee_id", $_POST["employee_id"]);
	}
}

?>
