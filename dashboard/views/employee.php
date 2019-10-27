<div class="inner-meta">
	<label>Employee Email: </label>
	<br/>
	<input type="text" name="email" size="20" placeholder="ex: john@gmail.com" value="<?= $custom['email'][0] ?>"/>
	<br/>
	<br/>

	<label>Employee Phone: </label>
	<br/>
	<input type="text" name="phone" size="20" placeholder="ex: 888-123-4567" value="<?= $custom['phone'][0] ?>"/>
	<br/>
	<br/>

	<label>Employee Type: </label>
	<br/>
	<select name="type">
		<option value="employee" <?=$custom['type'][0] == 'employee' ? 'selected=selected' : ''?>>Employee</option>
		<option value="admin" <?=$custom['type'][0] == 'admin' ? 'selected=selected' : ''?>>Admin</option>
	</select>
</div>