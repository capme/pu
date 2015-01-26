<div class="panel panel-default" style="width:50%">
  <table class="table">
	<tr>
    <td>Name</td>
    <td>Value</td>   
	<td>Remove</td>   
  </tr> 
<?php
for($i = 0 ; $i < count($value); $i++){
$ex =explode('_',$value[$i]['option_name']);
$im = implode(" ", $ex); 
$bar = ucwords(strtolower($im));
?>
	<tr>  
    <td><?php echo $bar ?></td>
	<td><input type="text" value="<?php echo $value[$i]['option_value']?>" name="clientoptions[option_value][<?php echo $value[$i]['id']?>]" ></td>
	<td><input type="checkbox" name="clientoptions[cek][<?php echo $i?>]" value="<?php echo $value[$i]['id']?>" ></td>
	</tr>
<?php }?>
</table>
</div>
