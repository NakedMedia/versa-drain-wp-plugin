<?php 

// Add dashboard styles
add_action('admin_head', 'add_dashboard_styles');
function add_dashboard_styles() {
  echo '<link rel="stylesheet" href="'.plugins_url('css/admin.css', __FILE__).'" type="text/css" media="all" />';
}

require_once 'utils.php';

require 'settings.php';

require 'types/client.php';
require 'types/employee.php';
require 'types/location.php';
require 'types/report.php';

?>