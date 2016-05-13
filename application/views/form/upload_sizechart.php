<div class="form-control height-auto <?php echo $msg ? "has-error" : "" ?>"" >
<label>File input</label>
<input type="file" name="<?php echo $name;?>" id="filename">
<p class="help-block">Please upload only .csv file. maximum size 2 Mb</p>
<span class="help-block">
	 <?php echo $msg ? $msg : $help;?>
</span>
<b>Sample: </b><a href="<?php echo site_url('merchandising/size_chart/sizechart.csv')?>"> Size Chart Simple Template</a>
<b>Reference: </b><a href="<?php echo site_url('merchandising/size_chart/sizechart_reference.xlsx')?>"> Size Chart Reference</a>
</div>


