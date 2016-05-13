<?php 
$selected = !empty($value) ? json_decode($value, true) : array();
$html = adminCollectModule($selected, $list, $group);
$html = '<li><label><input type="checkbox" name="'.$group.'[modules][all]" value="all" '.(in_array("all", $selected) ? 'checked' : '').'>All Modules</label>
		<ul class="list-unstyled">'.$html.'</ul></li>';
?>
<div class="form-control height-auto">
	<div class="scroller" style="height:575px;" data-always-visible="1">
		<ul class="list-unstyled">
			<?php echo $html;?>
		</ul>
	</div>
</div>

<?php 

?>