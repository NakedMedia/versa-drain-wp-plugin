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

add_action('wp_enqueue_scripts', 'add_style');

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
	       	'show_in_rest' => true,
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
	       	'show_in_rest' => true,
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
		'employee_email' => __('Email'),
		'employee_phone' => __('Phone'),
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

		case 'employee_email':
			echo $custom['employee_email'][0] ?: '--';
			break;

		case 'employee_phone':
			echo $custom['employee_phone'][0] ?: '--';
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


add_filter( 'enter_title_here', 'wpb_change_title_text' );
add_action('save_post', 'cpt_save', 20, 2);

/*----- API Route Registration -----*/
// add_action( 'rest_api_init', function () {
// 	register_rest_route( 'news', '/sites', array(
// 		'methods' => 'GET',
// 		'callback' => 'get_all_sites',
// 	) );
// } );

// function get_all_sites( $data ) {
// 	$urls = explode(";", get_option('news_list_option'));

// 	return $urls;
// }

// /*----- API Meta Registration -----*/
// add_action( 'rest_api_init', 'api_register_approved' );
// add_action( 'rest_api_init', 'api_register_author' );
// add_action( 'rest_api_init', 'api_register_site_name' );
// add_action( 'rest_api_init', 'api_register_featured_media' );

// function api_register_approved() {
//     register_rest_field( 'news',
//         'approved',
//         array(
//             'get_callback'    => 'api_get_approved',
//             'update_callback' => 'api_update_approved',
//             'schema'          => null,
//         )
//     );
// }

// function api_get_approved( $object, $field_name, $request ) {
//     return intval(get_post_meta( $object[ 'id' ], $field_name, true ));
// }

// function api_update_approved($value, $object, $field_name){
// 	return update_post_meta( $object->ID, $field_name, strip_tags( $value ) );
// }

// function api_register_author() {
//     register_rest_field( 'news',
//         'author',
//         array(
//             'get_callback'    => 'api_get_author',
//             'update_callback' => 'api_update_author',
//             'schema'          => null,
//         )
//     );
// }

// function api_get_author( $object, $field_name, $request ) {
//     return get_post_meta( $object[ 'id' ], $field_name, true );
// }

// function api_update_author($value, $object, $field_name){
// 	return update_post_meta( $object->ID, $field_name, strip_tags( $value ) );
// }

// function api_register_site_name() {
//     register_rest_field( 'news',
//         'site_name',
//         array(
//             'get_callback'    => 'api_get_site_name',
//             'update_callback' => null,
//             'schema'          => null,
//         )
//     );
// }

// function api_get_site_name( $object, $field_name, $request ) {
//     return get_option('blogname');
// }

// function api_register_featured_media() {
//     register_rest_field( 'news',
//         'featured_media',
//         array(
//             'get_callback'    => 'api_get_featured_media',
//             'update_callback' => null,
//             'schema'          => null,
//         )
//     );
// }

// function api_get_featured_media( $object, $field_name, $request ) {
//     return wp_get_attachment_url($object["featured_media"]);
// }


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

function meta_password() {
	global $post;
    $custom = get_post_custom($post->ID);

    include_once('views/password.php');
}

function cpt_save($post_id, $post) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' ) return $post_id;
    if ( $post->post_type != 'employee' && $post->post_type != 'client' ) return $post_id;

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
		update_post_meta($post->ID, "employee_email", $_POST["employee_email"]);
		update_post_meta($post->ID, "employee_phone", $_POST["employee_phone"]);
	}
}

?>
