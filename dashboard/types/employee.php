<?php 

// Create Employee type
add_action('init', 'employee_create_type');
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
    'supports' => array('title', 'thumbnail')
  );

	register_post_type( 'employee' , $args );
}

// Add metaboxes to employee page
add_action("admin_init", "create_employee_metaboxes");
function create_employee_metaboxes() {
	add_meta_box("employee-meta", "Employee Info", "render_employee_field_view", "employee", "normal", "high");
	add_meta_box("password-meta", "Password", "render_employee_password_view", "employee", "normal", "high");
}

function render_employee_field_view() {
	global $post;
  $custom = get_post_custom($post->ID);

  include_once(__DIR__ . '/../views/employee.php');
}

function render_employee_password_view() {
	global $post;
  $custom = get_post_custom($post->ID);

  include_once(__DIR__ . '/../views/password.php');
}

// Set dashboard list columns
add_filter( 'manage_edit-employee_columns', 'set_employee_columns' );
function set_employee_columns( $columns ) {
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

// Retrieve column data
add_action( 'manage_employee_posts_custom_column', 'get_employee_column_data', 10, 2 );
function get_employee_column_data( $column, $post_id ) {
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

// Select sortable columns
add_filter( 'manage_edit-employee_sortable_columns', 'select_employee_sortable_columns' );
function select_employee_sortable_columns( $columns ) {
  $columns['id'] = 'id';

  return $columns;
}

// Set updated messages for employee modification
add_filter( 'post_updated_messages', 'set_employee_update_messages' );
function set_employee_update_messages( $messages ) {
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

// Set title text
add_filter( 'enter_title_here', 'set_employee_title' );
function set_employee_title( $title ){
  $screen = get_current_screen();

  if  ( $screen->post_type == 'employee') {
       $title = 'Enter employee name';
  }

  return $title;
}

// On employee save
add_action('save_post', 'save_employee', 20, 2);
function save_employee($post_id, $post) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' ) return $post_id;
	
	if ( $post->post_type != 'employee' ) return $post_id;

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

	if(array_key_exists('password', $_POST) && $_POST['password'] != '')
		update_post_meta($post->ID, "password", password_hash($_POST["password"], PASSWORD_DEFAULT));	

	update_post_meta($post->ID, "email", $_POST["email"]);
	update_post_meta($post->ID, "phone", $_POST["phone"]);
	update_post_meta($post->ID, "type", $_POST["type"]);
}

?>