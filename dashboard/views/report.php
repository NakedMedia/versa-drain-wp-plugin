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
			echo '<option value="', $post->ID, '"', $custom['employee_id'] == $post_id ? ' selected="selected"' : '', '>', $name, '</option>';
		}

		?>
	</select>
	<br/>
	<br/>

	<label>Client:</label>
	<br/>
	<select name="client_id" id="client_id" value="<?= $custom['client_id'] ?>">
		<?php

		while ($client_query->have_posts()) {
			$client_query->the_post();
			$post_id = get_the_ID();
			$name = get_the_title();
			echo '<option value="', $post->ID, '"', $custom['client_id'] == $post_id ? ' selected="selected"' : '', '>', $name, '</option>';
		}

		?>
	</select>
	<br/>
	<br/>

	<label>Location:</label>
	<br/>
	<?php if($custom['client_id'] != null): ?>
	<?php $location_query = new WP_Query(array(
	    'post_type' => 'location',
			'post_status' => 'publish',
			'meta_key' => 'client_id',
			'meta_value' => $custom['client_id'],
	    'posts_per_page' => -1
	));?>
	<select name="location_id" id="location_id" value="<?= $custom['location_id'] ?>">
		<option value=<?= null?>>Select Location</option>

		<?php

		while ($location_query->have_posts()) {
			$location_query->the_post();
			$post_id = get_the_ID();
			$name = get_the_title();
			echo '<option value="', $post->ID, '"', $custom['location_id'] == $post->ID ? ' selected="selected"' : '', '>', $name, '</option>';
		}

		?>

	</select>
	<?php else:?>
	<p>To select a location, first select a client and save the record</p>
	<?php endif?>
	
	<br/>
	<br/>

</div>