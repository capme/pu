<div class="panel panel-default" style="width:100%">
  <table class="table" >
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
    <td style="width:30%"><?php echo $bar ?></td>
	<td><input class="form-control" type="text" value="<?php echo $value[$i]['option_value']?>" name="clientoptions[option_value][<?php echo $value[$i]['id']?>]" style="width:80%"></td>
	<td><input type="checkbox" name="clientoptions[cek][<?php echo $i?>]" value="<?php echo $value[$i]['id']?>" ></td>
	</tr>
<?php }?>
</table>
</div>
