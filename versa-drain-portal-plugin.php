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

add_action('admin_head', 'vd_admin_styles');

function vd_admin_styles() {
  echo '<link rel="stylesheet" href="'.plugins_url('css/admin.css', __FILE__).'" type="text/css" media="all" />';
}

require 'dashboard/client.php';
require 'dashboard/employee.php';
require 'dashboard/report.php';

add_action('save_post', 'cpt_save', 20, 2);

/*----- API Route Registration -----*/
require 'api/mod.php';


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
