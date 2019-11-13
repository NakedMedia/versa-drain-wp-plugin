<?php 

  add_action('admin_menu', 'vd_register_options_page');

  function vd_register_options_page() {
    add_options_page('VersaTrack Options', 'VersaTrack Options', 'manage_options', 'vd-options-page', 'vd_options_page');

    add_action( 'admin_init', 'vd_register_options' );
  }

  function vd_register_options() {
    register_setting( 'vd_options_group', 'vd_default_email' );
  }

  function vd_options_page() {
    require 'views/settings.php';
  }

?>