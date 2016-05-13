<div class="form-group <?php echo $msg ? "has-error" : "" ?>">
	<label class="control-label col-md-3"><?php echo $label?></label>
	<div class="col-md-9">
		<input type="text" placeholder="<?php echo $placeholder?>" class="form-control" value="<?php echo $value?>" name="<?php echo $group."[".$name."]";?>" id="<?php echo $id?>" <?php echo $disabled?> />
		<span class="help-block">
			 <?php echo $msg ? $msg : $help;?>
		</span>
	</div>
</div>
