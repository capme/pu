<div class="panel panel-default" style="width:100%">
  <table class="table" >
	<tr>
    <td style=" font:bold 12px/30px Arial;">Code</td>
    <td style=" font:bold 12px/30px Arial;">Brand</td>  
	<td style=" font:bold 12px/30px Arial;">Remove</td>  	
  </tr> 
<?php

for($i = 0 ; $i < count($value); $i++){
$brand = json_decode($value[$i]['option_value'], true);
$id=$value[0]['id'];
?><input type="hidden" name="brandcode[id]" value="<?php echo $id?>"> <?php
for($a=0; $a < count($brand);$a++){
$brands=array_values($brand);
$key=array_keys($brand);
?>	<tr> 	
	<td><input class="form-control" type="text" value="<?php echo $key[$a];?>" name="brandcode[key][<?php echo $a?>]" style="width:80%"></td>
    <td><input class="form-control" type="text" value="<?php echo $brands[$a]?>" name="brandcode[brands][<?php echo $a ?>]" style="width:80%"></td>	
	<td><input type="checkbox" name="brandcode[cek][<?php echo $a?>]" value="<?php echo $brands[$a]?>"></td>
	</tr>	
<?php
}
}?>
	<tr>
		<td id="key0"> </td>
		<td id ="brands0"> </td>			
	</tr>
</table>
</div>
<p><a href="javascript:action();" class="btn btn-xs default"><i class="glyphicon glyphicon-plus" ></i> Add Brand Code</a></p>
<p><a href="javascript:removekey();" class="btn btn-xs default"><i class="glyphicon glyphicon-trash" ></i> Remove</a></p>
