<?php 
	$employee_query = new WP_Query(array(
	    'post_type' => 'employee',
	    'post_status' => 'publish',
	    'posts_per_page' => -1
	));

	$client_query = new WP_Query(array(
	    'post_type' => 'client',
	    'post_status' => 'publish',
	    'posts_per_page' => -1
	));
?>

<div class="inner-meta">
	<label>Employee:</label>
	<br/>
	<select name="employee_id" id="employee_id">
		<?php

			while ($employee_query->have_posts()) {
			    $employee_query->the_post();
			    $post_id = get_the_ID();
			    $name = get_the_title();
			    echo '<option value="', $post->ID, '"', $custom['employee_id'][0] == $post_id ? ' selected="selected"' : '', '>', $name, '</option>';
			}

		?>
	</select>
	<br/>
	<br/>

	<label>Client:</label>
	<br/>
	<select name="client_id" id="client_id" value="<?= $custom['client_id'][0] ?>">
		<?php

			while ($client_query->have_posts()) {
			    $client_query->the_post();
			    $post_id = get_the_ID();
			    $name = get_the_title();
			    echo '<option value="', $post->ID, '"', $custom['client_id'][0] == $post_id ? ' selected="selected"' : '', '>', $name, '</option>';
			}

		?>
	</select>
	<br/>
	<br/>
</div>