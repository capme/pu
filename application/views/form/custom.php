<div class="form-group <?php echo $msg ? "has-error" : "" ?>">
	<label class="control-label col-md-3"><?php echo $label?></label>
	<div class="col-md-9">
		<?php echo $this->load->view($view, array("list" => @$list, "value" => @$value))?>
		<span class="help-block">
			 <?php echo $help?>
		</span>
		<div id="container_<?php echo $id?>_error">
		</div>
	</div>
</div>
