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
		'menu_icon'  => 'dashicons-groups',
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
  $custom = get_custom_fields($post->ID, array('address', 'contact_name', 'contact_email', 'contact_phone'));

  include_once(__DIR__ . '/../views/client.php');
}

function render_client_password_view() {
	global $post;
  $custom = get_custom_fields($post->ID, array('password'));

  include_once(__DIR__ . '/../views/password.php');
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

	$custom = get_custom_fields($post_id, array('address', 'contact_email', 'contact_phone'));

	switch( $column ) {

		case 'id' :
			echo $post_id;
			break;

		case 'address':
			echo $custom['address'] ?: '--';
			break;

		case 'contact_email':
			echo $custom['contact_email'] ?: '--';
			break;

		case 'contact_phone':
			echo $custom['contact_phone'] ?: '--';
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

// On client save
add_action('save_post', 'save_client', 20, 2);
function save_client( $post_id, $post ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' ) return $post_id;

	if( $post->post_type != 'client' || get_post_status($post_id) == 'trash' ) return $post_id;

	$password = $_POST['password'];
	$confirm_password = $_POST['confirm_password'];

	$password_match = $password == '' || $password == $confirm_password;

	if ( ( isset( $_POST['publish'] ) || isset( $_POST['save'] ) ) && $_POST['post_status'] == 'publish' ) {
		if ( !$password_match ) {
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'pending' ), array( 'ID' => $post_id ) );

			add_filter( 'redirect_post_location', function( $location ) {
				return add_query_arg("message", "5", $location);
			} );
			
			return $post_id;
		}
	}

	$updated_fields = array(
		'address' => $_POST['address'],
		'contact_name' => $_POST['contact_name'],
		'contact_email' => $_POST['contact_email'],
		'contact_phone' => $_POST['contact_phone']
	);

	if(array_key_exists('password', $_POST) && $_POST['password'] != '') {
		$updated_fields['password'] = password_hash($_POST["password"], PASSWORD_DEFAULT);
	}

	set_custom_fields($post_id, $updated_fields);

}

?>