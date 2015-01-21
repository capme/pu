	<?php 
    $strenc = urlencode($value);    
	$arr = unserialize(urldecode($strenc));
	?>
<div class="panel panel-default" style="width:50%">
  <table class="table" >
	<tr>
    <td>SKU</td>
    <td>QTY</td>    
  </tr>
  <tr>
    <td><?php echo $arr['name']; ?></td>
	<td><?php echo $arr['qty']; ?></td>    
  </tr>
  </table>
</div>
