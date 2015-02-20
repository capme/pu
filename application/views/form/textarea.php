<div class="form-group <?php echo $msg ? "has-error" : "" ?>">
	<label class="control-label col-md-3"><?php echo $label?></label>
	<div class="col-md-9">
		<textarea class="form-control" rows="3" name="<?php echo $group."[".$name."]";?>" id="<?php echo $id?>" placeholder="<?php echo $placeholder?>" <?php echo $disabled?> ><?php echo $value?> </textarea>
		<span class="help-block">
			 <?php echo $msg ? $msg : $help;?>
		</span>
	</div>
</div>