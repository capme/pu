<div class="form-group <?php echo $msg ? "has-error" : "" ?>">
	<label class="control-label col-md-3"><?php echo $label?></label>
	<div class="col-md-4">
		<div class="checkbox-list" data-error-container="#container<?php echo $id?>_error">
			<?php foreach($list as $val => $label):?>
			<label>
			<input type="checkbox" value="<?php echo $val?>" name="<?php echo $group . "[".$name."]"?>"/> <?php echo $label?> </label>
			<?php endforeach;?>
		</div>
		<span class="help-block">
			 <?php echo $help?>
		</span>
		<div id="container_<?php echo $id?>_error">
		</div>
	</div>
</div>
