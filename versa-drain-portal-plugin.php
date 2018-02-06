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

// Add create function to init
add_action('init', 'client_create_type');

// Create the custom post type and register it
function client_create_type() {
	$args = array(
	      	'label' => 'Clients',
	      	'labels' => array(
	      		'add_new_item' => 'Add New Client',
	      		'edit_item' => 'Edit Client'
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

function wpb_change_title_text( $title ){
     $screen = get_current_screen();
  
     if  ( $screen->post_type == 'client') {
          $title = 'Enter client name';
     }
  
     return $title;
}

function vd_edit_client_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'id' => __('ID'),
		'title' => __( 'Client' ),
		'address' => __('Address')
	);

	return $columns;
}

function vd_manage_client_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		case 'id' :
			echo $post_id;
			break;

		default :
			break;
	}
}

function vd_sortable_client_column( $columns ) {
    $columns['id'] = 'id';
 
    return $columns;
}

add_action("admin_init", "client_init");
add_action('save_post', 'client_save');

add_filter( 'enter_title_here', 'wpb_change_title_text' );
add_filter( 'manage_edit-client_columns', 'vd_edit_client_columns' );
add_action( 'manage_client_posts_custom_column', 'vd_manage_client_columns', 10, 2 );
add_filter( 'manage_edit-client_sortable_columns', 'vd_sortable_client_column' );
 


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
function client_init() {
	global $current_user;

	add_meta_box("client-meta", "Client Info", "client_meta", "client", "normal", "high");
}

function client_meta() {
	global $post; // Get global WP post var
    $custom = get_post_custom($post->ID); // Set our custom values to an array in the global post var

    // Form markup
    include_once('views/client.php');
}

// Save our variables
function client_save() {
	global $post;

	update_post_meta($post->ID, "address", $_POST["address"]);
}

?>
