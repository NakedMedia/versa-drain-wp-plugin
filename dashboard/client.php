<?php 

// Create Client type
add_action('init', 'client_create_type');
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
		'supports' => array('title', 'thumbnail')
	);
	
	register_post_type( 'client' , $args );
}

// Add metaboxes to client page
add_action("admin_init", "create_client_metaboxes");
function create_client_metaboxes() {
	add_meta_box("client-meta", "Client Info", "render_client_field_view", "client", "normal", "high");
	add_meta_box("password-meta", "Password", "render_client_password_view", "client", "normal", "high");
}

function render_client_field_view() {
	global $post;
  $custom = get_post_custom($post->ID);

  include_once('views/client.php');
}

function render_client_password_view() {
	global $post;
  $custom = get_post_custom($post->ID);

  include_once('views/password.php');
}

// Set dashboard list columns
add_filter( 'manage_edit-client_columns', 'set_client_columns' );
function set_client_columns( $columns ) {
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

// Retrieve column data
add_action( 'manage_client_posts_custom_column', 'get_client_column_data', 10, 2 );
function get_client_column_data( $column, $post_id ) {
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

// Select sortable columns
add_filter( 'manage_edit-client_sortable_columns', 'select_client_sortable_columns' );
function select_client_sortable_columns( $columns ) {
  $columns['id'] = 'id';

  return $columns;
}

// Set updated messages for client modification
add_filter( 'post_updated_messages', 'set_client_update_messages' );
function set_client_update_messages( $messages ) {
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

// Set title text
add_filter( 'enter_title_here', 'set_client_title' );
function set_client_title( $title ) {
  $screen = get_current_screen();
  
  if ( $screen->post_type == 'client') {
    $title = 'Enter client name';
  }

  return $title;
}

?>