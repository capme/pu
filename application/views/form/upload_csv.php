<div class="form-control height-auto <?php echo $msg ? "has-error" : "" ?>"" >
<label>File input</label>
<input type="file" name="<?php echo $name;?>" id="userfile">
<p class="help-block">Please upload only .csv file. maximum size 2 Mb</p>
<span class="help-block">
	 <?php echo $msg ? $msg : $help;?>
</span>
</div>


