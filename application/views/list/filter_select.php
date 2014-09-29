<select name="<?php echo $name?>" class="form-control form-filter input-sm">
	<?php foreach($option as $val => $label):?>
	<option value="<?php echo $val?>"><?php echo $label?></option>
	<?php endforeach;?>
</select>