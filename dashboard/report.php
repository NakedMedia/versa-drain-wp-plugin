<?php 

// Create Report type
add_action('init', 'report_create_type');
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
    'supports' => array('editor', 'thumbnail')
  );

	register_post_type( 'report' , $args );
}

// Add metaboxes to employee page
add_action("admin_init", "create_report_metaboxes");
function create_report_metaboxes() {
	add_meta_box("report-meta", "Report Info", "render_report_field_view", "report", "normal", "high");
}

function render_report_field_view() {
	global $post;
  $custom = get_post_custom($post->ID);

  include_once('views/report.php');
}

// Set dashboard list columns
add_filter( 'manage_edit-report_columns', 'set_report_columns' );
function set_report_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'id' => __('ID'),
		'description' => __( 'Description' ),
		'client' => __( 'Client' ),
		'employee' => __('Employee'),
	);

	return $columns;
}

// Retrieve column data
add_action( 'manage_report_posts_custom_column', 'get_report_column_data', 10, 2 );
function get_report_column_data( $column, $post_id ) {
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

// Select sortable columns
add_filter( 'manage_edit-report_sortable_columns', 'select_report_sortable_columns' );
function select_report_sortable_columns( $columns ) {
  $columns['id'] = 'id';
  $columns['client'] = 'client';
  $columns['employee'] = 'employee';

  return $columns;
}

// Set updated messages for report modification
add_filter( 'post_updated_messages', 'set_report_update_messages' );
function set_report_update_messages( $messages ) {
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

?>