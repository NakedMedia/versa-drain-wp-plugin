<div>
  <h2>VersaTrack Options</h2>

  <form method="post" action="options.php"> 
    <?php settings_fields( 'vd_options_group' ); ?>
    <?php do_settings_sections( 'vd_options_group' );?>

    <label>Default Email Address</label>
    <br />
    <input type="text" name="vd_default_email" value="<?= get_option('vd_default_email');?>" />

    <?php  submit_button(); ?>
  </form>
</div>