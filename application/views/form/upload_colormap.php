<div class="form-control height-auto <?php echo $msg ? "has-error" : "" ?>"" >
<div class="alert alert-warning">
    <strong>Warning!</strong> Make sure you've export the latest data before start import.
</div>
<label>File input</label>
<input type="file" name="<?php echo $name;?>" id="filename">
<p class="help-block">Please upload only .xls file. maximum size 2 Mb</p>
<span class="help-block">
	 <?php echo $msg ? $msg : $help;?>
</span>
<b>Sample: </b><a href="<?php echo site_url('merchandising/color_map/sample.xlsx')?>">Color Map</a>
</div>


