<?php 

// Create Location type
add_action('init', 'location_create_type');
function location_create_type() {
	$args = array(
		'label' => 'Locations',
		'labels' => array(
			'add_new_item' => 'Add New Location',
			'edit_item' => 'Edit Location',
			'search_items' => 'Search Locations',
			'not_found' => 'No Locations Found',
			'singular_name' => 'Location'
		),
		'public' => false,
		'show_ui' => true,
		'capability_type' => 'post',
		'show_in_rest' => false,
		'hierarchical' => false,
		'rewrite' => array('slug' => 'locations'),
		'menu_icon'  => 'dashicons-store',
		'query_var' => true,
		'supports' => array('title')
	);
	
	register_post_type( 'location' , $args );
}

// Add metaboxes to client page
add_action("admin_init", "create_location_metaboxes");
function create_location_metaboxes() {
	add_meta_box("location-meta", "Location Info", "render_location_field_view", "location", "normal", "high");
}

function render_location_field_view() {
	global $post;
  $custom = get_custom_fields($post->ID, array('address', 'client_id'));

  include_once(__DIR__ . '/../views/location.php');
}

// Set dashboard list columns
add_filter( 'manage_edit-location_columns', 'set_location_columns' );
function set_location_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'id' => __('ID'),
    'title' => __( 'Location' ),
    'address' => __('Address'),
    'client' => __('Client')
	);

	return $columns;
}

// Retrieve column data
add_action( 'manage_location_posts_custom_column', 'get_location_column_data', 10, 2 );
function get_location_column_data( $column, $post_id ) {
	global $post;

	$custom = get_custom_fields($post_id, array('address', 'client_id'));

	switch( $column ) {

		case 'id' :
			echo $post_id;
			break;

		case 'address':
			echo $custom['address'] ?: '--';
      break;
      
    case 'client':
			echo get_post($custom['client_id'])->post_title;
			break;

		default :
			break;
	}
}

// Select sortable columns
add_filter( 'manage_edit-location_sortable_columns', 'select_location_sortable_columns' );
function select_location_sortable_columns( $columns ) {
  $columns['id'] = 'id';

  return $columns;
}

// Set updated messages for location modification
add_filter( 'post_updated_messages', 'set_location_update_messages' );
function set_location_update_messages( $messages ) {
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	$messages['location'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => 'Location updated.',
		2  => 'Custom field updated.',
		3  => 'Custom field deleted.',
		4  => 'Location updated.',
		5  => 'Error: passwords do not match',
		6  => 'Location created.',
		7  => 'Location saved.',
		8  => 'Location submitted.',
		10 => 'Location draft updated.'
	);

	return $messages;
}

// Set title text
add_filter( 'enter_title_here', 'set_location_title' );
function set_location_title( $title ) {
  $screen = get_current_screen();
  
  if ( $screen->post_type == 'location') {
    $title = 'Enter location name';
  }

  return $title;
}

// On client save
add_action('save_post', 'save_location', 20, 2);
function save_location( $post_id, $post ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' ) return $post_id;

  if( $post->post_type != 'location' || get_post_status($post_id) == 'trash' ) return $post_id;
  
  $updated_fields = array(
    'address' => $_POST["address"],
    'client_id' => $_POST["client_id"]
  );

  set_custom_fields($post_id, $updated_fields);
}

?>