<div class="form-control height-auto <?php echo $msg ? "has-error" : "" ?>"" >
<label>File input</label>
<input type="file" name="<?php echo $name;?>" id="filename">
<span class="help-block">
	 <?php echo $msg ? $msg : $help;?>
</span>
</div>


