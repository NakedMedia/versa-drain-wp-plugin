<?php 
	$client_query = new WP_Query(array(
	    'post_type' => 'client',
	    'post_status' => 'publish',
	    'posts_per_page' => -1
	));
?>

<div class="inner-meta">
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

  <label>Address: </label>
	<br/>
	<input type="text" name="address" size="60" placeholder="ex: 1600 Amphitheatre Parkway Mountain View, CA 94043" value="<?= $custom['address']?>"/>
	<br/>
	<br/>

  <label>Email: </label>
	<br/>
	<input type="text" name="email" size="20" placeholder="ex: john@gmail.com" value="<?= $custom['email'] ?>"/>
	<br/>
	<br/>

	<label>Phone: </label>
	<br/>
	<input type="text" name="phone" size="20" placeholder="ex: 888-123-4567" value="<?= $custom['phone'] ?>"/>
	<br/>
	<br/>

	<label>Contact Name: </label>
	<br/>
	<input type="text" name="contact_name" size="20" placeholder="ex: John Smith" value="<?= $custom['contact_name'] ?>"/>
	<br/>
	<br/>
</div>