<div class="form-group <?php echo $msg ? "has-error" : "" ?>">
	<label class="control-label"><?php echo $label?></label>
	<div class="col-md-9">
		<input type="text" placeholder="<?php echo $placeholder?>" class="form-control-text" value="<?php echo $value?>" name="<?php echo $group."[".$name."]";?>" id="<?php echo $id?>" maxlength="<?php echo $maxlength?>" size="<?php echo $size?>" <?php echo $disabled?> />
		<span class="help-block">
			 <?php echo $msg ? $msg : $help;?>
		</span>
	</div>
</div>
