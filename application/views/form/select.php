<div class="form-group <?php echo $msg ? "has-error" : "" ?>">
	<label class="control-label col-md-3"><?php echo $label?></label>
	<div class="col-md-9">
		<select class="form-control select2_category" name="<?php echo $group."[".$name."]";?>" id="<?php echo $id?>">
			<?php foreach($list as $key => $val):?>
			<option <?php echo $key == $value ? "selected" : ""?> value="<?php echo $key?>"><?php echo $val?></option>
			<?php endforeach;?>
		</select>
		<span class="help-block">
			 <?php echo $msg ? $msg : $help;?>
		</span>
	</div>
</div>